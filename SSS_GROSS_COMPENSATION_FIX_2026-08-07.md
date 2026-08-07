# SSS Gross Compensation Basis Fix — 2026-08-07

## Confirmed issue

The SSS bracket engine itself was correct, but `PayrollComputationService` supplied the wrong monthly compensation basis.

Legacy basis excluded recurring allowance/additions:

- Previous 2nd cutoff: PHP 11,000.00 - PHP 2,215.08 = PHP 8,784.92
- Current 1st cutoff: PHP 11,000.00 - PHP 4,882.20 = PHP 6,117.80
- Legacy SSS basis: PHP 14,902.72
- MSC: PHP 15,000.00
- Employee SSS: PHP 750.00

This exactly explains the PHP 750.00 shown in the payroll screen.

## Correct basis

The payroll now uses each cutoff's `gross_pay` before government and salary/loan deductions as the SSS compensation basis.

Reported employee example:

- Previous 2nd cutoff gross: PHP 11,784.92
- Current 1st cutoff gross: PHP 9,117.80
- Monthly SSS compensation: PHP 20,902.72
- Official SSS range: PHP 20,750.00 to PHP 21,249.99
- Total MSC: PHP 21,000.00
- Regular SS MSC: PHP 20,000.00
- MPF MSC: PHP 1,000.00
- Employee Regular SS: PHP 1,000.00
- Employee MPF: PHP 50.00
- Correct Employee SSS deduction: PHP 1,050.00
- Employer SS + MPF: PHP 2,100.00
- Employer EC: PHP 30.00
- Employer total including EC: PHP 2,130.00
- Total contribution: PHP 3,180.00

## Compatibility fix

When calculating the current first cutoff, the service now reads `gross_pay` from the previous second-cutoff payroll item before reading legacy SSS-basis metadata. This means an older second-cutoff payroll item can still be used correctly without regenerating it solely to repair the old metadata.

## Files changed

- `app/Services/Payroll/PayrollComputationService.php`
- `config/payroll.php`
- `tests/Unit/SssContributionServiceTest.php`

## Deployment

No database migration is required for this correction.

```bash
php artisan down
php artisan optimize:clear
php artisan up
```

If the affected first-cutoff payroll is still a draft, delete that draft and regenerate it. Existing payroll rows are stored calculations and will not change simply by replacing PHP files.

After regeneration, verify the payroll item audit metadata:

- `meta.government_monthly_cycle_basis.amount` = `20902.72`
- `meta.government_raw_before_schedule.sss_basis` = `20902.72`
- `meta.government_raw_before_schedule.sss_msc` = `21000.00`
- `sss_employee` = `1050.00`

If a payroll has already been finalized/released, do not overwrite it silently; process a controlled payroll correction/adjustment with an audit trail.
