# Payroll delete audit foreign-key fix

Updated 2026-08-12.

## Problem

Deleting a draft payroll removed the `payrolls` row successfully, then the Eloquent `deleted` observer attempted to insert a new `payroll_audit_logs` row with the same deleted ID in the `payroll_id` foreign-key column.

MySQL rejected that insert with error 1452 because `payroll_audit_logs.payroll_id` references `payrolls.id`.

## Fix

`PayrollAuditService::recordModelChange()` is now deletion-aware:

- Deleting a `Payroll` stores `payroll_id = NULL` in the new audit row.
- Deleting a `PayrollItem` stores `payroll_item_id = NULL` in the new audit row.
- Deleting an `EmployeeBiometric` stores `employee_biometric_id = NULL` in the new audit row.
- The deleted record remains traceable through `auditable_type`, `auditable_id`, `description`, and `old_values`.
- Related IDs that still point to existing parent rows continue to be stored normally.

This preserves audit history without creating an invalid foreign-key reference.

## Database migration

No migration is required. The existing `NULL`-permitted foreign-key columns and `ON DELETE SET NULL` definitions are already correct.

## Deployment

After replacing the project files, run:

```bash
php artisan optimize:clear
```

Then create and delete a draft payroll. Expected result:

1. Draft payroll is deleted.
2. Browser returns to `/payroll`.
3. No SQLSTATE 23000 / error 1452 is logged.
4. Payroll Transaction Logs contains a `Deleted` payroll audit record.
5. That audit record has a null live `payroll_id`, while its deleted payroll identity remains in `auditable_id` and `old_values`.
