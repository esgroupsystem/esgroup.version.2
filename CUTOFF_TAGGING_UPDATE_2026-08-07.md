# Payroll Cutoff Tagging Update — 2026-08-07

## Objective
User-facing cutoff names were corrected without changing payroll computation, stored cutoff values, date-range logic, government-deduction logic, or historical data.

## Business Display Convention
- **1st Cutoff:** 26th through 10th
- **2nd Cutoff:** 11th through 25th

## Legacy Internal Mapping Preserved
The existing database/application keys are intentionally unchanged:
- `cutoff_type = second` -> 26th through 10th -> displayed as **1st Cutoff (26-10)**
- `cutoff_type = first` -> 11th through 25th -> displayed as **2nd Cutoff (11-25)**

The same mapping applies to salary deduction/release schedule keys:
- `second_cutoff` -> displayed as **1st Cutoff Only (26-10)**
- `first_cutoff` -> displayed as **2nd Cutoff Only (11-25)**

## What Was Not Changed
- Payroll period calculations
- Attendance dates
- SSS / PhilHealth / Pag-IBIG computation
- Payroll item calculations
- Existing database values
- Existing migrations
- Payroll-number generation
- Historical payroll records

## Updated Areas
- Payroll Create cutoff selector
- Payroll List cutoff filter
- Payroll model cutoff label shown in detail/print pages
- Attendance Summary selector and period label
- Manual Biometrics selector and period label
- HR Mirasol Biometrics Logs selector and period label
- Employee Salary deduction/release schedule labels
- Employee Salary cutoff preview headers
- Central display mapping in `config/payroll.php`

## Deployment
No migration is required. After replacing the files, run:

```bash
php artisan optimize:clear
```

This is a display/tagging-only update.
