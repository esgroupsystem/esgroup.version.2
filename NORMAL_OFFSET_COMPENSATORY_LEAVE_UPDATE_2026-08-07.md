# ES Group Payroll v3.6 — Normal Offset / Company Compensatory Leave Update

Date: 2026-08-07
Project: Laravel 12 / MySQL / Falcon Admin
Base: ES Group v3.5 payroll adjustments + premiums build

## Objective

Replace the v3.5 custom Offset workflow ("pay Offset cash in the next cutoff") with a normal company Offset workflow that is easier to audit and does not create a separate Offset cash addition.

## New Offset Business Rule

Offset is now treated as a **company compensatory-leave / attendance credit**:

1. Employee selects an **earlier source date** where biometrics prove excess time beyond the required clock span.
2. Employee selects a **later target work date** that has an attendance shortage.
3. Employee enters the number of Offset hours requested.
4. System validates:
   - employee is payroll-active;
   - target has a plotted work schedule;
   - target is not the employee's weekly day off;
   - source has biometric proof;
   - source has qualifying excess minutes;
   - requested minutes are not already allocated to another Offset request;
   - target has enough shortage to receive the requested credit when a summary already exists.
5. Offset is saved as **Pending Approval**.
6. A user with `payroll.finalize` permission approves or rejects it.
7. On approval, Attendance Summary for the target date is rebuilt.
8. Approved Offset minutes cover target attendance shortage in this order:
   - undertime;
   - late;
   - remaining absent/partial-day shortage.
9. Offset creates **no separate cash addition** and is **not deferred to a later payroll**.
10. The Offset is tagged in payroll as an applied attendance adjustment when consumed.

## Overtime Safeguard

The new Offset workflow does **not cancel or suppress separately approved overtime pay**.

This is intentional. Philippine Labor Code Book III, Article 88 states that undertime on one day shall not be offset by overtime on another day. The software therefore treats Offset as an additional **company-paid compensatory leave/attendance benefit**, not as a replacement for statutory overtime compensation. Approved OT remains independently payable through the existing OT approval workflow.

HR/legal should confirm which employees are covered by statutory hours-of-work rules and document the company's compensatory-leave policy. The application does not determine legal exemption status by itself.

Official reference: Department of Labor and Employment, Book III — Conditions of Employment, Articles 87–88.

## Example A — 8-Hour Employee

Workday rule:
- Paid work: 8 hours = 480 minutes
- Lunch: 1 hour = 60 minutes
- Required clock span: 9 hours = 540 minutes

Source date biometrics:
- Time In: 08:00
- Time Out: 18:00
- Clock span: 600 minutes
- Qualifying company Offset evidence: 600 - 540 = **60 minutes**

Target date:
- Employee has 60 minutes undertime
- Approved Offset: 60 minutes

Result:
- Target undertime after Offset: 0 minutes
- Target payable day restored to full day
- Separate Offset cash addition: PHP 0.00
- Approved OT from the source date, if any, remains independently payable.

## Example B — Partial Absence Credit

Target day paid requirement: 480 minutes
Employee absent: payable minutes = 0
Approved Offset credit: 120 minutes

Result:
- New payable minutes = 120
- Remaining shortage = 360 minutes
- Remaining shortage remains deductible
- Offset does not accidentally convert the entire day to paid attendance.

## Example C — Duplicate Offset Protection

Source qualifying excess: 120 minutes
Existing pending/approved Offset reservation: 60 minutes
New available Offset credit: 60 minutes

A new 90-minute Offset request is rejected because only 60 minutes remain available for another Offset request.

OT is not deducted from this company Offset balance because OT entitlement remains separate.

## Existing v3.5 Offset Records

Migration:

`database/migrations/2026_08_07_172000_convert_offset_to_company_compensatory_leave.php`

