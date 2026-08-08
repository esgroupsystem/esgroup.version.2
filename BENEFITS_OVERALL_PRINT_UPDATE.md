# Benefits Overall / Print Update

## Added

- Sidebar link: **Benefits Overall** under Payroll Process.
- Route: `benefits-records.overall` (`GET /benefits-records/overall`).
- Route: `benefits-records.print` (`GET /benefits-records/print`).
- Falcon-styled Benefits Overall dashboard.
- Exact printable A4 landscape contribution register.
- SSS detailed print section including MSC, Regular SS, MPF, EC, employee share, employer share and combined contribution.
- PhilHealth detailed print section.
- Pag-IBIG / HDMF detailed print section.
- Consolidated employee/company contribution section.
- Company totals and overall totals.
- Print uses finalized contribution snapshots and does not recompute historical values.

## Files changed

- `routes/web.php`
- `app/Http/Controllers/Payroll/BenefitsRecordController.php`
- `app/Services/Payroll/BenefitRecordsService.php`
- `resources/views/layouts/sidebar.blade.php`
- `resources/views/payroll/benefits_records/index.blade.php`

## Files added

- `resources/views/payroll/benefits_records/overall.blade.php`
- `resources/views/payroll/benefits_records/print.blade.php`

## Installation

No new migration is required for this update.

After copying the files, run:

```bash
php artisan optimize:clear
```

The same existing permission is used:

```text
benefits-records.view
```
