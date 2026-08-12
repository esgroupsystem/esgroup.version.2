# ES Group Payroll Rules, Benefit Settlement, Benefits Records, and Audit Logs

Date: 2026-08-12
Target: Laravel payroll project in `esgroup.version.2`

This document supersedes earlier draft notes where they conflict with the implementation described below.

## 1. Rest-day qualification rule

Company payroll rule implemented for each cutoff:

- Count unique dates with a valid biometric **Time In AND Time Out**.
- Default minimum: **3 valid logged days** in the cutoff.
- If the employee has fewer than 3 valid logged days, otherwise-unworked scheduled rest days become unpaid for a monthly-paid employee.
- If the cutoff contains an approved attendance adjustment or leave, the employee qualifies for the rest-day exception and the otherwise-unworked rest day remains paid.
- A rest day that the employee actually worked is never removed by this qualification rule; worked-rest-day premium computation remains separate.
- Daily-paid employees already receive base pay from payable worked hours, so the rule does not create a second deduction for an ordinary unworked rest day.

Configuration:

```php
payroll.attendance.rest_day_minimum_valid_log_days = 3
payroll.attendance.rest_day_adjustment_or_leave_exception = true
```

The Payroll Item screen shows the qualification result, valid log dates, exception reason, unpaid rest-day dates, and deduction used in the computation.

## 2. Benefits Records format and settlement tracking

Benefits Records are now employee-first and simplified around the three statutory programs:

- SSS
- PhilHealth
- Pag-IBIG

For each program the employee record shows:

- Government/member number
- Employee statutory share due
- Amount actually collected from payroll
- Employer/company share
- Total contribution

The screen also shows the employee's **unrecovered/advanced employee share** when payroll cash was not sufficient to collect the entire statutory employee portion.

Important distinction:

- **Employee Due** = statutory employee contribution for the completed monthly contribution cycle.
- **Collected in Payroll** = actual signed employee cash withholding after true-ups, caps, and approved reimbursements.
- **Unrecovered / Advanced** = statutory employee share not recovered through payroll.

Inactive/resigned employees remain visible for a selected month when a finalized Benefits Record exists for that month.

## 3. Resigned employee / negative closing-cutoff payroll

Business cutoff mapping used by this project remains:

- Business 1st cutoff: **26-10** (legacy database `cutoff_type = second`)
- Business 2nd cutoff: **11-25** (legacy database `cutoff_type = first`)

The 11-25 payroll is the monthly government-contribution reconciliation point.

### Settlement-only carry-forward

If an employee appears in the finalized 26-10 payroll but is already inactive/resigned before 11-25, payroll generation creates a **Separated / Settlement** closing payroll item with zero attendance/gross instead of dropping the employee completely.

That row exists only to settle the completed month's statutory benefits and any approved last-pay refund.

### Available HR settlement actions

**Auto Cap** (default)

- Calculate the exact monthly statutory liability first.
- Subtract what was already collected in 26-10.
- On 11-25, collect positive employee government deductions only up to cash actually available after other deductions.
- Government deductions by themselves therefore cannot make the employee's net pay negative.
- Any employee share not collected remains visible in Benefits Records as unrecovered.

**Employer Advance**

- Do not withhold positive employee government shares from the 11-25 payroll.
- Preserve the statutory monthly contribution and employer shares.
- Track the unrecovered employee portion as employer-advanced/unrecovered for accounting treatment.
- This is the recommended system action for a resigned employee with zero closing-cutoff gross when the company will settle/remit the contribution without taking cash from that payroll.

**Collect Full**

- Withhold the full closing employee share only when the payroll can cover it.
- Validation blocks this action if it would produce a negative net pay.

### Approved reimbursement / final-pay credit

HR can enter SSS, PhilHealth, and/or Pag-IBIG employee reimbursement amounts with a required reason.

The reimbursement:

- becomes a real payroll credit after current positive collection has been capped/advanced;
- is limited so HR cannot manually refund more than the prior employee withholding remaining after automatic monthly true-up; and
- does **not** erase the statutory monthly contribution liability from Benefits Records.

This supports the scenario where PhilHealth/Pag-IBIG were withheld in 26-10 and HR/accounting approves returning some or all of that employee cash in the separated employee's last-pay settlement.

Do not solve the negative-net case by deleting SSS from the statutory contribution calculation. Each government program is tracked independently; the settlement layer changes employee payroll cash collection, not the underlying monthly liability.

