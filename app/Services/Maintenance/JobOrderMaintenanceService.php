<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Enums\JobOrderStatus;
use App\Models\Bus;
use App\Models\JobOrderMaintenance;
use App\Repositories\Contracts\Fleet\BusRepositoryInterface;
use App\Repositories\Contracts\Maintenance\JobOrderMaintenanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Maintenance Job Orders: a bus repair from Standby → Waiting Parts / On Going Repair → Operational.
 * Every status change closes the open status period (downtime) and opens a new one while the status
 * counts as downtime; every change writes a history row.
 */
final class JobOrderMaintenanceService
{
    public const SHOW_RELATIONS = ['bus', 'creator', 'histories.user', 'statusPeriods.changedBy'];

    public function __construct(
        private readonly JobOrderMaintenanceRepositoryInterface $jobOrders,
        private readonly BusRepositoryInterface $buses,
    ) {}

    /** @return array{search: string, status: string, bus_id: ?int, date_filter: string, filter_date: string, filter_month: string, filter_year: string} */
    public static function filters(Request $request): array
    {
        return [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'bus_id' => $request->integer('bus_id') ?: null,
            'date_filter' => $request->string('date_filter')->toString(),
            'filter_date' => $request->string('filter_date')->toString(),
            'filter_month' => $request->string('filter_month')->toString(),
            'filter_year' => $request->string('filter_year')->toString(),
        ];
    }

    /**
     * @param  array{search: string, status: string, bus_id: ?int, date_filter: string, filter_date: string, filter_month: string, filter_year: string}  $filters
     * @return LengthAwarePaginator<int, JobOrderMaintenance>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->jobOrders->paginate($filters);
    }

    /**
     * One card per status with its count under the other filters.
     *
     * @param  array{search: string, status: string, bus_id: ?int, date_filter: string, filter_date: string, filter_month: string, filter_year: string}  $filters
     * @return list<array{value: string, label: string, description: string, count: int}>
     */
    public function statusCards(array $filters): array
    {
        $counts = $this->jobOrders->countByStatus($filters);

        return collect(JobOrderStatus::cases())->map(fn (JobOrderStatus $status): array => [
            'value' => $status->value,
            'label' => $status->label(),
            'description' => $status->description(),
            'count' => (int) ($counts[$status->value] ?? 0),
        ])->values()->all();
    }

    /** @return Collection<int, Bus> */
    public function busOptions(): Collection
    {
        return $this->buses->options();
    }

    /** @return Collection<int, Bus> with operational status and last odometer, for the create form */
    public function busOptionsWithLastOdometer(): Collection
    {
        return $this->buses->optionsWithLastOdometer();
    }

    /** @param list<string> $relations */
    public function load(JobOrderMaintenance $jobOrder, array $relations = self::SHOW_RELATIONS): JobOrderMaintenance
    {
        return $this->jobOrders->load($jobOrder, $relations);
    }

    /** @param array<string, mixed> $data validated StoreJobOrderMaintenanceRequest */
    public function create(array $data, ?int $userId): JobOrderMaintenance
    {
        return DB::transaction(function () use ($data, $userId): JobOrderMaintenance {
            $bus = $this->buses->findForUpdate((int) $data['bus_id']);

            $jobOrder = $this->jobOrders->create([
                'job_order_no' => filled($data['job_order_no'] ?? null) ? trim($data['job_order_no']) : $this->generateJobOrderNumber(),
                'bus_id' => $bus->id,
                'bus_no_snapshot' => $bus->bus_no,
                'plate_no_snapshot' => $bus->plate_no,
                'company_snapshot' => $bus->company,
                'garage_snapshot' => $bus->garage,
                'full_name' => filled($data['full_name'] ?? null) ? trim($data['full_name']) : null,
                'mechanic_names' => $this->normalizeMechanicNames($data['mechanic_names'] ?? []),
                'repair_types' => $this->normalizeRepairTypes($data['repair_types'] ?? []),
                'description_of_work' => trim($data['description_of_work']),
                'odometer_reading' => $data['odometer_reading'] ?? null,
                'last_odometer_reading' => $this->jobOrders->lastOdometerReading($bus->id),
                'status' => JobOrderStatus::Standby,
                'created_by' => $userId,
            ]);

            $this->jobOrders->startPeriod($jobOrder, [
                'status' => JobOrderStatus::Standby,
                'started_at' => now(),
                'changed_by' => $userId,
            ]);
            $this->jobOrders->addHistory($jobOrder, [
                'action' => 'Job order created',
                'old_value' => null,
                'new_value' => JobOrderStatus::Standby->label(),
                'remarks' => 'Initial maintenance status.',
                'user_id' => $userId,
            ]);

            return $jobOrder->fresh(['bus', 'creator', 'statusPeriods']);
        }, 3);
    }

