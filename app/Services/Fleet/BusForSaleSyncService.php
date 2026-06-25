<?php

namespace App\Services\Fleet;

use App\Models\Bus;
use App\Models\BusForSaleRecord;
use Carbon\Carbon;

class BusForSaleSyncService
{
    public function syncFromBus(Bus $bus): ?BusForSaleRecord
    {
        if ($bus->sale_status !== Bus::SALE_FOR_SALE) {
            BusForSaleRecord::query()
                ->where('bus_id', $bus->id)
                ->delete();

            return null;
        }

        $record = BusForSaleRecord::query()
            ->firstOrNew([
                'bus_id' => $bus->id,
            ]);

        $record->fill([
            'bus_id' => $bus->id,
            'bus_no' => $this->uppercase($bus->bus_no),
            'plate_no' => $this->uppercase($bus->plate_no),
            'company' => $this->uppercase($bus->company),
            'garage' => $this->uppercase($bus->garage),
            'status' => $bus->operational_status ?: Bus::STATUS_ACTIVE,
            'remarks' => $bus->monitoring_remarks,
        ]);

        $record->days_in_breakdown = $this->computeDaysInBreakdown([
            'breakdown_start_date' => $record->breakdown_start_date,
            'breakdown_end_date' => $record->breakdown_end_date,
        ]);

        $record->save();

        return $record->fresh(['bus']);
    }

    public function syncFromForSaleRecord(BusForSaleRecord $record): Bus
    {
        $bus = $this->resolveBus($record) ?? new Bus();

        $bus->fill([
            'bus_no' => $this->uppercase($record->bus_no),
            'plate_no' => $this->uppercase($record->plate_no),
            'company' => $this->uppercase($record->company),
            'garage' => $this->uppercase($record->garage),
            'operational_status' => $record->status ?: Bus::STATUS_ACTIVE,
            'sale_status' => Bus::SALE_FOR_SALE,
            'monitoring_remarks' => $record->remarks,
            'status_updated_at' => now(),
        ]);

        $bus->save();

        BusForSaleRecord::query()
            ->where('bus_id', $bus->id)
            ->whereKeyNot($record->id)
            ->delete();

        if ((int) $record->bus_id !== (int) $bus->id) {
            $record->updateQuietly([
                'bus_id' => $bus->id,
            ]);
        }

        return $bus->fresh(['currentForSaleRecord']);
    }

    private function resolveBus(BusForSaleRecord $record): ?Bus
    {
        if ($record->bus_id) {
            return Bus::query()->find($record->bus_id);
        }

        $query = Bus::query()
            ->where('bus_no', $record->bus_no);

        if ($record->plate_no) {
            $query->where('plate_no', $record->plate_no);
        }

        if ($record->company) {
            $query->where('company', $record->company);
        }

        if ($record->garage) {
            $query->where('garage', $record->garage);
        }

        if ($query->count() !== 1) {
            return null;
        }

        return $query->first();
    }

    private function computeDaysInBreakdown(array $data): int
    {
        if (empty($data['breakdown_start_date'])) {
            return 0;
        }

        $startDate = Carbon::parse($data['breakdown_start_date'])->startOfDay();

        $endDate = ! empty($data['breakdown_end_date'])
            ? Carbon::parse($data['breakdown_end_date'])->startOfDay()
            : now()->startOfDay();

        if ($endDate->lessThan($startDate)) {
            return 0;
        }

        return (int) $startDate->diffInDays($endDate);
    }

    private function uppercase(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return mb_strtoupper($value);
    }
}
