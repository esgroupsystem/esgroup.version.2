# Biometrics Sync Update — 2026-08-08

## Objective

Replace the queue-dependent Mirasol-only attendance synchronization workflow with a multi-source **Biometrics Sync** workflow that can synchronize Mirasol, Balintawak, Gonzales, any selected combination, or all configured CrossChex accounts directly from the browser.

## New synchronization architecture

The web page no longer dispatches `CrossChexSyncLogsJob`.

Instead, the browser performs a controlled page-by-page synchronization:

1. User selects a date range and one or more biometric sources.
2. `sync-start` creates a short-lived server-side sync session in Laravel Cache.
3. The browser calls `sync-step` repeatedly.
4. Each `sync-step` downloads **one CrossChex API page** and performs one batch `INSERT IGNORE` operation.
5. The UI immediately displays source, API page, new records, already-saved records, invalid records and overall progress.
6. When an API source is complete, the process advances to the next selected source.
7. If CrossChex reports a rate limit, the browser waits automatically and retries the same page.
8. The sync job ID is saved in browser local storage so a page refresh can resume the cached synchronization session.

This means the normal Biometrics Sync page does **not** require:

```bash
php artisan queue:work database --queue=default --tries=10 --timeout=1200
```

and it does **not** require a cron job.

### Important limitation

This is an on-demand browser-driven synchronization. The browser/page must initiate and continue the work. A truly unattended 24/7 synchronization while nobody has the page open still requires one of the following:

- a supported CrossChex webhook/push integration, or
- Laravel scheduler/cron, or
- a queue worker/service.

The supplied implementation intentionally avoids all three for the normal user-click sync workflow.

## Performance changes

### Old behavior

For each API page the old queue job:

- selected existing CrossChex IDs from MySQL;
- counted existing/new IDs in PHP;
- used `upsert` to update existing attendance rows again.

Repeated synchronization of a previously imported range therefore caused unnecessary reads and writes.

### New behavior

The new `CrossChexAttendanceSyncService`:

- deduplicates duplicate IDs within the API page in memory;
- performs one batch `insertOrIgnore()` per API page;
- relies on the existing unique index on `(crosschex_account, crosschex_id)`;
- does not SELECT every existing ID before writing;
- does not UPDATE an attendance transaction that is already stored.

Existing records are counted as **Already Saved / Ignored** and are not rewritten.

Default batch size:

```env
CROSSCHEX_SYNC_PER_PAGE=200
```

If the CrossChex tenant is stable with larger pages, this can be tested at 300 or 500 to reduce the number of API calls. Use 200 first because it is the existing proven page size in this project.

## Multi-source selection

The Biometrics Sync page now reads all fully configured accounts from `config/services.php` and allows:

- Mirasol only
- Balintawak only
- Gonzales only
- any selected combination
- Sync All

An account appears only when URL, API key and API secret are all configured.

## CrossChex employee UUID improvement

The original `mirasol_biometrics_logs.employee_id` column is an unsigned BIGINT, but CrossChex employee identifiers may be string/UUID values.

The update adds:

```text
mirasol_biometrics_logs.source_employee_id VARCHAR(100) NULL
```

New API employee identifiers are preserved in this string field without truncation. Numeric IDs are also copied into the legacy `employee_id` field for backward compatibility.

`EmployeeBiometricSyncService` now prefers `source_employee_id` and falls back to the legacy `employee_id` value.

## Files added

```text
app/Http/Requests/Biometrics/StartBiometricsSyncRequest.php
app/Http/Requests/Biometrics/StepBiometricsSyncRequest.php
app/Services/Biometrics/CrossChexAttendanceSyncService.php
app/Services/Biometrics/CrossChexSyncCoordinator.php
database/migrations/2026_08_08_145000_add_source_employee_id_to_mirasol_biometrics_logs_table.php
```

## Files updated

```text
.env.example
app/Console/Commands/CrossChexSyncAttendance.php
app/Http/Controllers/HR_Department/MirasolBiometricsLogController.php
app/Jobs/CrossChexSyncLogsJob.php
app/Models/MirasolBiometricsLog.php
app/Services/Biometrics/EmployeeBiometricSyncService.php
app/Services/CrossChexServiceFactory.php
config/services.php
resources/views/biometrics/employees/index.blade.php
resources/views/hr_department/mirasol_logs/index.blade.php
resources/views/layouts/sidebar.blade.php
routes/web.php
```

## Deployment

Copy the updated files, then run:

```bash
php artisan migrate
php artisan optimize:clear
```

No queue worker command is required for the Biometrics Sync page.

No cron entry is required for the Biometrics Sync page.

## Optional `.env` tuning

The following values are optional because defaults already exist in `config/services.php`:

```env
CROSSCHEX_SYNC_PER_PAGE=200
CROSSCHEX_SYNC_SESSION_MINUTES=120
CROSSCHEX_SYNC_RATE_LIMIT_RETRY_SECONDS=31
```

Your existing CrossChex URL/key/secret values stay in `.env` only.

## Security action required

Real API credentials had been present in the project's `.env.example` and were also exposed in supplied material. This update removes real credentials from `.env.example`, but changing the file does **not** remove credentials from Git history.

Rotate the affected CrossChex API keys/secrets and any other exposed secrets. Keep the new credentials only in `.env`; do not commit `.env` or paste real secrets into `.env.example`.

## Verification performed

All changed PHP and Blade files were checked with `php -l` and passed syntax validation.

The JavaScript embedded in the Biometrics Sync page was extracted and checked with `node --check` and passed syntax validation.

Full Laravel boot / route-list validation could not run in the sandbox PHP environment because the provided runtime is missing the `mbstring` extension (and Pint also reported missing `xml`). This is an environment limitation, not a PHP syntax error in the update.
