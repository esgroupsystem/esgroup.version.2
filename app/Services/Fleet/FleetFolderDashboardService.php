<?php

declare(strict_types=1);

namespace App\Services\Fleet;

use App\Models\Bus;
use App\Models\BusForSaleRecord;
use App\Repositories\Contracts\Fleet\BusForSaleRecordRepositoryInterface;
use App\Repositories\Contracts\Fleet\BusRepositoryInterface;
use App\Support\Fleet\FleetValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The folder tabs on Bus Analytics: one tab per garage (buses by company, each flagged when for
 * sale) and a "For Sale" tab (records by company). Garages named like "For Sale" get no tab.
 */
final class FleetFolderDashboardService
{
    private const RESERVED_GARAGES = ['FOR SALE', 'FORSALE', 'FOR-SALE', 'FOR SALE UNITS', 'FOR SALE UNIT'];

    public function __construct(
        private readonly BusRepositoryInterface $buses,
        private readonly BusForSaleRecordRepositoryInterface $records,
    ) {}

    /**
     * @param  array{search: string, company: string, operational_status: string}  $filters
     * @return array{tabs: Collection<int, array<string, mixed>>, total_units: int, total_for_sale: int}
     */
    public function getFolderDashboardData(array $filters): array
    {
        $forSale = $this->records->folderRows(['search' => $filters['search'], 'company' => $filters['company']]);
        $forSaleIds = $forSale->pluck('bus_id')->filter()->map(fn (mixed $id): int => (int) $id)->values();
        $forSaleNumbers = $forSale->pluck('bus_no')->filter()->map(fn (mixed $busNo): string => Str::upper(trim((string) $busNo)))->values();
        $buses = $this->buses->folderRows($filters);

        $tabs = $buses
            // Grouped without regard to case: 'Mirasol' and 'MIRASOL' are one garage (and one tab key).
            ->groupBy(fn (Bus $bus): string => FleetValue::upper($bus->garage) ?? 'Unassigned Garage')
            ->reject(fn (Collection $garageBuses, int|string $garage): bool => in_array(Str::upper(trim((string) $garage)), self::RESERVED_GARAGES, true))
            ->map(fn (Collection $garageBuses, int|string $garage): array => [
                'type' => 'garage',
                'key' => 'garage-'.Str::slug((string) $garage),
                'label' => (string) $garage,
                'count' => $garageBuses->count(),
                'companies' => $garageBuses->groupBy(fn (Bus $bus): string => FleetValue::upper($bus->company) ?? 'No Company')->sortKeys()
                    ->map(fn (Collection $companyBuses): Collection => $companyBuses->map(fn (Bus $bus): array => [
                        'bus' => $bus,
                        // Linked by id, or by bus number.
                        'for_sale' => $forSaleIds->contains((int) $bus->id) || $forSaleNumbers->contains(Str::upper(trim((string) $bus->bus_no))),
                    ])),
            ])
            ->sortBy(fn (array $tab): string => match (Str::upper($tab['label'])) {
                'MIRASOL' => '00_MIRASOL',
                'BALINTAWAK' => '01_BALINTAWAK',
                default => '99_'.Str::upper($tab['label']),
            })
            ->values()
            ->push([
                'type' => 'for_sale',
                'key' => 'for-sale-records',
                'label' => 'For Sale',
                'count' => $forSale->count(),
                'records' => $forSale->groupBy(fn (BusForSaleRecord $record): string => FleetValue::upper($record->company) ?? 'No Company')->sortKeys()
                    ->map(fn (Collection $records): Collection => $records->map(fn (BusForSaleRecord $record): array => [
                        'record' => $record,
                        'status_label' => self::statusLabel($record->status),
                        'days' => FleetValue::breakdownDays($record->breakdown_start_date, $record->breakdown_end_date),
                    ])),
            ]);

        return ['tabs' => $tabs, 'total_units' => $buses->count(), 'total_for_sale' => $forSale->count()];
    }

    /** Folder wording for a for-sale status (older short keys included). */
    private static function statusLabel(?string $status): string
    {
        return match ($status) {
            'mechanical_breakdown' => 'Mechanical Breakdown',
            'accident_related' => 'Accident Related',
            'on_hold' => 'On Hold',
            'running_condition' => 'Running Condition',
            default => $status ? Str::title(str_replace('_', ' ', $status)) : 'For Sale',
        };
    }
}
