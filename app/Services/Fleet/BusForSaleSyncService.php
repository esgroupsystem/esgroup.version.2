<?php

declare(strict_types=1);

namespace App\Services\Fleet;

use App\Models\Bus;
use App\Models\BusForSaleRecord;
use App\Repositories\Contracts\Fleet\BusForSaleRecordRepositoryInterface;
use App\Repositories\Contracts\Fleet\BusRepositoryInterface;
use App\Support\Fleet\FleetValue;

/**
 * Keeps a bus and its for-sale record in step: a bus marked "for sale" gets a record, a record
 * marks its bus "for sale" (creating the bus when no single bus matches).
 */
final class BusForSaleSyncService
{
    public function __construct(
        private readonly BusRepositoryInterface $buses,
        private readonly BusForSaleRecordRepositoryInterface $records,
    ) {}

    public function syncFromBus(Bus $bus): ?BusForSaleRecord
    {
        if ($bus->sale_status !== Bus::SALE_FOR_SALE) {
            $this->records->deleteForBus((int) $bus->id);

            return null;
        }

        $record = $this->records->forBus((int) $bus->id) ?? new BusForSaleRecord(['bus_id' => $bus->id]);
        $record->fill([
            'bus_id' => $bus->id,
            'bus_no' => FleetValue::upper($bus->bus_no),
            'plate_no' => FleetValue::upper($bus->plate_no),
            'company' => FleetValue::upper($bus->company),
            'garage' => FleetValue::upper($bus->garage),
            'status' => $bus->operational_status ?: Bus::STATUS_ACTIVE,
            'remarks' => $bus->monitoring_remarks,
        ]);
        $record->days_in_breakdown = FleetValue::breakdownDays($record->breakdown_start_date, $record->breakdown_end_date);
        $this->records->save($record);

        return $record->fresh(['bus']);
    }

    public function syncFromForSaleRecord(BusForSaleRecord $record): Bus
    {
        $bus = ($record->bus_id ? $this->buses->find((int) $record->bus_id) : $this->buses->findUnique($record->bus_no, $record->plate_no, $record->company, $record->garage))
            ?? new Bus;

        $bus->fill([
            'bus_no' => FleetValue::upper($record->bus_no),
            'plate_no' => FleetValue::upper($record->plate_no),
            'company' => FleetValue::upper($record->company),
            'garage' => FleetValue::upper($record->garage),
            'operational_status' => $record->status ?: Bus::STATUS_ACTIVE,
            'sale_status' => Bus::SALE_FOR_SALE,
            'monitoring_remarks' => $record->remarks,
            'status_updated_at' => now(),
        ]);
        $this->buses->save($bus);

        $this->records->deleteForBus((int) $bus->id, (int) $record->id);
        if ((int) $record->bus_id !== (int) $bus->id) {
            $this->records->linkBus($record, (int) $bus->id);
        }

        return $bus->fresh(['currentForSaleRecord']);
    }
}
