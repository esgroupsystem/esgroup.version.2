<?php

namespace App\Services\Fleet;

use App\Models\Bus;
use App\Models\BusForSaleRecord;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ForSaleUnitService
{
    public function getIndexData(array $filters = []): array
    {
        $records = $this->filteredQuery($filters)
            ->with('bus')
            ->orderByDesc('days_in_breakdown')
            ->orderBy('company')
            ->orderBy('garage')
            ->orderBy('bus_no')
            ->paginate(25)
            ->withQueryString();

        return [
            'filters' => $filters,
            'records' => $records,
            'summary' => $this->summary(),
            'companies' => $this->getCompanies(),
            'garages' => $this->getGarages(),
            'status_options' => BusForSaleRecord::statusOptions(),
        ];
    }

    public function getFormData(): array
    {
        return [
            'buses_json' => Bus::query()
                ->get(['bus_no', 'plate_no', 'company', 'garage'])
                ->mapWithKeys(function ($bus) {
                    return [
                        strtoupper($bus->bus_no) => [
                            'plate_no' => $bus->plate_no,
                            'company' => $bus->company,
                            'garage' => $bus->garage,
                        ],
                    ];
                }),

            'status_options' => BusForSaleRecord::statusOptions(),
            'buses' => Bus::query()
                ->orderBy('bus_no')
                ->get(['id', 'bus_no', 'plate_no', 'company', 'garage']),
        ];
    }

    public function create(array $validated): BusForSaleRecord
    {
        return DB::transaction(function () use ($validated): BusForSaleRecord {
            $data = $this->normalizeData($validated);
            $data['days_in_breakdown'] = $this->computeDaysInBreakdown($data);

            $record = BusForSaleRecord::query()->create($data);

            $this->syncBusFromRecord($record);

            return $record->fresh(['bus']);
        });
    }

    public function update(BusForSaleRecord $record, array $validated): BusForSaleRecord
    {
        return DB::transaction(function () use ($record, $validated): BusForSaleRecord {
            $data = $this->normalizeData($validated);
            $data['days_in_breakdown'] = $this->computeDaysInBreakdown($data);

            $record->update($data);

            $this->syncBusFromRecord($record);

            return $record->fresh(['bus']);
        });
    }

    public function delete(BusForSaleRecord $record): void
    {
        DB::transaction(function () use ($record): void {
            $bus = $record->bus;

            $record->delete();

            if (! $bus) {
                return;
            }

            $stillForSale = BusForSaleRecord::query()
                ->where('bus_id', $bus->id)
                ->exists();

            if (! $stillForSale) {
                $bus->update([
                    'sale_status' => Bus::SALE_NOT_FOR_SALE,
                    'status_updated_at' => now(),
                ]);
            }
        });
    }

    private function filteredQuery(array $filters): Builder
    {
        return BusForSaleRecord::query()
            ->when($this->filled($filters, 'search'), function (Builder $query) use ($filters): void {
                $search = trim((string) $filters['search']);

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('bus_no', 'like', "%{$search}%")
                        ->orWhere('plate_no', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('garage', 'like', "%{$search}%")
                        ->orWhere('storage_area', 'like', "%{$search}%")
                        ->orWhere('unit_location', 'like', "%{$search}%")
                        ->orWhere('progress', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->when($this->filled($filters, 'company'), function (Builder $query) use ($filters): void {
                $query->where('company', $this->uppercase($filters['company']));
            })
            ->when($this->filled($filters, 'garage'), function (Builder $query) use ($filters): void {
                $query->where('garage', $this->uppercase($filters['garage']));
            })
            ->when($this->filled($filters, 'status'), function (Builder $query) use ($filters): void {
                $query->where('status', trim((string) $filters['status']));
            });
    }

    private function summary(): array
    {
        $statusCounts = BusForSaleRecord::query()
            ->selectRaw('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = BusForSaleRecord::query()->count();

        $mechanical = (int) ($statusCounts[Bus::STATUS_MECHANICAL_BREAKDOWN] ?? 0);
        $accident = (int) ($statusCounts[Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN] ?? 0);
        $onHold = (int) ($statusCounts[Bus::STATUS_ON_HOLD_PLATE_REGISTRATION] ?? 0);
        $running = (int) ($statusCounts[Bus::STATUS_ACTIVE] ?? 0);

        return [
            'total' => $total,
            'running_condition' => $running,
            'mechanical_breakdown' => $mechanical,
            'accident_related' => $accident,
            'on_hold' => $onHold,
            'breakdown_total' => $mechanical + $accident + $onHold,
        ];
    }

    private function syncBusFromRecord(BusForSaleRecord $record): void
    {
        $bus = Bus::query()
            ->where('bus_no', $record->bus_no)
            ->first();

        if (! $bus) {
            $bus = new Bus([
                'bus_no' => $record->bus_no,
            ]);
        }

        $bus->fill([
            'plate_no' => $record->plate_no,
            'company' => $record->company,
            'garage' => $record->garage,
            'operational_status' => $record->status,
            'sale_status' => Bus::SALE_FOR_SALE,
            'monitoring_remarks' => $record->remarks,
            'status_updated_at' => now(),
        ]);

        $bus->save();

        if ($record->bus_id !== $bus->id) {
            $record->updateQuietly([
                'bus_id' => $bus->id,
            ]);
        }
    }

    private function normalizeData(array $data): array
    {
        return [
            'bus_no' => $this->uppercase($data['bus_no'] ?? null),
            'plate_no' => $this->uppercase($data['plate_no'] ?? null),
            'company' => $this->uppercase($data['company'] ?? null),
            'garage' => $this->uppercase($data['garage'] ?? null),
            'status' => trim((string) ($data['status'] ?? Bus::STATUS_ACTIVE)),
            'storage_area' => $this->nullableString($data['storage_area'] ?? null),
            'breakdown_start_date' => $this->nullableDate($data['breakdown_start_date'] ?? null),
            'breakdown_end_date' => $this->nullableDate($data['breakdown_end_date'] ?? null),
            'column_11' => $this->nullableString($data['column_11'] ?? null),
            'unit_location' => $this->nullableString($data['unit_location'] ?? null),
            'progress' => $this->nullableString($data['progress'] ?? null),
            'remarks' => $this->nullableString($data['remarks'] ?? null),
        ];
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

    private function getCompanies(): Collection
    {
        return BusForSaleRecord::query()
            ->whereNotNull('company')
            ->where('company', '!=', '')
            ->distinct()
            ->orderBy('company')
            ->pluck('company');
    }

    private function getGarages(): Collection
    {
        return BusForSaleRecord::query()
            ->whereNotNull('garage')
            ->where('garage', '!=', '')
            ->distinct()
            ->orderBy('garage')
            ->pluck('garage');
    }

    private function uppercase(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return mb_strtoupper($value);
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableDate(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function filled(array $filters, string $key): bool
    {
        return isset($filters[$key]) && trim((string) $filters[$key]) !== '';
    }
}
