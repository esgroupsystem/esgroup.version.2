# ES Group v3.4 — Flexible Shift System-Wide Fix

Date: 2026-08-07

## Objective

Treat `Flexible Shift` as a valid plotted work schedule throughout payroll and attendance checking even though flexible schedules intentionally have no fixed scheduled Time In / Time Out.

## Exact bug

The payroll attendance audit used this legacy check:

```php
$hasSchedule = ! empty($row->scheduled_time_in)
    && ! empty($row->scheduled_time_out);
```

That rule is correct for a Regular Shift but incorrect for a Flexible Shift. Flexible schedules intentionally store `time_in` and `time_out` as `NULL` and are validated by total required clock minutes instead.

As a result, correctly plotted flexible employees were incorrectly tagged `Missing Schedule` in payroll attendance audit rows.

## Correct business rule

### Regular Shift

A valid working schedule requires both plotted Time In and Time Out.

### Flexible Shift

A valid schedule does **not** require fixed Time In / Time Out.

Compliance is based on the employee workday type:

- 8 paid hours + 1 hour lunch = 9 required clock hours.
- 9 paid hours + 1 hour lunch = 10 required clock hours.
- Flexible shift has no late deduction against a fixed start time.
- If actual clock duration is below the required clock duration, the shortage is treated as undertime.
- A single unmatched biometric log still follows the existing automatic half-day policy.

## Files changed

- `app/Models/DailyAttendanceSummary.php`
- `app/Services/Payroll/DailyAttendanceSummaryService.php`
- `app/Services/Payroll/PayrollComputationService.php`
- `resources/views/payroll/attendance_summary/table.blade.php`
- `resources/views/payroll/items/partials/attendance-audit-table.blade.php`
- `resources/views/payroll/payrolls/show_item.blade.php`
- `tests/Unit/Models/DailyAttendanceSummaryFlexibleScheduleTest.php`
- `tests/Unit/Services/Payroll/AttendancePayrollRulesTest.php`

## Additional integrity improvements

`DailyAttendanceSummaryService` now persists the schedule source ID, adjustment source ID, holiday source ID, CrossChex snapshot, raw biometric count, biometric flag, adjustment snapshot fields, computed flags, and computation timestamp that already exist in the `daily_attendance_summaries` schema.

Newly rebuilt summary rows also store:

```text
meta.schedule_mode = flexible | regular | none
meta.has_configured_schedule = true | false
```

This makes later payroll checks explicit instead of guessing schedule validity only from scheduled timestamps.

## UI behavior after update

A valid flexible row shows:

```text
Flexible Shift
10 clock hr(s)
9 paid hr(s) + lunch; no fixed Time In/Out
```

It will no longer show `Missing Schedule` merely because scheduled Time In / Time Out are blank.

Legitimate issues still remain visible, for example:

- Missing Pair Log
- Half Day
- Undertime
- Absent / Unpaid
- No Schedule

## Deployment

No new migration is required.

After replacing the files:

```bash
composer dump-autoload -o
php artisan optimize:clear
```

Recommended after deployment:

1. Open Attendance Summary.
2. Rebuild the affected cutoff so the new schedule-source and audit metadata are stored.
3. Regenerate only draft payroll batches that should use the rebuilt attendance summary.
4. Do not silently rewrite finalized payroll batches.

## Validation

Target PHP files and modified Blade source files passed PHP syntax validation.

The sandbox PHP CLI cannot execute the normal Laravel PHPUnit bootstrap because its PHP build does not include `mbstring` (`mb_split()` is unavailable). Regression tests are included and can be run on the application server with the required Laravel PHP extensions installed:

```bash
php artisan test --filter=DailyAttendanceSummaryFlexibleScheduleTest
php artisan test --filter=AttendancePayrollRulesTest
```
