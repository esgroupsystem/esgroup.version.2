<?php

namespace App\Services\Maintenance;

use App\Enums\JobOrderStatus;
use App\Models\Bus;
use App\Models\JobOrderMaintenance;
use App\Models\JobOrderMaintenanceHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class JobOrderMaintenanceService
{
    /**
     * @throws Throwable
     */
    public function create(array $data, ?int $userId): JobOrderMaintenance
    {
        return DB::transaction(function () use ($data, $userId) {
            $bus = Bus::query()
                ->lockForUpdate()
                ->findOrFail($data['bus_id']);

            $lastOdometer = $this->getLastOdometerReading($bus->id);

            $currentOdometer = isset($data['odometer_reading']) && $data['odometer_reading'] !== null
                ? (int) $data['odometer_reading']
                : null;

            $odometerDifference = null;
            $isLowerThanLast = false;

            if ($currentOdometer !== null && $lastOdometer !== null) {
                $odometerDifference = $currentOdometer - $lastOdometer;
                $isLowerThanLast = $currentOdometer < $lastOdometer;
            }

            $jobOrderNo = $this->resolveJobOrderNumber($data['job_order_no'] ?? null);

            $jobOrderMaintenance = JobOrderMaintenance::query()->create([
                'job_order_no' => $jobOrderNo,

                'bus_id' => $bus->id,
                'bus_no_snapshot' => $bus->bus_no,
                'plate_no_snapshot' => $bus->plate_no,
                'company_snapshot' => $bus->company,
                'garage_snapshot' => $bus->garage,

                'full_name' => $data['full_name'] ?? null,
                'description_of_work' => $data['description_of_work'],

                'odometer_reading' => $currentOdometer,
                'last_odometer_reading' => $lastOdometer,
                'odometer_difference' => $odometerDifference,
                'is_odometer_lower_than_last' => $isLowerThanLast,

                'status' => JobOrderStatus::Standby,
                'created_by' => $userId,
            ]);

            JobOrderMaintenanceHistory::query()->create([
                'job_order_maintenance_id' => $jobOrderMaintenance->id,
                'user_id' => $userId,
                'action' => 'Created',
                'remarks' => 'Maintenance job order created.',
                'old_value' => null,
                'new_value' => $jobOrderMaintenance->job_order_no,
            ]);

            Log::info('Maintenance job order created', [
                'job_order_maintenance_id' => $jobOrderMaintenance->id,
                'job_order_no' => $jobOrderMaintenance->job_order_no,
                'bus_id' => $bus->id,
                'bus_no' => $bus->bus_no,
                'created_by' => $userId,
            ]);

            return $jobOrderMaintenance;
        });
    }

    /**
     * @throws Throwable
     */
    public function updateStatus(
        JobOrderMaintenance $jobOrderMaintenance,
        JobOrderStatus $status,
        ?int $userId,
        ?string $remarks = null
    ): JobOrderMaintenance {
        return DB::transaction(function () use ($jobOrderMaintenance, $status, $userId, $remarks) {
            $oldStatus = $jobOrderMaintenance->status?->value;
            $newStatus = $status->value;

            $jobOrderMaintenance->update([
                'status' => $status,
            ]);

            JobOrderMaintenanceHistory::query()->create([
                'job_order_maintenance_id' => $jobOrderMaintenance->id,
                'user_id' => $userId,
                'action' => 'Status Updated',
                'remarks' => $this->cleanRemarks($remarks),
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
            ]);

            Log::info('Maintenance job order status updated', [
                'job_order_maintenance_id' => $jobOrderMaintenance->id,
                'job_order_no' => $jobOrderMaintenance->job_order_no,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'updated_by' => $userId,
            ]);

            return $jobOrderMaintenance->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    public function updateJobOrderNumber(
        JobOrderMaintenance $jobOrderMaintenance,
        string $jobOrderNo,
        ?int $userId,
        ?string $remarks = null
    ): JobOrderMaintenance {
        return DB::transaction(function () use ($jobOrderMaintenance, $jobOrderNo, $userId, $remarks) {
            $oldJobOrderNo = $jobOrderMaintenance->job_order_no;
            $newJobOrderNo = strtoupper(trim($jobOrderNo));

            $jobOrderMaintenance->update([
                'job_order_no' => $newJobOrderNo,
            ]);

            JobOrderMaintenanceHistory::query()->create([
                'job_order_maintenance_id' => $jobOrderMaintenance->id,
                'user_id' => $userId,
                'action' => 'JO-NO Updated',
                'remarks' => $this->cleanRemarks($remarks),
                'old_value' => $oldJobOrderNo,
                'new_value' => $newJobOrderNo,
            ]);

            Log::info('Maintenance job order number updated', [
                'job_order_maintenance_id' => $jobOrderMaintenance->id,
                'old_job_order_no' => $oldJobOrderNo,
                'new_job_order_no' => $newJobOrderNo,
                'updated_by' => $userId,
            ]);

            return $jobOrderMaintenance->refresh();
        });
    }

    private function resolveJobOrderNumber(?string $customJobOrderNo): string
    {
        $customJobOrderNo = strtoupper(trim((string) $customJobOrderNo));

        if ($customJobOrderNo !== '') {
            return $customJobOrderNo;
        }

        return $this->generateJobOrderNumber();
    }

    private function getLastOdometerReading(int $busId): ?int
    {
        return JobOrderMaintenance::query()
            ->where('bus_id', $busId)
            ->whereNotNull('odometer_reading')
            ->latest('created_at')
            ->latest('id')
            ->value('odometer_reading');
    }

    private function generateJobOrderNumber(): string
    {
        $date = now()->format('Ymd');

        $countToday = JobOrderMaintenance::query()
            ->whereDate('created_at', now()->toDateString())
            ->count();

        do {
            $countToday++;

            $jobOrderNo = 'JO-MNT-'.$date.'-'.str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);
        } while (
            JobOrderMaintenance::query()
                ->where('job_order_no', $jobOrderNo)
                ->exists()
        );

        return $jobOrderNo;
    }

    private function cleanRemarks(?string $remarks): ?string
    {
        $remarks = trim((string) $remarks);

        return $remarks !== '' ? $remarks : null;
    }
}
