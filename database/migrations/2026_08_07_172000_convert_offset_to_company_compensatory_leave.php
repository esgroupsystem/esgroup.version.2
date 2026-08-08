<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payroll_attendance_adjustments')) {
            return;
        }

        /*
         * v3.5 used a custom "pay next cutoff" Offset policy. v3.6 changes
         * Offset to company compensatory-leave credit: verified excess work from an
         * earlier source date is used as a company attendance credit on the
         * target date. It is never a separate cash payroll addition and does
         * not cancel any separately payable statutory overtime.
         *
         * Historical Offset records linked to FINALIZED payrolls are preserved. Offset records linked only to draft payrolls are released and reset for review.
         * Unconsumed records are reset for review because their old approved
         * minutes represented a different business rule.
         */
        $finalizedPayrollIds = Schema::hasTable('payrolls')
            ? DB::table('payrolls')
                ->where('status', 'finalized')
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all()
            : [];

        $query = DB::table('payroll_attendance_adjustments')
            ->where('adjustment_type', 'offset')
            ->where(function ($query) use ($finalizedPayrollIds): void {
                $query->whereNull('paid_payroll_id');

                if ($finalizedPayrollIds !== []) {
                    $query->orWhereNotIn('paid_payroll_id', $finalizedPayrollIds);
                } else {
                    $query->orWhereNotNull('paid_payroll_id');
                }
            });

        $query->update([
            'defer_to_next_payroll' => false,
            'payroll_effective_date' => null,
            'paid_payroll_id' => null,
            'paid_payroll_item_id' => null,
            'approved_minutes' => null,
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'updated_at' => now('Asia/Manila'),
        ]);
    }

    public function down(): void
    {
        // Intentionally no automatic rollback to the old deferred-cash policy.
        // Reintroducing that workflow requires a deliberate business decision.
    }
};