## 4. Payroll Transaction Logs

A separate payroll audit table has been added:

`payroll_audit_logs`

The sidebar includes **Payroll Transaction Logs** for authorized users.

Each audit row can retain:

- user who performed the action;
- request/correlation ID;
- payroll and payroll-item reference;
- employee reference;
- garage/payroll group;
- module and action;
- before and after values;
- contextual values;
- IP address and user agent where available; and
- timestamp.

Audit coverage includes:

- Payroll and Payroll Items
- Payment/deduction logs and payroll report logs
- Attendance adjustments
- Benefit Contribution Records
- Benefit Settlement actions
- Employee biometric/profile changes
- Employee payroll rate/salary profile changes
- Employee recurring/other salary deductions
- Employee plotting schedules
- Holidays
- Manual WFH biometric encodings
- CrossChex biometric sync start/completion/failure summaries

Raw imported machine punches stay in the existing biometric log table rather than being duplicated row-for-row into the payroll audit table.

## 5. New permissions

- `payroll-benefit-settlements.manage`
- `payroll-audit-logs.view`

The PermissionSeeder creates these permissions. Existing HR/payroll roles still need the permissions assigned through the project's role/permission management flow unless those roles automatically receive all new permissions.

## 6. Database migrations

New migrations:

- `2026_08_09_180000_create_payroll_benefit_settlements_table.php`
- `2026_08_09_180100_add_collection_tracking_to_benefit_contribution_records_table.php`
- `2026_08_09_180200_create_payroll_audit_logs_table.php`

The Benefits Records migration backfills historical contribution rows as fully collected so old finalized records do not incorrectly appear as zero-collected after deployment.

## 7. Deployment commands

Back up the production database first, then run from the Laravel project root:

```bash
php artisan migrate
php artisan db:seed --class=PermissionSeeder
php artisan permission:cache-reset
php artisan optimize:clear
```

After verification, use your normal production config/route/view cache deployment commands if applicable.

## 8. Recommended acceptance tests

1. Employee has only 1-2 complete biometric log days, no adjustment/leave, and an otherwise-unworked scheduled rest day: monthly payroll removes that rest-day amount.
2. Same case with an approved attendance adjustment: rest-day qualification exception applies and no rest-day qualification deduction is taken.
3. Same case with leave: rest-day qualification exception applies.
4. Employee actually works the rest day: worked-rest-day premium remains; the qualification rule does not remove it.
5. Employee works 26-10 and resigns before 11-25: 11-25 generates a Separated / Settlement row even with zero attendance/gross.
6. Default Auto Cap on the separated row: government withholding does not push net pay below zero; Benefits Records still shows monthly due and unrecovered amount.
7. Employer Advance: positive closing employee government deductions become zero while monthly statutory liability remains in Benefits Records.
8. Approved prior-cutoff PhilHealth/Pag-IBIG refund: reimbursement becomes a payroll credit and is shown in the settlement metadata/audit logs.
9. Finalize 11-25: Benefits Records shows statutory Due, Collected in Payroll, Company Share, Total, and Unrecovered/Advanced for the employee.
10. Modify employee rate/deduction, schedule, attendance adjustment, benefits settlement, or payroll: Payroll Transaction Logs show the user and before/after values.

## 9. Validation performed in the patch workspace

Completed successfully:

- PHP syntax lint on changed/new PHP and Blade files.
- Direct Blade compiler check on the modified payroll/benefits/audit/sidebar views.
- Git whitespace/diff check.
- Targeted custom regression checks for:
  - Auto Cap;
  - Employer Advance + approved reimbursements;
  - 3-valid-log rest-day rule;
  - adjustment exception;
  - worked-rest-day preservation;
  - Benefits Records Due vs Collected vs Unrecovered totals; and
  - stable employee matching for separation carry-forward.

The full PHPUnit/Artisan test runner cannot start in the supplied patch container because its PHP CLI build is missing Laravel/PHPUnit runtime extensions `dom`, `mbstring`, `xml`, and `xmlwriter`. This is an environment limitation; it should still be run in staging/production CI with the normal required PHP extensions before production deployment.

## 10. Data/process note

This code intentionally separates **statutory monthly benefit liability** from **cash collected from a particular payroll cutoff**. That separation makes resignation/final-pay cases auditable and avoids hiding a statutory contribution merely to make net pay non-negative.

HR/accounting should still approve the business treatment of unrecovered employee shares and any final-pay reimbursement before payroll finalization.