    /**
     * @param  list<string>  $mechanicNames
     * @param  list<string>  $repairTypes
     *
     * @throws ValidationException when completing (Operational) without a mechanic or repair type
     */
    public function updateStatus(
        JobOrderMaintenance $jobOrderMaintenance,
        JobOrderStatus $status,
        ?int $userId,
        ?string $remarks = null,
        array $mechanicNames = [],
        array $repairTypes = []
    ): JobOrderMaintenance {
        return DB::transaction(function () use ($jobOrderMaintenance, $status, $userId, $remarks, $mechanicNames, $repairTypes): JobOrderMaintenance {
            $jobOrder = $this->jobOrders->findForUpdate((int) $jobOrderMaintenance->getKey());
            $oldStatus = $jobOrder->status;
            $mechanics = $this->normalizeMechanicNames($mechanicNames);
            $types = $this->normalizeRepairTypes($repairTypes);

            if ($status === JobOrderStatus::Operational && $mechanics === []) {
                throw ValidationException::withMessages(['mechanic_names' => 'At least one mechanic is required before completion.']);
            }
            if ($status === JobOrderStatus::Operational && $types === []) {
                throw ValidationException::withMessages(['repair_types' => 'At least one repair type is required before completion.']);
            }

            $detailsChanged = $jobOrder->mechanic_names_list !== $mechanics
                || collect($jobOrder->repair_types ?? [])->sort()->values()->all() !== collect($types)->sort()->values()->all();
            $historyRemarks = $this->statusHistoryRemarks($remarks, $mechanics, $types);

            if ($oldStatus !== $status) {
                $changedAt = now();
                $this->jobOrders->endOpenPeriods($jobOrder, $changedAt);

                if ($status->countsAsDowntime()) {
                    $this->jobOrders->startPeriod($jobOrder, ['status' => $status, 'started_at' => $changedAt, 'changed_by' => $userId]);
                }

                $this->jobOrders->update($jobOrder, ['status' => $status, 'mechanic_names' => $mechanics, 'repair_types' => $types]);
                $this->jobOrders->addHistory($jobOrder, [
                    'action' => 'Maintenance status updated',
                    'old_value' => $oldStatus->label(),
                    'new_value' => $status->label(),
                    'remarks' => $historyRemarks,
                    'user_id' => $userId,
                ]);
            } elseif ($detailsChanged || filled($remarks)) {
                $this->jobOrders->update($jobOrder, ['mechanic_names' => $mechanics, 'repair_types' => $types]);
                $this->jobOrders->addHistory($jobOrder, [
                    'action' => 'Repair details updated',
                    'old_value' => null,
                    'new_value' => $status->label(),
                    'remarks' => $historyRemarks,
                    'user_id' => $userId,
                ]);
            }

            return $jobOrder->fresh(self::SHOW_RELATIONS);
        }, 3);
    }

    /** Ends any open downtime period, then soft-deletes the job order. */
    public function delete(JobOrderMaintenance $jobOrderMaintenance, ?int $userId): void
    {
        DB::transaction(function () use ($jobOrderMaintenance, $userId): void {
            $jobOrder = $this->jobOrders->findForUpdate((int) $jobOrderMaintenance->getKey());

            $this->jobOrders->endOpenPeriods($jobOrder, now());
            $this->jobOrders->addHistory($jobOrder, [
                'action' => 'Job order deleted',
                'old_value' => $jobOrder->status->label(),
                'new_value' => null,
                'remarks' => null,
                'user_id' => $userId,
            ]);
            $this->jobOrders->delete($jobOrder);
        }, 3);
    }

    public function updateJobOrderNumber(JobOrderMaintenance $jobOrderMaintenance, string $jobOrderNo, ?int $userId, ?string $remarks = null): JobOrderMaintenance
    {
        return DB::transaction(function () use ($jobOrderMaintenance, $jobOrderNo, $userId, $remarks): JobOrderMaintenance {
            $jobOrder = $this->jobOrders->findForUpdate((int) $jobOrderMaintenance->getKey());
            $oldNumber = $jobOrder->job_order_no;
            $newNumber = trim($jobOrderNo);

            if ($oldNumber === $newNumber && ! filled($remarks)) {
                return $jobOrder;
            }

            $this->jobOrders->update($jobOrder, ['job_order_no' => $newNumber]);
            $this->jobOrders->addHistory($jobOrder, [
                'action' => 'Job order number updated',
                'old_value' => $oldNumber,
                'new_value' => $newNumber,
                'remarks' => $remarks,
                'user_id' => $userId,
            ]);

            return $jobOrder->fresh();
        }, 3);
    }

    /** Next free `JO-<year>-00001` number, counting deleted job orders too. */
    private function generateJobOrderNumber(): string
    {
        $prefix = 'JO-'.now()->format('Y').'-';

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $latest = $this->jobOrders->latestNumberLike($prefix);
            $next = $latest !== null && preg_match('/(\d+)$/', $latest, $matches) ? ((int) $matches[1]) + 1 : 1;
            $candidate = $prefix.str_pad((string) ($next + $attempt), 5, '0', STR_PAD_LEFT);

            if (! $this->jobOrders->numberExists($candidate)) {
                return $candidate;
            }
        }

        return $prefix.now()->format('mdHis').'-'.Str::upper(Str::random(4));
    }

    /**
     * @param  array<int, mixed>  $mechanicNames
     * @return list<string> trimmed, blanks dropped, duplicates (any case) dropped
     */
    private function normalizeMechanicNames(array $mechanicNames): array
    {
        return collect($mechanicNames)
            ->map(fn (mixed $name): string => trim((string) $name))
            ->filter()
            ->unique(fn (string $name): string => mb_strtolower($name))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $repairTypes
     * @return list<string>
     */
    private function normalizeRepairTypes(array $repairTypes): array
    {
        return collect($repairTypes)
            ->map(fn (mixed $type): string => trim((string) $type))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $mechanicNames
     * @param  list<string>  $repairTypes
     */
    private function statusHistoryRemarks(?string $remarks, array $mechanicNames, array $repairTypes): ?string
    {
        $lines = [];

        if (filled($remarks)) {
            $lines[] = trim($remarks);
        }
        if ($mechanicNames !== []) {
            $lines[] = 'Mechanic(s): '.implode(', ', $mechanicNames);
        }
        if ($repairTypes !== []) {
            $lines[] = 'Repair type(s): '.collect($repairTypes)
                ->map(fn (string $type): string => str($type)->replace('_', ' ')->title()->toString())
                ->implode(', ');
        }

        return $lines === [] ? null : implode(PHP_EOL, $lines);
    }
}
