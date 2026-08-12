# ES Group Payroll Settlement, Benefits Register, Rest-Day Audit, and Payroll Audit Logs

Date: 2026-08-09
Target: Laravel 12 / PHP 8.3+ / MySQL

## Scope

This patch adds four related payroll controls:

1. **3-day rest-day attendance eligibility audit**
   - Counts unique cutoff dates with a valid time-in and time-out.
   - Default threshold: 3 logged days per cutoff.
   - Approved leave or approved payroll attendance adjustment creates an override.
   - The flag is visible in the Payroll Item Attendance Audit.
   - It intentionally **does not deduct unworked rest-day salary from monthly-paid employees** and never removes premium pay for work actually rendered on a rest day.

2. **Spreadsheet-style Benefits Records**
   - Employee and company only as the primary identifying columns.
   - Grouped SSS PREMIUM columns: EE / ER / EC / TOTAL.
   - Grouped MPF columns: EE / ER / TOTAL.
   - TOTAL SSS/MPF.
   - Grouped PhilHealth EE / ER / TOTAL.
   - Grouped Pag-IBIG EE / ER / TOTAL.
   - Index, Overall, and Print screens now use the same register structure.
   - Historical posted employees remain visible even if they are now inactive/resigned.

3. **Closing-cutoff government deduction settlement**
   - Business 1st cutoff remains 26-10 (legacy database key `second`).
   - Business 2nd cutoff remains 11-25 (legacy database key `first`).
   - Default schedule:
     - SSS: business 2nd cutoff (11-25).
     - PhilHealth: business 1st cutoff (26-10).
     - Pag-IBIG: business 1st cutoff (26-10).
   - A resigned/inactive employee who appeared in the finalized opening cutoff is carried into the closing cutoff as a **settlement-only payroll item** even when the employee is no longer in the active roster.
   - Exact monthly statutory liability is calculated from the complete monthly cycle.
   - Payroll cash collection is separated from statutory liability so SSS on a zero-pay closing cutoff does not automatically create a negative net pay.

   Settlement actions:
   - `Auto Cap` - collect only the positive employee government share that available payroll cash can cover.
   - `Employer Advance` - do not collect positive employee government shares on the closing cutoff; track the employee share as unrecovered/employer-advanced while retaining the statutory contribution record.
   - `Collect Full` - collect the full closing employee share only when it will not create a negative net pay; otherwise validation blocks the action.

   Optional employee reimbursement fields are available for SSS, PhilHealth, and Pag-IBIG. They are payroll credits only; they do not cancel the statutory monthly liability. Manual reimbursement is capped after any automatic true-up credit so an employee cannot be refunded more than was actually withheld.

4. **Payroll Audit Logs**
   - New table: `payroll_audit_logs`.
   - Captures model create/update/delete changes with user, request correlation ID, payroll references, employee references, before/after JSON, IP, user agent, and timestamp.
   - Covered modules include:
     - Payroll and Payroll Items
     - Payroll payment/report logs
     - Attendance adjustments
     - Benefit contribution records and settlement actions
     - Employee biometrics/profile changes
     - Employee payroll rates
     - Plotting schedules
     - Holidays
     - Manual WFH biometric encodings
   - Raw machine biometric punches are not duplicated into the audit table because the source biometric log table already serves as the high-volume immutable event source.
   - Sidebar page: **Payroll Process > Payroll Audit Logs**.

## Important payroll architecture decision

The project currently treats monthly-paid employees as:

`monthly salary / 2 - attendance loss + approved additions/premiums`

For that pay model, ordinary unworked rest days are inside the monthly salary base. Therefore, the new 3-day rule is implemented as an auditable company-policy flag rather than a monetary rest-day deduction. If management wants a category where ordinary rest days are unpaid, that should be implemented as a separate, legally reviewed daily/314-day wage model instead of silently subtracting rest days from the existing monthly/365-day model.

## Closing-cutoff example

Scenario:

- Business 1st cutoff (26-10): employee has earnings and PhilHealth/Pag-IBIG were withheld.
- Employee separates before business 2nd cutoff (11-25).
- Business 2nd cutoff has no attendance and no gross pay.
- Monthly SSS liability is still determined from the full monthly compensation cycle.

New behavior:

