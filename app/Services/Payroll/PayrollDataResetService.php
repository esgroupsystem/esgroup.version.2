<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Repositories\Contracts\Payroll\PayrollDataResetRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Wipes payroll TEST data so payroll can start clean (`php artisan payroll:reset-data`).
 *
 * Never touched: employees, biometrics and punches, work schedules, Employee Rates (salaries,
 * loans, other deductions), holidays, Payroll Settings versions and rules, users and roles.
 * The table structure and foreign keys stay as they are; only rows are deleted.
 */
final class PayrollDataResetService
{
    public const SCOPES = ['payroll', 'adjustments', 'summaries'];

    private const ATTACHMENT_DISK = 'local';

    public function __construct(
        private readonly PayrollDataResetRepositoryInterface $reset,
    ) {}

    /**
     * @param  list<string>  $scopes
     * @return array<string, int>
     */
    public function preview(array $scopes): array
    {
        return $this->reset->counts($this->normalize($scopes));
    }

    /**
     * Deletes everything in one transaction (all or nothing). Uploaded OT forms are removed
     * from disk only after the rows are gone.
     *
     * @param  list<string>  $scopes
     * @return array<string, int> what was removed
     */
    public function run(array $scopes): array
    {
        $scopes = $this->normalize($scopes);
        $counts = $this->reset->counts($scopes);
        $files = in_array('adjustments', $scopes, true) ? $this->reset->adjustmentAttachmentPaths() : [];

        DB::transaction(function () use ($scopes): void {
            if (in_array('payroll', $scopes, true)) {
                $this->reset->deletePayrollRuns();
            }

            if (in_array('adjustments', $scopes, true)) {
                $this->reset->deleteAdjustments();
            }

            if (in_array('summaries', $scopes, true)) {
                $this->reset->deleteSummaries();
            }
        });

        $removed = 0;
        foreach ($files as $path) {
            if (Storage::disk(self::ATTACHMENT_DISK)->exists($path) && Storage::disk(self::ATTACHMENT_DISK)->delete($path)) {
                $removed++;
            }
        }

        if ($files !== []) {
            $counts['OT form files removed'] = $removed;
        }

        return $counts;
    }

    /**
     * Payroll runs are always included: adjustments and summaries are what payroll is built from.
     *
     * @param  list<string>  $scopes
     * @return list<string>
     */
    private function normalize(array $scopes): array
    {
        return array_values(array_intersect(self::SCOPES, array_merge(['payroll'], $scopes)));
    }
}
