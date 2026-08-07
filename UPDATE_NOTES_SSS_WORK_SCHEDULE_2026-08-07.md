# ES Group Payroll Update — SSS 2025 and Work Schedule Rules

Release date: 2026-08-07

## Objective

This release updates payroll and attendance to:

1. Compute SSS contributions using the official Business Employer and Employee schedule under SSS Circular No. 2024-006, effective January 2025.
2. Support two permanent workday rules per employee:
   - 8 paid work hours + 1 unpaid lunch hour = 9 clock hours.
   - 9 paid work hours + 1 unpaid lunch hour = 10 clock hours.
3. Support multiple weekly days off.
4. Carry each employee's paid-hours rule into attendance summaries, payroll rates, payslip day equivalents, and salary previews.

## SSS Formula and Method

The application uses the official Monthly Salary Credit bracket:

- Compensation below PHP 5,250.00 uses MSC PHP 5,000.00.
- Compensation from PHP 5,250.00 through PHP 34,749.99 uses PHP 500.00 MSC increments.
- Compensation of PHP 34,750.00 or more uses MSC PHP 35,000.00.
- Employee share = 5% of MSC.
- Employer Social Security share = 10% of MSC.
- Employer EC = PHP 10.00 when MSC is PHP 14,500.00 or below; otherwise PHP 30.00.
- Regular SS MSC is capped at PHP 20,000.00.
- MSC above PHP 20,000.00 is separated into the Mandatory Provident Fund component.

Primary implementation:

- `config/sss.php`
- `app/Services/Payroll/SssContributionService.php`
- `app/Services/Payroll/GovernmentDeductionService.php`

The payroll contribution basis remains configurable in `config/payroll.php`. This release uses the actual monthly contribution-cycle basic compensation for SSS and applies the complete monthly contribution according to the configured cutoff schedule.

## Work Schedule Rules

Permanent schedules now store:

- `workday_type`
- `paid_work_minutes`
- `lunch_break_minutes`
- `day_offs` as JSON

The legacy `day_off` column remains populated as a comma-delimited compatibility snapshot. Its database length is increased to support multiple days.

Regular shifts are validated as follows:

- 8-hour workday: Time Out must be exactly 9 clock hours after Time In.
- 9-hour workday: Time Out must be exactly 10 clock hours after Time In.
- Overnight schedules are supported.
- Flexible shifts use the corresponding required clock duration.

Half-day rules:

- 8-hour workday: 4.00 paid hours.
- 9-hour workday: 4.50 paid hours.

## Important Database Changes

Migration:

`database/migrations/2026_08_07_100500_add_workday_rules_and_multiple_day_offs_to_employee_plotting_schedules.php`

The migration:

- Converts schedule `status` to `VARCHAR(30)` so `inactive` is valid.
- Expands legacy `day_off` to `VARCHAR(100)`.
- Adds the workday and multiple-day-off columns.
- Backfills existing schedules based on their current Time In/Time Out span.
- Defaults schedules without a usable span, including old flexible schedules, to the 8-hour workday rule. Review those employees after deployment.

## Deployment Procedure

Run these commands from the Laravel project root:

```bash
php artisan down
php artisan migrate --force
php artisan optimize:clear
php artisan up
```

For deployment through Composer or a clean source package, also run:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Use the asset commands only when the server builds frontend assets. The supplied full-source package already retains the existing public assets.

## Required Post-Deployment Workflow

1. Open **Payroll > Permanent Plotting Schedule**.
2. Review every employee, especially flexible shifts and employees who should use 9 paid hours.
3. Select all applicable weekly days off.
4. Save the schedules.
5. Rebuild **Daily Attendance Summary** for every affected date range.
6. Delete and regenerate affected **draft** payroll batches.
7. Do not silently overwrite finalized payroll. Reverse or reopen it through the application's approved audit workflow.
8. Verify at least one employee from each SSS bracket used by the company against the official table.

## Validation Completed

- PHP syntax validation passed for every changed PHP file.
- All modified Blade views compiled successfully.
- Laravel route loading passed for the plotting module.
- All 61 MSC brackets from PHP 5,000 through PHP 35,000 were programmatically validated.
- Boundary values at PHP 5,250.00, PHP 14,750.00, PHP 20,250.00, and PHP 34,750.00 were validated.
- Regular SS and MPF allocation was validated.
- 8-hour and 9-hour paid-time conversion, half-day conversion, multiple weekly days off, payroll rate conversion, and payslip day-equivalent logic were validated through application-bootstrap scripts.

The container used for preparation did not include PHP `dom`, `mbstring`, `xml`, and `xmlwriter`, so the standard PHPUnit runner could not execute there. The included PHPUnit tests are ready to run on the deployment environment:

```bash
php artisan test --filter=SssContributionServiceTest
php artisan test --filter=AttendancePayrollRulesTest
```

## Main Files Updated

- `app/Enums/WorkdayType.php`
- `app/Http/Controllers/HR_Department/MirasolBiometricsLogController.php`
- `app/Http/Controllers/Payroll/EmployeePlottingScheduleController.php`
- `app/Http/Controllers/Payroll/PayrollEmployeeSalaryController.php`
- `app/Http/Requests/Payroll/SaveEmployeePlottingScheduleRequest.php`
- `app/Models/EmployeePlottingSchedule.php`
- `app/Services/Payroll/DailyAttendanceSummaryService.php`
- `app/Services/Payroll/EmployeePlottingScheduleService.php`
- `app/Services/Payroll/GovernmentDeductionService.php`
- `app/Services/Payroll/PayrollComputationService.php`
- `app/Services/Payroll/PayrollDeductionService.php`
- `app/Services/Payroll/PayrollPayslipService.php`
- `app/Services/Payroll/SssContributionService.php`
- `config/payroll.php`
- `config/sss.php`
- `database/migrations/2026_08_07_100500_add_workday_rules_and_multiple_day_offs_to_employee_plotting_schedules.php`
- `resources/views/hr_department/mirasol_logs/index.blade.php`
- `resources/views/payroll/attendance_summary/index.blade.php`
- `resources/views/payroll/attendance_summary/table.blade.php`
- `resources/views/payroll/employee_salaries/_identity_create.blade.php`
- `resources/views/payroll/employee_salaries/_identity_edit.blade.php`
- `resources/views/payroll/employee_salaries/_salary_fields.blade.php`
- `resources/views/payroll/employee_salaries/_salary_preview_script.blade.php`
- `resources/views/payroll/plotting/index.blade.php`
- `routes/web.php`
- `tests/Unit/Services/Payroll/AttendancePayrollRulesTest.php`
- `tests/Unit/Services/Payroll/SssContributionServiceTest.php`
