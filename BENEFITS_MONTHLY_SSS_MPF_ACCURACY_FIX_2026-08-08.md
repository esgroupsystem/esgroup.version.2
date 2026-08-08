# Benefits Monthly SSS / MPF Accuracy Fix — 2026-08-08

## Root cause

The previous Benefits Records implementation stored the government amounts attached to each individual payroll cutoff. That is not correct for a monthly SSS contribution ledger because the SSS Monthly Salary Credit (MSC) must be selected from the employee's total actual remuneration for the contribution month.

For the company's payroll cycle, July is:

- Business 1st cutoff: June 26 to July 10
- Business 2nd cutoff: July 11 to July 25
- July SSS compensation = gross from both finalized cutoffs

The old print could therefore show only PHP 450 employee SSS and could omit MPF even when the complete July compensation belongs to an MPF bracket.

## Correct July sample

Employee: John Gabriel Medina

- 1st cutoff gross (June 26-July 10): PHP 11,784.92
- 2nd cutoff gross (July 11-July 25): PHP 9,117.80
- Total July SSS compensation: PHP 20,902.72

Under SSS Circular No. 2024-006:

- Compensation range: PHP 20,750.00 to PHP 21,249.99
- Total MSC: PHP 21,000.00
- Regular SS MSC: PHP 20,000.00
- MPF MSC: PHP 1,000.00
- Employee Regular SS: PHP 1,000.00
- Employee MPF: PHP 50.00
- Employee SSS total: PHP 1,050.00
- Employer Regular SS: PHP 2,000.00
- Employer MPF: PHP 100.00
- Employer EC: PHP 30.00
- Employer SSS total including EC: PHP 2,130.00
- Combined SSS: PHP 3,180.00

For fixed Monthly Basic Salary PHP 22,000.00:

- PhilHealth employee: PHP 550.00
- PhilHealth employer: PHP 550.00
- PhilHealth combined: PHP 1,100.00
- Pag-IBIG employee: PHP 200.00
- Pag-IBIG employer: PHP 200.00
- Pag-IBIG combined: PHP 400.00

Monthly employee government contribution: PHP 1,800.00
Monthly company contribution: PHP 2,880.00
Monthly combined contribution: PHP 4,680.00

## New processing rule

1. Generate and finalize business 1st cutoff (26-10).
2. The system does not post the monthly Benefits Record yet.
3. Generate business 2nd cutoff (11-25). The system requires the 1st cutoff to already be finalized.
4. The 2nd-cutoff draft uses 1st-cutoff gross + 2nd-cutoff gross for the SSS monthly basis.
5. When Finalize is clicked on the 2nd cutoff, the system performs a final monthly government true-up in the same database transaction.
6. One canonical monthly Benefits Record per employee is posted with the full SSS Regular SS / MPF / EC breakdown plus PhilHealth and Pag-IBIG employee/company shares.

## Files added

- `app/Services/Payroll/MonthlyGovernmentContributionService.php`
- `app/Services/Payroll/MonthlyGovernmentReconciliationService.php`
- `app/Console/Commands/RepairMonthlyGovernmentContributions.php`
- `database/migrations/2026_08_08_131500_upgrade_benefit_records_to_monthly_cycle.php`
- `tests/Unit/Services/Payroll/MonthlyGovernmentContributionServiceTest.php`

## Files updated

- `app/Http/Controllers/Payroll/PayrollController.php`
- `app/Models/BenefitContributionRecord.php`
- `app/Services/Payroll/BenefitContributionPostingService.php`
- `app/Services/Payroll/BenefitRecordsService.php`
- `app/Services/Payroll/PayrollComputationService.php`
- `resources/views/payroll/benefits_records/index.blade.php`
- `resources/views/payroll/benefits_records/overall.blade.php`
- `resources/views/payroll/benefits_records/print.blade.php`

## Installation

```bash
php artisan migrate
php artisan optimize:clear
```

## Repair already-finalized July test payroll

First run a dry run. It does not save changes:

```bash
php artisan payroll:repair-monthly-government --year=2026 --month=7 --group=1
```

Review the table. For the sample employee the expected monthly gross is `20,902.72`, SSS MSC is `21,000.00`, MPF MSC is `1,000.00`, and employee SSS becomes `1,050.00` for the complete month.

Then apply the audited repair:

```bash
php artisan payroll:repair-monthly-government --year=2026 --month=7 --group=1 --apply
```

The apply command:

- stores old/new government values in payroll-item audit metadata;
- corrects the closing-cutoff government true-up and net pay;
- rebuilds the monthly Benefits Record;
- removes the old duplicate/per-cutoff Benefits rows for that employee/month.

If Group 2 also has July payroll, run the same command with `--group=2`.

## Testing note

All changed PHP/Blade files pass `php -l` syntax checks. The Laravel test runner cannot start in the provided sandbox because the sandbox PHP installation does not have `mbstring` (`mb_split()` is missing). A standalone execution of the statutory calculator was run and returned the exact sample values above.