1. The employee is included in the 11-25 payroll as a settlement-only row.
2. The exact monthly SSS/MPF liability is shown.
3. Default Auto Cap prevents employee government deductions by themselves from pushing net pay below zero.
4. HR may select Employer Advance when the company will remit/absorb the currently unrecovered employee share.
5. If HR/accounting specifically approves return of a prior-cutoff employee deduction, enter a reimbursement amount and reason. The benefit ledger continues to show the exact statutory liability separately from payroll cash collected/refunded.

Do not automatically refund PhilHealth or Pag-IBIG merely because SSS cannot be collected from the closing payroll. Each program is tracked separately.

## New permissions

- `payroll-benefit-settlements.manage`
- `payroll-audit-logs.view`

The seeder creates the permissions. Assign them to the appropriate HR/payroll/admin roles through your role-management screen or permission sync workflow.

## Install / update commands

Run from the Laravel project root:

```bash
php artisan migrate
php artisan db:seed --class=PermissionSeeder
php artisan permission:cache-reset
php artisan optimize:clear
```

If your production deployment caches routes/config/views after verification:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Recommended verification scenarios

### A. Normal active employee

- Opening 26-10 finalized.
- Closing 11-25 has normal attendance and positive gross.
- Generate closing payroll.
- Confirm exact monthly SSS/MPF true-up, expected PhilHealth/Pag-IBIG, positive net, and Benefits Record after finalization.

### B. Resigned/no-pay closing cutoff

- Employee exists in finalized 26-10 payroll.
- Mark employee payroll inactive/resigned before 11-25.
- Generate 11-25 payroll.
- Confirm a settlement-only payroll item exists.
- Confirm default Auto Cap keeps net >= 0.
- Open employee payroll detail and select Employer Advance if that is the approved accounting treatment.
- Finalize and confirm Benefits Records show exact liability plus unrecovered/advanced employee share status.

### C. Approved reimbursement

- Use a resigned settlement-only employee.
- Enter an approved reimbursement and a reason.
- Confirm the form enforces the displayed maximum manual credit.
- Confirm the payroll item becomes a credit as intended without changing statutory employer liabilities.
- Confirm audit logs show the settlement record and payroll-item recalculation.

### D. 3-day rest-day audit

- Employee with 1-2 valid logged work dates and no leave/adjustment: flag should be Not Eligible.
- Employee with >=3 valid logged dates: flag should be Eligible.
- Employee with <3 valid logged dates but approved leave/adjustment: flag should be Eligible by override.
- Confirm actual rest-day worked premium remains payable when valid work exists.

### E. Benefits register

- Confirm SSS Premium EE/ER/EC/Total, MPF EE/ER/Total, Total SSS/MPF, PhilHealth, and Pag-IBIG columns match the contribution record data.
- Confirm historical resigned employees with a posted record remain visible for the selected month.

### F. Audit access

- Grant `payroll-audit-logs.view` to an authorized role.
- Update a rate, schedule, adjustment, benefit settlement, or payroll.
- Confirm the audit page shows actor, module/action, before/after values, request ID, and timestamp.
- Confirm payroll group restrictions are respected.

## Validation performed in the patch workspace

- PHP syntax lint passed for all 22 changed/new PHP files.
- Git whitespace/diff validation passed for tracked modified files.
- Full Artisan route boot could not be executed in the patch sandbox because the sandbox PHP build does not have the `mbstring` extension (`mb_split()` is unavailable). This is an environment limitation, not a PHP syntax error. Laravel production PHP should have `mbstring` enabled before deployment.

## Database changes

Migrations:

- `2026_08_09_180000_create_payroll_benefit_settlements_table.php`
- `2026_08_09_180100_add_collection_tracking_to_benefit_contribution_records_table.php`
- `2026_08_09_180200_create_payroll_audit_logs_table.php`

The Benefits Records tracking migration backfills historical finalized rows so old records continue to represent fully collected employee shares instead of appearing as zero-collected.

## Rollback

Normal Laravel rollback can remove the three new migrations if they are the latest batch:

```bash
php artisan migrate:rollback --step=3
php artisan optimize:clear
```

Back up the production database before payroll-schema deployment. Audit and settlement records are financial-control data and should be included in normal database backup/retention policy.
