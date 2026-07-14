<?php

namespace App\Services\Maintenance;

use App\Enums\JobOrderStatus;
use App\Models\Bus;
use App\Models\JobOrderMaintenance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class JobOrderMaintenanceService
{
    public function create(array $data, ?int $userId): JobOrderMaintenance
    {
        return DB::transaction(function () use ($data, $userId): JobOrderMaintenance {
            $bus = Bus::query()
                ->lockForUpdate()
                ->findOrFail($data['bus_id']);

            $lastOdometerReading = JobOrderMaintenance::query()
                ->where('bus_id', $bus->id)
                ->whereNotNull('odometer_reading')
                ->latest('id')
                ->value('odometer_reading');

            $jobOrder = JobOrderMaintenance::query()->create([
                'job_order_no' => filled($data['job_order_no'] ?? null)
                    ? trim($data['job_order_no'])
                    : $this->generateJobOrderNumber(),
                'bus_id' => $bus->id,
                'bus_no_snapshot' => $bus->bus_no,
                'plate_no_snapshot' => $bus->plate_no,
                'company_snapshot' => $bus->company,
                'garage_snapshot' => $bus->garage,
                'full_name' => filled($data['full_name'] ?? null)
                    ? trim($data['full_name'])
                    : null,
                'mechanic_names' => $this->normalizeMechanicNames($data['mechanic_names'] ?? []),
                'repair_types' => $this->normalizeRepairTypes($data['repair_types'] ?? []),
                'description_of_work' => trim($data['description_of_work']),
                'odometer_reading' => $data['odometer_reading'] ?? null,
                'last_odometer_reading' => $lastOdometerReading,
                'status' => JobOrderStatus::Standby,
                'created_by' => $userId,
            ]);

            $jobOrder->statusPeriods()->create([
                'status' => JobOrderStatus::Standby,
                'started_at' => now(),
                'changed_by' => $userId,
            ]);

            $jobOrder->histories()->create([
                'action' => 'Job order created',
                'old_value' => null,
                'new_value' => JobOrderStatus::Standby->label(),
                'remarks' => 'Initial maintenance status.',
                'user_id' => $userId,
            ]);

            return $jobOrder->fresh([
                'bus',
                'creator',
                'statusPeriods',
            ]);
        }, 3);
    }

    public function updateStatus(
        JobOrderMaintenance $jobOrderMaintenance,
        JobOrderStatus $status,
        ?int $userId,
        ?string $remarks = null,
        array $mechanicNames = [],
        array $repairTypes = []
    ): JobOrderMaintenance {
        return DB::transaction(function () use (
            $jobOrderMaintenance,
            $status,
            $userId,
            $remarks,
            $mechanicNames,
            $repairTypes
        ): JobOrderMaintenance {
            $jobOrder = JobOrderMaintenance::query()
                ->lockForUpdate()
                ->findOrFail($jobOrderMaintenance->getKey());

            $oldStatus = $jobOrder->status;
            $normalizedMechanics = $this->normalizeMechanicNames($mechanicNames);
            $normalizedRepairTypes = $this->normalizeRepairTypes($repairTypes);

            if ($status === JobOrderStatus::Operational) {
                if ($normalizedMechanics === []) {
                    throw new RuntimeException('At least one mechanic is required before completion.');
                }

                if ($normalizedRepairTypes === []) {
                    throw new RuntimeException('At least one repair type is required before completion.');
                }
            }

            $detailsChanged = $jobOrder->mechanic_names_list !== $normalizedMechanics
                || collect($jobOrder->repair_types ?? [])->sort()->values()->all()
                    !== collect($normalizedRepairTypes)->sort()->values()->all();

            if ($oldStatus !== $status) {
                $changedAt = now();

                $jobOrder->statusPeriods()
                    ->whereNull('ended_at')
                    ->lockForUpdate()
                    ->update([
                        'ended_at' => $changedAt,
                        'updated_at' => $changedAt,
                    ]);

                if ($status->countsAsDowntime()) {
                    $jobOrder->statusPeriods()->create([
                        'status' => $status,
                        'started_at' => $changedAt,
                        'changed_by' => $userId,
                    ]);
                }

                $jobOrder->forceFill([
                    'status' => $status,
                    'mechanic_names' => $normalizedMechanics,
                    'repair_types' => $normalizedRepairTypes,
                ])->save();

                $jobOrder->histories()->create([
                    'action' => 'Maintenance status updated',
                    'old_value' => $oldStatus?->label(),
                    'new_value' => $status->label(),
                    'remarks' => $this->buildStatusHistoryRemarks(
                        remarks: $remarks,
                        mechanicNames: $normalizedMechanics,
                        repairTypes: $normalizedRepairTypes
                    ),
                    'user_id' => $userId,
                ]);
            } elseif ($detailsChanged || filled($remarks)) {
                $jobOrder->forceFill([
                    'mechanic_names' => $normalizedMechanics,
                    'repair_types' => $normalizedRepairTypes,
                ])->save();

                $jobOrder->histories()->create([
                    'action' => 'Repair details updated',
                    'old_value' => null,
                    'new_value' => $status->label(),
                    'remarks' => $this->buildStatusHistoryRemarks(
                        remarks: $remarks,
                        mechanicNames: $normalizedMechanics,
                        repairTypes: $normalizedRepairTypes
                    ),
                    'user_id' => $userId,
                ]);
            }

            return $jobOrder->fresh([
                'bus',
                'creator',
                'histories.user',
                'statusPeriods.changedBy',
            ]);
        }, 3);
    }

    public function updateJobOrderNumber(
        JobOrderMaintenance $jobOrderMaintenance,
        string $jobOrderNo,
        ?int $userId,
        ?string $remarks = null
    ): JobOrderMaintenance {
        return DB::transaction(function () use (
            $jobOrderMaintenance,
            $jobOrderNo,
            $userId,
            $remarks
        ): JobOrderMaintenance {
            $jobOrder = JobOrderMaintenance::query()
                ->lockForUpdate()
                ->findOrFail($jobOrderMaintenance->getKey());

            $oldJobOrderNo = $jobOrder->job_order_no;
            $newJobOrderNo = trim($jobOrderNo);

            if ($oldJobOrderNo === $newJobOrderNo && ! filled($remarks)) {
                return $jobOrder;
            }

            $jobOrder->update([
                'job_order_no' => $newJobOrderNo,
            ]);

            $jobOrder->histories()->create([
                'action' => 'Job order number updated',
                'old_value' => $oldJobOrderNo,
                'new_value' => $newJobOrderNo,
                'remarks' => $remarks,
                'user_id' => $userId,
            ]);

            return $jobOrder->fresh();
        }, 3);
    }

    private function generateJobOrderNumber(): string
    {
        $prefix = 'JO-'.now()->format('Y').'-';

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $latestNumber = JobOrderMaintenance::withTrashed()
                ->where('job_order_no', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('job_order_no');

            $nextSequence = 1;

            if (is_string($latestNumber) && preg_match('/(\d+)$/', $latestNumber, $matches)) {
                $nextSequence = ((int) $matches[1]) + 1;
            }

            $candidate = $prefix.str_pad((string) ($nextSequence + $attempt), 5, '0', STR_PAD_LEFT);

            if (! JobOrderMaintenance::withTrashed()->where('job_order_no', $candidate)->exists()) {
                return $candidate;
            }
        }

        return $prefix.now()->format('mdHis').'-'.Str::upper(Str::random(4));
    }

    private function normalizeMechanicNames(array $mechanicNames): array
    {
        return collect($mechanicNames)
            ->map(fn ($name): string => trim((string) $name))
            ->filter()
            ->unique(fn (string $name): string => mb_strtolower($name))
            ->values()
            ->all();
    }

    private function normalizeRepairTypes(array $repairTypes): array
    {
        return collect($repairTypes)
            ->map(fn ($type): string => trim((string) $type))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function buildStatusHistoryRemarks(
        ?string $remarks,
        array $mechanicNames,
        array $repairTypes
    ): ?string {
        $lines = [];

        if (filled($remarks)) {
            $lines[] = trim($remarks);
        }

        if ($mechanicNames !== []) {
            $lines[] = 'Mechanic(s): '.implode(', ', $mechanicNames);
        }

        if ($repairTypes !== []) {
            $labels = collect($repairTypes)
                ->map(fn (string $type): string => str($type)->replace('_', ' ')->title()->toString())
                ->implode(', ');

            $lines[] = 'Repair type(s): '.$labels;
        }

        return $lines === [] ? null : implode(PHP_EOL, $lines);
    }
}
