<?php

declare(strict_types=1);

namespace App\Repositories\Payroll;

use App\Repositories\Contracts\Payroll\PayrollDataResetRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PayrollDataResetRepository implements PayrollDataResetRepositoryInterface
{
    /** Transaction Logs modules written by payroll runs / benefits (see PayrollAuditService::moduleFor). */
    private const PAYROLL_LOG_MODULES = ['payroll', 'benefits'];

    private const ADJUSTMENT_LOG_MODULES = ['adjustment'];

    public function counts(array $scopes): array
    {
        $counts = [];

        if (in_array('payroll', $scopes, true)) {
            $counts += [
                'payrolls' => DB::table('payrolls')->count(),
                'payroll_items' => DB::table('payroll_items')->count(),
                'benefit_contribution_records' => DB::table('benefit_contribution_records')->count(),
                'payroll_benefit_settlements' => DB::table('payroll_benefit_settlements')->count(),
                'payment_logs' => DB::table('payment_logs')->count(),
                'payroll_report_logs' => $this->countIfExists('payroll_report_logs'),
                'payroll_audit_logs (payroll / benefits)' => $this->payrollLogs()->count(),
                'payroll_attendance_adjustments released (kept)' => DB::table('payroll_attendance_adjustments')
                    ->where(fn ($query) => $query->whereNotNull('paid_payroll_id')->orWhereNotNull('paid_payroll_item_id'))
                    ->count(),
            ];
        }

        if (in_array('adjustments', $scopes, true)) {
            $counts += [
                'payroll_attendance_adjustments' => DB::table('payroll_attendance_adjustments')->count(),
                'payroll_audit_logs (adjustment)' => DB::table('payroll_audit_logs')->whereIn('module', self::ADJUSTMENT_LOG_MODULES)->count(),
            ];
        }

        if (in_array('summaries', $scopes, true)) {
            $counts['daily_attendance_summaries'] = DB::table('daily_attendance_summaries')->count();
        }

        return $counts;
    }

    public function adjustmentAttachmentPaths(): array
    {
        return DB::table('payroll_attendance_adjustments')
            ->whereNotNull('attachment_path')
            ->where('attachment_path', '!=', '')
            ->pluck('attachment_path')
            ->map(fn ($path): string => (string) $path)
            ->values()
            ->all();
    }

    public function deletePayrollRuns(): void
    {
        // Children first: benefit_contribution_records RESTRICTS deleting payrolls/items,
        // which is why a finalized payroll could never be removed from the app.
        DB::table('benefit_contribution_records')->delete();
        DB::table('payroll_benefit_settlements')->delete();
        // Loan balances are "principal − sum(payment_logs)", so removing the logs restores them.
        DB::table('payment_logs')->delete();

        if (Schema::hasTable('payroll_report_logs')) {
            DB::table('payroll_report_logs')->delete();
        }

        $this->payrollLogs()->delete();

        // Adjustments consumed by a payroll become free again (they are kept).
        DB::table('payroll_attendance_adjustments')
            ->where(fn ($query) => $query->whereNotNull('paid_payroll_id')->orWhereNotNull('paid_payroll_item_id'))
            ->update(['paid_payroll_id' => null, 'paid_payroll_item_id' => null]);

        DB::table('payroll_items')->delete();
        DB::table('payrolls')->delete(); // includes soft-deleted rows
    }

    public function deleteAdjustments(): void
    {
        DB::table('payroll_audit_logs')->whereIn('module', self::ADJUSTMENT_LOG_MODULES)->delete();
        DB::table('payroll_attendance_adjustments')->delete(); // includes soft-deleted rows
    }

    public function deleteSummaries(): void
    {
        DB::table('daily_attendance_summaries')->delete();
    }

    private function payrollLogs(): \Illuminate\Database\Query\Builder
    {
        return DB::table('payroll_audit_logs')->where(fn ($query) => $query
            ->whereIn('module', self::PAYROLL_LOG_MODULES)
            ->orWhereNotNull('payroll_id')
            ->orWhereNotNull('payroll_item_id'));
    }

    private function countIfExists(string $table): int
    {
        return Schema::hasTable($table) ? DB::table($table)->count() : 0;
    }
}
