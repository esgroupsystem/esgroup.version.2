<?php

declare(strict_types=1);

namespace App\Services\Fleet;

use App\Models\Bus;
use App\Models\BusForSaleRecord;
use App\Repositories\Contracts\Fleet\BusForSaleRecordRepositoryInterface;
use App\Repositories\Contracts\Fleet\BusRepositoryInterface;
use App\Support\Fleet\FleetValue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Fleet → For Sale Units: the list, its status summary, and create / edit / delete (synced to the bus). */
final class ForSaleUnitService
{
    public function __construct(
        private readonly BusForSaleRecordRepositoryInterface $records,
        private readonly BusRepositoryInterface $buses,
        private readonly BusForSaleSyncService $sync,
    ) {}

    /**
     * @param  array<string, mixed>  $filters  search, company, garage, status
     * @return array{records: LengthAwarePaginator<int, BusForSaleRecord>, summary: array<string, int>, companies: Collection<int, string>, garages: Collection<int, string>}
     */
    public function getIndexData(array $filters = []): array
    {
        $text = fn (string $key): string => trim((string) ($filters[$key] ?? ''));

        return [
            'records' => $this->records->paginate([
                'search' => $text('search'),
                'company' => (string) FleetValue::upper($text('company')),
                'garage' => (string) FleetValue::upper($text('garage')),
                'status' => $text('status'),
            ]),
            'summary' => $this->summary(),
            'companies' => $this->records->companies(),
            'garages' => $this->records->garages(),
        ];
    }

    /** @return Collection<int, Bus> buses for the form picker */
    public function busOptions(): Collection
    {
        return $this->buses->pickerOptions();
    }

    /** @param array<string, mixed> $validated ForSaleUnitRequest */
    public function create(array $validated): BusForSaleRecord
    {
        return DB::transaction(function () use ($validated): BusForSaleRecord {
            $data = $this->normalize($validated);
            // One record per bus: picking a bus that already has one updates it.
            $record = ($data['bus_id'] ? $this->records->forBus($data['bus_id']) : null) ?? new BusForSaleRecord;

            return $this->saveAndSync($record, $data);
        });
    }

    /** @param array<string, mixed> $validated ForSaleUnitRequest */
    public function update(BusForSaleRecord $record, array $validated): BusForSaleRecord
    {
        return DB::transaction(fn (): BusForSaleRecord => $this->saveAndSync($record, $this->normalize($validated)));
    }

    /** Deletes the record; its bus goes back to "not for sale" when no other record points to it. */
    public function delete(BusForSaleRecord $record): void
    {
        DB::transaction(function () use ($record): void {
            $bus = $record->bus;
            $this->records->delete($record);

            if ($bus !== null && ! $this->records->existsForBus((int) $bus->id)) {
                $bus->fill(['sale_status' => Bus::SALE_NOT_FOR_SALE, 'status_updated_at' => now()]);
                $this->buses->save($bus);
            }
        });
    }

    /** @param array<string, mixed> $data */
    private function saveAndSync(BusForSaleRecord $record, array $data): BusForSaleRecord
    {
        $record->fill([...$data, 'days_in_breakdown' => FleetValue::breakdownDays($data['breakdown_start_date'], $data['breakdown_end_date'])]);
        $this->records->save($record);
        $this->sync->syncFromForSaleRecord($record);

        return $record->fresh(['bus']);
    }

    /** @return array{total: int, running_condition: int, mechanical_breakdown: int, accident_related: int, on_hold: int, breakdown_total: int} */
    private function summary(): array
    {
        $counts = $this->records->countByStatus();
        $mechanical = (int) ($counts[Bus::STATUS_MECHANICAL_BREAKDOWN] ?? 0);
        $accident = (int) ($counts[Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN] ?? 0);
        $onHold = (int) ($counts[Bus::STATUS_ON_HOLD_PLATE_REGISTRATION] ?? 0);

        return [
            'total' => $this->records->count(),
            'running_condition' => (int) ($counts[Bus::STATUS_ACTIVE] ?? 0),
            'mechanical_breakdown' => $mechanical,
            'accident_related' => $accident,
            'on_hold' => $onHold,
            'breakdown_total' => $mechanical + $accident + $onHold,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        return [
            'bus_id' => ($data['bus_id'] ?? null) === null || $data['bus_id'] === '' ? null : (int) $data['bus_id'],
            'bus_no' => FleetValue::upper($data['bus_no'] ?? null),
            'plate_no' => FleetValue::upper($data['plate_no'] ?? null),
            'company' => FleetValue::upper($data['company'] ?? null),
            'garage' => FleetValue::upper($data['garage'] ?? null),
            'status' => trim((string) ($data['status'] ?? Bus::STATUS_ACTIVE)),
            'storage_area' => FleetValue::text($data['storage_area'] ?? null),
            'breakdown_start_date' => FleetValue::text($data['breakdown_start_date'] ?? null),
            'breakdown_end_date' => FleetValue::text($data['breakdown_end_date'] ?? null),
            'column_11' => FleetValue::text($data['column_11'] ?? null),
            'unit_location' => FleetValue::text($data['unit_location'] ?? null),
            'progress' => FleetValue::text($data['progress'] ?? null),
            'remarks' => FleetValue::text($data['remarks'] ?? null),
        ];
    }
}
