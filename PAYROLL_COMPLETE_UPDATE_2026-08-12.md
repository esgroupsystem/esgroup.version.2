# Payroll Complete Update — 2026-08-12

This build keeps the earlier rest-day qualification, government-benefit settlement, Benefits Records, and Payroll Transaction Logs changes, and adds the requested Benefits Overall/cutoff/name/time updates.

## Benefits Overall

The Benefits Overall register is now a compact spreadsheet-style contribution table. It displays only the contribution columns needed for reporting:

- SSS PREMIUM: EE, ER, EC, Total
- MPF: EE, ER, Total
- TOTAL SSS/MPF
- PHILHEALTH: EE, ER, Total
- PAG-IBIG: EE, ER, Total

Employee rows use payroll surname-first display names and the table includes one overall totals row.

## Payroll cycle month / cutoff selection

The selected month is now the payroll cycle month for both cutoffs throughout Payroll, Attendance Summary, Manual Biometrics, and HR Biometrics views.

Examples:

- Select July 2026
  - 1st Cutoff = June 26, 2026 to July 10, 2026
  - 2nd Cutoff = July 11, 2026 to July 25, 2026
- Select August 2026
  - 1st Cutoff = July 26, 2026 to August 10, 2026
  - 2nd Cutoff = August 11, 2026 to August 25, 2026

The legacy internal cutoff keys remain unchanged for database compatibility (`second` = business 1st cutoff / 26-10, `first` = business 2nd cutoff / 11-25).

## Employee names and ordering

Payroll/Biometrics display names are surname-first without rewriting source biometric identity data. Example:

`Lenberd Arazo Ilaw` -> `Ilaw, Lenberd Arazo`

Employee directory/salary listings put Active employees first and Inactive employees below, with surname A-Z ordering inside each section. Active/Inactive filtering remains available in Biometrics Employees and is also available in Employee Salary Records.

## Attendance seconds / flexible schedule

Biometric Time In/Out seconds are discarded before payroll attendance calculations. Example:

`6:01:52 AM` is computed as `6:01 AM`.

The same minute normalization is applied to approved manual actual Time In/Out. Flexible schedules therefore evaluate required clock minutes at minute precision; for example `9:01 AM` to `7:01 PM` is exactly 600 clock minutes and qualifies as complete when the flexible shift requires 600 clock minutes.

Raw biometric logs remain unchanged; only payroll attendance computation/display normalization ignores seconds.

## Validation completed in this build

- PHP syntax checks passed for all modified/new PHP and Blade files.
- `git diff --check` passed.
- Targeted runtime checks passed for July/August cutoff mapping, surname-first formatting, ignoring biometric seconds, and the 9:01 AM to 7:01 PM 600-minute flexible example.
- The full Laravel/PHPUnit suite could not start in the supplied container because its PHP CLI is missing `dom`, `mbstring`, `xml`, and `xmlwriter` extensions.

## Deployment

For an existing installation that already applied the previous payroll settlement/audit update, this round adds no new database migration. It is still safe to run:

```bash
php artisan migrate
php artisan optimize:clear
```

Run the full automated test suite in staging/production PHP after confirming the required PHP extensions are installed.
