# Flexible Shift "Missing Schedule" Hotfix

Date: 2026-08-07
Version: 3.4.1

## Problem

Payroll Attendance Audit could still display `Missing Schedule` for a valid Flexible Shift.
The affected rows typically had valid biometric logs and remarks such as:

`Flexible shift completed 10 clock hours / 9 paid hours.`

but older `daily_attendance_summaries` snapshots could have a blank/legacy `shift_name`
or `schedule_status = no_schedule`.

## Root Cause

`DailyAttendanceSummary::isFlexibleShift()` only inspected `shift_name`.
The audit view therefore treated older flexible rows as regular rows with blank scheduled Time In/Out
and added the `Missing Schedule` issue.

## Fix

Flexible detection is now backward-compatible and checks, in this order:

1. `meta.schedule_mode = flexible`
2. `shift_name` contains `flexible`
3. eager-loaded `plottingSchedule`
4. historical `schedule_remarks` / `remarks` containing `Flexible shift`

Configured schedule detection also accepts:

- flexible shift evidence
- `meta.has_configured_schedule = true`
- a non-null `plotting_schedule_id`
- regular Time In + Time Out for regular shifts

The Payroll Detail and Attendance Summary controllers now eager-load `plottingSchedule`.
All relevant audit/summary Blade fallbacks use the same compatibility logic.

## Files Changed

- app/Models/DailyAttendanceSummary.php
- app/Http/Controllers/Payroll/PayrollController.php
- app/Http/Controllers/Payroll/AttendanceSummaryController.php
- resources/views/payroll/items/partials/attendance-audit-table.blade.php
- resources/views/payroll/attendance_summary/table.blade.php
- resources/views/payroll/payrolls/show_item.blade.php
- tests/Unit/Models/DailyAttendanceSummaryFlexibleScheduleTest.php

## Deployment

No migration is required.

Run:

```bash
composer dump-autoload -o
php artisan optimize:clear
```

Existing payroll detail rows that already contain Flexible Shift remarks/meta will stop showing
`Missing Schedule` immediately after the code/cache update. Rebuilding Attendance Summary is still
recommended so the current canonical schedule snapshot is stored in all rows.

## Validation

All changed PHP and Blade files pass `php -l` syntax validation.
The included Laravel test cannot run in the provided container because PHP `mbstring` is not installed
(`Illuminate\\Support\\mb_split()` is unavailable). Regression cases were added for legacy flexible
rows detected from metadata and remarks.
