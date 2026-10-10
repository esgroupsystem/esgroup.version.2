<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Payroll;

/**
 * Bulk removal of payroll test data (`payroll:reset-data`). Query builder only, so no
 * model events run and the wipe does not write thousands of audit rows.
 */
interface PayrollDataResetRepositoryInterface
{
    /**
     * @param  list<string>  $scopes  'payroll' | 'adjustments' | 'summaries'
     * @return array<string, int> table (or table:part) => rows that would be removed / released
     */
    public function counts(array $scopes): array;

    /** @return list<string> stored attachment paths of every attendance adjustment (incl. soft-deleted) */
    public function adjustmentAttachmentPaths(): array;

    /** Payroll runs and everything saved from them. Releases adjustments linked to a payroll. */
    public function deletePayrollRuns(): void;

    /** Every attendance adjustment (incl. soft-deleted) and its log rows. */
    public function deleteAdjustments(): void;

    /** Every daily attendance summary (rebuildable from the biometric logs). */
    public function deleteSummaries(): void;
}
