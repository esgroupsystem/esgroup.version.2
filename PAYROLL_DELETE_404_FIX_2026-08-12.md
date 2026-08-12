# Payroll delete 404 fix

Updated 2026-08-12.

## Fixed

- Deleting a draft payroll now redirects directly to `/payroll`.
- Added a backward-compatible `/payroll/v2` route that redirects to `/payroll`.
- This prevents the custom 404 page when an old/stale payroll URL remains in a browser bookmark or route reference.

## Deploy

After replacing the project files, run:

```bash
php artisan optimize:clear
```

If your deployment uses cached routes, rebuild them only after confirming the application starts normally.
