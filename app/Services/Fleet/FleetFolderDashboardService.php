<?php

namespace App\Services\Fleet;

use App\Models\Bus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FleetFolderDashboardService
{
    public function getFolderDashboardData(Request $request): array
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'company' => trim((string) $request->query('company', '')),
            'operational_status' => trim((string) $request->query('operational_status', '')),
        ];

        $forSaleRows = $this->getForSaleRows($filters);

        $forSaleBusIds = $forSaleRows
            ->pluck('bus_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->values();

        $forSaleBusNumbers = $forSaleRows
            ->pluck('bus_no')
            ->filter()
            ->map(fn ($busNo): string => Str::upper(trim((string) $busNo)))
            ->values();

        $buses = $this->getBusRows($filters);

        $garageTabs = $this->buildGarageTabs(
            buses: $buses,
            forSaleBusIds: $forSaleBusIds,
            forSaleBusNumbers: $forSaleBusNumbers
        );

        $tabs = $garageTabs->push([
            'type' => 'for_sale',
            'key' => 'for-sale-records',
            'label' => 'For Sale',
            'count' => $forSaleRows->count(),
            'records' => $forSaleRows
                ->groupBy(fn ($record): string => $record->company ?: 'No Company')
                ->sortKeys(),
        ]);

        return [
            'filters' => $filters,
            'tabs' => $tabs,
            'companies' => Bus::query()
                ->whereNotNull('company')
                ->distinct()
                ->orderBy('company')
                ->pluck('company'),
            'total_units' => $buses->count(),
            'total_for_sale' => $forSaleRows->count(),
        ];
    }

    private function getBusRows(array $filters): Collection
    {
        return Bus::query()
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('bus_no', 'like', "%{$search}%")
                        ->orWhere('plate_no', 'like', "%{$search}%")
                        ->orWhere('chassis_number', 'like', "%{$search}%")
                        ->orWhere('engine_number', 'like', "%{$search}%")
                        ->orWhere('case_number', 'like', "%{$search}%");
                });
            })
            ->when($filters['company'] !== '', function (Builder $query) use ($filters): void {
                $query->where('company', $filters['company']);
            })
            ->when($filters['operational_status'] !== '', function (Builder $query) use ($filters): void {
                $query->where('operational_status', $filters['operational_status']);
            })
            ->orderByRaw("
                CASE
                    WHEN UPPER(garage) = 'MIRASOL' THEN 0
                    WHEN UPPER(garage) = 'BALINTAWAK' THEN 1
                    ELSE 2
                END
            ")
            ->orderBy('garage')
            ->orderBy('company')
            ->orderByRaw('CAST(bus_no AS UNSIGNED), bus_no')
            ->get();
    }

    private function getForSaleRows(array $filters): Collection
    {
        if (! Schema::hasTable('bus_for_sale_records')) {
            return collect();
        }

        $columns = [
            'id',
            'bus_no',
            'plate_no',
            'company',
            'garage',
            'status',
            'storage_area',
            'breakdown_start_date',
            'breakdown_end_date',
            'column_11',
            'unit_location',
            'progress',
            'remarks',
        ];

        if (Schema::hasColumn('bus_for_sale_records', 'bus_id')) {
            $columns[] = 'bus_id';
        }

        $query = DB::table('bus_for_sale_records')
            ->select($columns);

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($query) use ($search): void {
                $query->where('bus_no', 'like', "%{$search}%")
                    ->orWhere('plate_no', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('garage', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        if ($filters['company'] !== '') {
            $query->where('company', $filters['company']);
        }

        return $query
            ->orderBy('company')
            ->orderByRaw('CAST(bus_no AS UNSIGNED), bus_no')
            ->get()
            ->map(function ($record) {
                $record->status_label = $this->statusLabel($record->status ?? null);
                $record->status_badge_class = $this->statusBadgeClass($record->status ?? null);
                $record->live_days_in_breakdown = $this->liveDaysInBreakdown(
                    $record->breakdown_start_date ?? null,
                    $record->breakdown_end_date ?? null
                );

                return $record;
            });
    }

    private function buildGarageTabs(
        Collection $buses,
        Collection $forSaleBusIds,
        Collection $forSaleBusNumbers
    ): Collection {
        return $buses
            ->groupBy(fn (Bus $bus): string => $bus->garage ?: 'Unassigned Garage')
            ->reject(function (Collection $garageBuses, string $garage): bool {
                return $this->isReservedForSaleGarage($garage);
            })
            ->map(function (Collection $garageBuses, string $garage) use ($forSaleBusIds, $forSaleBusNumbers): array {
                return [
                    'type' => 'garage',
                    'key' => 'garage-'.Str::slug($garage),
                    'label' => $garage,
                    'count' => $garageBuses->count(),
                    'companies' => $garageBuses
                        ->groupBy(fn (Bus $bus): string => $bus->company ?: 'No Company')
                        ->sortKeys(),
                    'for_sale_bus_ids' => $forSaleBusIds,
                    'for_sale_bus_numbers' => $forSaleBusNumbers,
                ];
            })
            ->sortBy(function (array $tab): string {
                $label = Str::upper($tab['label']);

                return match ($label) {
                    'MIRASOL' => '00_'.$label,
                    'BALINTAWAK' => '01_'.$label,
                    default => '99_'.$label,
                };
            })
            ->values();
    }

    private function isReservedForSaleGarage(string $garage): bool
    {
        $normalizedGarage = Str::upper(trim($garage));

        return in_array($normalizedGarage, [
            'FOR SALE',
            'FORSALE',
            'FOR-SALE',
            'FOR SALE UNITS',
            'FOR SALE UNIT',
        ], true);
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'mechanical_breakdown' => 'Mechanical Breakdown',
            'accident_related' => 'Accident Related',
            'on_hold' => 'On Hold',
            'running_condition' => 'Running Condition',
            default => $status ? Str::title(str_replace('_', ' ', $status)) : 'For Sale',
        };
    }

    private function statusBadgeClass(?string $status): string
    {
        return match ($status) {
            'mechanical_breakdown' => 'badge-subtle-warning text-warning',
            'accident_related' => 'badge-subtle-danger text-danger',
            'on_hold' => 'badge-subtle-info text-info',
            'running_condition' => 'badge-subtle-success text-success',
            default => 'badge-subtle-secondary text-secondary',
        };
    }

    private function liveDaysInBreakdown(?string $startDate, ?string $endDate): int
    {
        if (! $startDate) {
            return 0;
        }

        $start = Carbon::parse($startDate)->startOfDay();

        $end = $endDate
            ? Carbon::parse($endDate)->startOfDay()
            : now()->startOfDay();

        return max(0, $start->diffInDays($end));
    }
}