The migration:
- preserves Offset records already linked to **FINALIZED** payrolls;
- resets unconsumed Offset records to `pending`;
- releases Offset records linked only to draft payrolls;
- clears old `payroll_effective_date` deferred-payment scheduling;
- clears old `approved_minutes`, forcing HR to edit/revalidate the request under the new rule;
- clears draft payroll links for those Offset requests.

Payroll finalization also blocks a draft that still contains the legacy v3.5 **Deferred offset payment** calculation, forcing safe regeneration under v3.6.

## Files Updated

- `app/Http/Controllers/Payroll/PayrollAttendanceAdjustmentController.php`
- `app/Http/Controllers/Payroll/PayrollController.php`
- `app/Http/Requests/Payroll/PayrollAttendanceAdjustmentRequest.php`
- `app/Models/PayrollAttendanceAdjustment.php`
- `app/Services/Payroll/DailyAttendanceSummaryService.php`
- `app/Services/Payroll/PayrollComputationService.php`
- `app/Services/Payroll/PayrollPremiumService.php`
- `database/migrations/2026_08_07_172000_convert_offset_to_company_compensatory_leave.php`
- `resources/views/payroll/attendance_adjustments/_form.blade.php`
- `resources/views/payroll/attendance_adjustments/index.blade.php`
- `resources/views/payroll/attendance_summary/index.blade.php`
- `resources/views/payroll/items/show.blade.php`
- `tests/Unit/Services/Payroll/PayrollAdjustmentPolicyTest.php`

## Approval Integrity Improvements

Offset approval now revalidates the request immediately before approval. Approval is blocked if:
- source/target data are incomplete;
- requested minutes are missing;
- source date is not earlier than target;
- target schedule is missing;
- target is a weekly day off;
- source biometrics are no longer available;
- another Offset request already consumed the source credit;
- target no longer has enough attendance shortage.

Editing an already-approved Offset forces it back to Pending Approval if any critical field changes, including employee, target date, source date, or requested hours.

## Payroll Finalization Safeguards

Payroll finalization now blocks when:
- an OT request in the cutoff is pending;
- an Offset request targeting the cutoff is pending;
- the draft contains the legacy v3.5 deferred-cash Offset calculation;
- approved adjustments changed after the draft was generated.

## Deployment

Back up the database first.

```bash
php artisan down
php artisan optimize:clear
php artisan migrate --force
composer dump-autoload -o
php artisan optimize:clear
php artisan up
```

Then:

1. Open **Payroll > Adjustment**.
2. Review all Offset records reset to **Pending Approval**.
3. Edit each old Offset request.
4. Select/confirm the source excess-time date.
5. Enter the hours to transfer.
6. Click **Check Available Offset Credit**.
7. Save.
8. Approve or reject using an authorized Head Manager/payroll finalizer account.
9. Rebuild the affected Attendance Summary cutoff.
10. Delete and regenerate affected **draft** payrolls.
11. Do not rewrite finalized historical payrolls without your normal controlled correction process.

## Validation Performed in Build Environment

- PHP syntax validation: 393 PHP files passed.
- `git diff --check`: passed with no whitespace errors.
- Adjustment form JavaScript: `node --check` passed.
- Attendance Summary JavaScript: `node --check` passed.
- Static regression checks confirmed the old deferred Offset calculation is no longer used by current payroll computation.

Full Laravel PHPUnit execution was not available in the build container because the package intentionally excludes `vendor` and the container PHP CLI lacks required extensions such as `mbstring`/DOM/XML. The regression test source is included for execution on the deployment environment.

Recommended after deployment:

```bash
php artisan test --filter=PayrollAdjustmentPolicyTest
php artisan test --filter=PayrollPremiumServiceTest
```

## Important Payroll Policy Note

This implementation is deliberately more protective than the old deferred-payment workflow: Offset is a company attendance benefit and does not take away separately payable OT. If the company wants a different flexible-work arrangement, document it with HR/legal first and then configure/extend the workflow rather than silently converting statutory OT into attendance credits.
