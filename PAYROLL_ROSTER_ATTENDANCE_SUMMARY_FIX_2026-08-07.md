# Payroll Roster + Attendance Summary Integrity Fix

Date: 2026-08-07
Target: Laravel 12 / PHP 8.3+ / MySQL

## Business rule implemented

A payroll employee is included when all of these are true:

1. Employee belongs to the selected Payroll Group.
2. Employment Status is Active. Legacy NULL status is treated as Active.
3. Payroll Inclusion is ON. Legacy NULL inclusion is treated as ON.

Only employees explicitly marked Inactive or Payroll Inclusion OFF are excluded.

## Root causes fixed

### 1. Payroll used Attendance Summary as the employee roster

Old generation logic could only create a payroll item for employees that already had Daily Attendance Summary rows. An eligible employee could therefore disappear from payroll when the summary was missing or incorrectly merged.

Fix: `employee_biometrics` is now the canonical payroll roster. Attendance Summary is calculation data only.

### 2. Weak legacy biometric IDs could merge two canonical employees

CrossChex accounts can reuse employee IDs such as `1`, `2`, etc. The old summary person merge logic could merge two different `employee_biometrics.id` records when any legacy identifier overlapped.

Fix: `employee_biometrics.id` is authoritative. Different canonical IDs can never be merged.

### 3. CrossChex account boundary was not always applied to raw logs

Fix: raw log identity resolution and log queries now include the source CrossChex account when available.

### 4. Schedule/adjustment/salary matching mixed canonical and weak identifiers

Fix: when `employee_biometric_id` is available it is the only identity used. Legacy IDs are fallback only when no canonical FK exists.

### 5. Inactive employees could be re-resolved through legacy IDs

Fix: canonical inactive/payroll-excluded employees now resolve to NULL when payroll-active resolution is requested; the resolver no longer falls through to another employee with a reused legacy ID.

### 6. Attendance Summary export roster came from plotting schedules

Fix: export roster now comes from eligible `employee_biometrics`, so an eligible employee without a schedule remains visible for audit.

### 7. Attendance Summary export view expected `$employees` but controller did not provide it

Fix: controller now builds and passes the canonical employee export collection and all employee-level totals used by the Blade template.

### 8. Attendance Summary export route was double-prefixed

Old effective path: `/attendance-summary/attendance-summary/export-payroll`

New effective path: `/attendance-summary/export-payroll`

The route name remains `attendance-summary.export-payroll`.

## Safety behavior

- Payroll generation iterates every eligible employee in the selected group.
- If an eligible employee has no Attendance Summary rows, a zero-pay payroll item is still created and tagged `No Summary`.
- The employee does not silently disappear and cannot be accidentally paid from missing attendance input.
- Payroll finalization is blocked when any payroll item has incomplete Attendance Summary coverage.
- Attendance Summary rebuild is atomic per date: a failed employee/date cannot leave that date partially rebuilt.
- After a full period rebuild, the service verifies expected roster rows = eligible employees x cutoff calendar days.

## Attendance Summary UI changes

- Added Payroll Group filter.
- Added canonical roster coverage audit:
  - Eligible employees
  - Employees with Attendance Summary
  - Missing Summary employees
  - Names/employee numbers of missing employees
- Rebuild preserves Payroll Group filter.
- Export shows eligible employees even if summary/schedule is missing.
- Export identifies employees with `NO ATTENDANCE SUMMARY`.
- Fixed employee totals in print export (Absent, Review, Holiday Paid, Holiday Unpaid).

## Payroll UI changes

- Shows eligible roster count and missing-summary count.
- Missing employees remain visible with `No Summary` / `Summary Gap` audit badges.
- Displays a clear warning when Attendance Summary coverage is incomplete.
- Finalize is blocked until summary coverage is complete.

## Required deployment workflow

1. Back up the database.
2. Replace the updated files.
3. Run:

```bash
php artisan optimize:clear
```

No new database migration is required for this specific roster/summary fix.

4. Open Attendance Summary.
5. Select the correct cutoff and Payroll Group.
6. Click **Rebuild Current Cutoff**.
7. Confirm **Missing Summary = 0**.
8. Delete the affected **draft** payroll batch generated with the old logic.
9. Generate payroll again.
10. Confirm payroll employee count equals the Attendance Summary **Eligible** roster count for that group.

Do not delete or regenerate finalized payroll without an approved reversal/audit workflow.

## Primary changed files

- `app/Models/EmployeeBiometric.php`
- `app/Services/Biometrics/EmployeeBiometricIdentityService.php`
- `app/Services/Payroll/PayrollEmployeeRosterService.php` (new)
- `app/Services/Payroll/DailyAttendanceSummaryService.php`
- `app/Services/Payroll/PayrollComputationService.php`
- `app/Http/Controllers/Payroll/PayrollController.php`
- `app/Http/Controllers/Payroll/AttendanceSummaryController.php`
- `resources/views/payroll/payrolls/show.blade.php`
- `resources/views/payroll/attendance_summary/index.blade.php`
- `resources/views/payroll/attendance_summary/export-payroll.blade.php`
- `routes/web.php`

## Validation performed

- PHP syntax lint passed across application/config/routes/database/tests PHP files.
- Changed Attendance Summary and Payroll Blade templates compiled and resulting PHP passed syntax lint using a standalone Blade compiler.
- Full `artisan` console validation is limited in the sandbox because the CLI PHP build is missing native DOM and mbstring extensions.
