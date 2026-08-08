# Benefits Records Update — 2026-08-08

## Objective

Add an audit-safe Benefits Records ledger for active payroll employees covering:

- SSS employee share
- SSS employer Regular SS share
- SSS employer MPF share
- SSS Employees' Compensation (EC)
- Full SSS total contribution
- PhilHealth employee and employer shares
- Pag-IBIG employee and employer shares
- Employee total, employer/company total, and combined total

## Posting rule

Draft payrolls are reviewable calculations only. `benefit_contribution_records` rows are written only inside the successful payroll Finalize database transaction.

This keeps the official Benefits Records module free from draft/temporary payroll data.

## SSS basis

The existing `SssContributionService` is retained and uses SSS Circular No. 2024-006, effective January 2025:

- 15% total SS contribution rate
- 10% employer share
- 5% employee share
- MSC minimum: PHP 5,000
- MSC maximum: PHP 35,000
- Regular SS MSC cap: PHP 20,000
- MSC above PHP 20,000 goes to MPF / MySSS Pension Booster
- Employer-only EC: PHP 10 for MSC up to PHP 14,500; PHP 30 for MSC PHP 15,000 and above

## PhilHealth basis

- 5% premium rate
- PHP 10,000 income floor
- PHP 100,000 income ceiling
- 50% employee / 50% employer
- Uses fixed Monthly Basic Salary in payroll, not reduced by absence, tardiness, undertime, or LWOP deductions

## Pag-IBIG basis

Pag-IBIG Fund Circular No. 460:

- Maximum Fund Salary: PHP 10,000
- Employee: 1% at PHP 1,500 and below; 2% above PHP 1,500
- Employer: 2%
- Maximum mandatory employee contribution: PHP 200
- Maximum mandatory employer contribution: PHP 200

## New files

- `database/migrations/2026_08_08_121700_create_benefit_contribution_records_table.php`
- `app/Models/BenefitContributionRecord.php`
- `app/Http/Requests/Payroll/BenefitsRecordIndexRequest.php`
- `app/Http/Controllers/Payroll/BenefitsRecordController.php`
- `app/Services/Payroll/BenefitContributionPostingService.php`
- `app/Services/Payroll/BenefitRecordsService.php`
- `resources/views/payroll/benefits_records/index.blade.php`
- `app/Console/Commands/BackfillBenefitContributionRecords.php`

## Updated files

- `app/Http/Controllers/Payroll/PayrollController.php`
- `app/Models/EmployeeBiometric.php`
- `app/Models/PayrollEmployeeSalary.php`
- `app/Models/PayrollItem.php`
- `app/Models/Payroll.php`
- `app/Services/Payroll/GovernmentDeductionService.php`
- `routes/web.php`
- `database/seeders/PermissionSeeder.php`
- `resources/views/layouts/sidebar.blade.php`
- `resources/views/payroll/payrolls/show.blade.php`

## Installation

```bash
php artisan migrate
php artisan db:seed --class=PermissionSeeder
php artisan permission:cache-reset
php artisan optimize:clear
```

Assign the permission `benefits-records.view` to the HR/Payroll roles that should see the new module.

The route is:

```text
/benefits-records
```

## Existing finalized payrolls

New finalizations post automatically. For payrolls finalized before this update, run the optional one-time backfill:

```bash
php artisan benefits:backfill-finalized
```

Or a specific contribution period:

```bash
php artisan benefits:backfill-finalized --year=2026 --month=8
```

The command is idempotent because each Benefits Record is uniquely keyed to `payroll_item_id` and uses `updateOrCreate()`.
