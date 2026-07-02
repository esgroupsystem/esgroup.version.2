<?php

namespace App\Services\Fleet;

use App\Models\Bus;
use App\Models\BusForSaleRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BusService
{
    public function __construct(
        private readonly BusForSaleSyncService $busForSaleSyncService
    ) {}

    public function getMonitoringDashboard(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);

        $filteredBuses = $this->filteredBusQuery($filters)
            ->orderBy('garage')
            ->orderBy('company')
            ->orderBy('bus_no')
            ->get();

        $groupedBuses = $filteredBuses
            ->groupBy(fn (Bus $bus): string => $this->displayGroup($bus->garage))
            ->map(function (Collection $garageBuses): Collection {
                return $garageBuses->groupBy(
                    fn (Bus $bus): string => $this->displayGroup($bus->company)
                );
            });

        return [
            'filters' => $filters,

            'garages' => $this->getGarages(),
            'companies' => $this->getCompanies(),
            'operational_status_options' => Bus::operationalStatusOptions(),
            'sale_status_options' => Bus::saleStatusOptions(),

            'grouped_buses' => $groupedBuses,

            'garage_summary_raw' => $filteredBuses->groupBy(
                fn (Bus $bus): string => $this->displayGroup($bus->garage)
            ),
            'company_summary_raw' => $filteredBuses->groupBy(
                fn (Bus $bus): string => $this->displayGroup($bus->company)
            ),

            'garage_summary' => $this->summaryBy('garage'),
            'company_summary' => $this->summaryBy('company'),
            'for_sale_summary' => $this->forSaleSummary(),
            'for_sale_records' => $this->dashboardForSaleRecords($filters),
            'for_sale_bus_ids' => $this->forSaleBusIds(),
            'for_sale_bus_numbers' => $this->forSaleBusNumbers(),
            'totals' => $this->overallTotals(),

            'filtered_count' => $filteredBuses->count(),
        ];
    }

    public function getGroupedByGarageAndCompany(): array
    {
        $dashboard = $this->getMonitoringDashboard();

        return [
            'mirasol' => $dashboard['grouped_buses']->get('MIRASOL', collect()),
            'balintawak' => $dashboard['grouped_buses']->get('BALINTAWAK', collect()),
        ];
    }

    public function getAnalytics(): array
    {
        $dashboard = $this->getMonitoringDashboard();

        return [
            'garage_summary' => $dashboard['garage_summary'],
            'company_summary' => $dashboard['company_summary'],
            'for_sale_summary' => $dashboard['for_sale_summary'],
            'total_units' => $dashboard['totals']['total_units'],
        ];
    }

    private function filteredBusQuery(array $filters): Builder
    {
        return Bus::query()
            ->when($this->filled($filters, 'search'), function (Builder $query) use ($filters): void {
                $search = strtoupper(trim((string) $filters['search']));

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('bus_no', 'like', "%{$search}%")
                        ->orWhere('plate_no', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('garage', 'like', "%{$search}%")
                        ->orWhere('chassis_number', 'like', "%{$search}%")
                        ->orWhere('engine_number', 'like', "%{$search}%")
                        ->orWhere('case_number', 'like', "%{$search}%");
                });
            })
            ->when($this->filled($filters, 'garage'), function (Builder $query) use ($filters): void {
                $query->where('garage', strtoupper(trim((string) $filters['garage'])));
            })
            ->when($this->filled($filters, 'company'), function (Builder $query) use ($filters): void {
                $query->where('company', strtoupper(trim((string) $filters['company'])));
            })
            ->when($this->filled($filters, 'operational_status'), function (Builder $query) use ($filters): void {
                $query->where('operational_status', trim((string) $filters['operational_status']));
            })
            ->when($this->filled($filters, 'sale_status'), function (Builder $query) use ($filters): void {
                $saleStatus = trim((string) $filters['sale_status']);

                if ($saleStatus === Bus::SALE_FOR_SALE) {
                    $this->applyForSaleConstraint($query);

                    return;
                }

                if ($this->isNotForSaleStatus($saleStatus)) {
                    $this->applyNotForSaleConstraint($query);

                    return;
                }

                $query->where('sale_status', $saleStatus);
            });
    }

    private function dashboardForSaleRecords(array $filters): Collection
    {
        return BusForSaleRecord::query()
            ->when($this->filled($filters, 'search'), function (Builder $query) use ($filters): void {
                $search = strtoupper(trim((string) $filters['search']));

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('bus_no', 'like', "%{$search}%")
                        ->orWhere('plate_no', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('garage', 'like', "%{$search}%")
                        ->orWhere('unit_location', 'like', "%{$search}%")
                        ->orWhere('progress', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->when($this->filled($filters, 'garage'), function (Builder $query) use ($filters): void {
                $query->where('garage', strtoupper(trim((string) $filters['garage'])));
            })
            ->when($this->filled($filters, 'company'), function (Builder $query) use ($filters): void {
                $query->where('company', strtoupper(trim((string) $filters['company'])));
            })
            ->when($this->filled($filters, 'operational_status'), function (Builder $query) use ($filters): void {
                $status = trim((string) $filters['operational_status']);

                $query->whereIn('status', $this->statusVariants($status));
            })
            ->when($this->filled($filters, 'sale_status'), function (Builder $query) use ($filters): void {
                $saleStatus = trim((string) $filters['sale_status']);

                if ($saleStatus !== Bus::SALE_FOR_SALE) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->orderByDesc('days_in_breakdown')
            ->orderBy('company')
            ->orderBy('garage')
            ->orderBy('bus_no')
            ->limit(50)
            ->get();
    }

    private function summaryBy(string $column): Collection
    {
        if (! in_array($column, ['garage', 'company'], true)) {
            throw new InvalidArgumentException('Invalid fleet summary column.');
        }

        $busAlias = 'b';
        $forSaleAlias = 'fs';

        $groupExpression = sprintf(
            "COALESCE(NULLIF(%s, ''), 'UNKNOWN')",
            $this->qualifiedColumn($busAlias, $column)
        );

        $rows = Bus::query()
            ->from('buses as b')
            ->selectRaw("{$groupExpression} as group_name")

            // Total registered units, including for sale.
            ->selectRaw('COUNT(*) as total_units')

            // Total units that are NOT for sale.
            ->selectRaw($this->countNotForSaleSql($busAlias, $forSaleAlias).' as not_for_sale')

            // These are counted only from NOT FOR SALE units.
            ->selectRaw($this->countStatusNotForSaleSql(
                Bus::STATUS_MECHANICAL_BREAKDOWN,
                $busAlias,
                $forSaleAlias
            ).' as mechanical_breakdown')

            ->selectRaw($this->countStatusNotForSaleSql(
                Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN,
                $busAlias,
                $forSaleAlias
            ).' as accident_related')

            ->selectRaw($this->countStatusNotForSaleSql(
                Bus::STATUS_ON_HOLD_PLATE_REGISTRATION,
                $busAlias,
                $forSaleAlias
            ).' as on_hold')

            // For-sale count for reference.
            ->selectRaw($this->countForSaleSql($busAlias, $forSaleAlias).' as for_sale')

            ->groupBy('group_name')
            ->orderBy('group_name')
            ->get();

        return $rows->mapWithKeys(function ($row): array {
            $notForSale = (int) $row->not_for_sale;
            $mechanical = (int) $row->mechanical_breakdown;
            $accident = (int) $row->accident_related;
            $onHold = (int) $row->on_hold;

            /*
             * Required rule:
             * Active = Not For Sale - Mechanical - Accident - On Hold
             */
            $active = max($notForSale - $mechanical - $accident - $onHold, 0);

            return [
                $row->group_name => [
                    'active' => $active,
                    'mechanical_breakdown' => $mechanical,
                    'accident_related' => $accident,
                    'on_hold' => $onHold,
                    'for_sale' => (int) $row->for_sale,

                    // Use this as the Garage/Company Summary total.
                    'not_for_sale' => $notForSale,

                    // Keep this available if you still need all registered units.
                    'total_units' => (int) $row->total_units,

                    // Backward compatibility for your existing Blade.
                    'total' => $notForSale,
                ],
            ];
        });
    }

    private function forSaleSummary(): array
    {
        $rows = BusForSaleRecord::query()
            ->selectRaw("COALESCE(NULLIF(company, ''), 'UNKNOWN') as company_name")
            ->selectRaw('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('company', 'status')
            ->orderBy('company')
            ->get();

        $summary = [];

        foreach ($rows as $row) {
            $company = (string) $row->company_name;
            $count = (int) $row->total;
            $status = $this->normalizeOperationalStatus((string) $row->status);

            if (! isset($summary[$company])) {
                $summary[$company] = [
                    'mechanical_breakdown' => 0,
                    'accident_related' => 0,
                    'on_hold' => 0,
                    'breakdown_total' => 0,
                    'running_condition' => 0,
                    'total_for_sale' => 0,
                ];
            }

            switch ($status) {
                case Bus::STATUS_MECHANICAL_BREAKDOWN:
                    $summary[$company]['mechanical_breakdown'] += $count;
                    break;

                case Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN:
                    $summary[$company]['accident_related'] += $count;
                    break;

                case Bus::STATUS_ON_HOLD_PLATE_REGISTRATION:
                    $summary[$company]['on_hold'] += $count;
                    break;

                case Bus::STATUS_ACTIVE:
                    $summary[$company]['running_condition'] += $count;
                    break;
            }
        }

        foreach ($summary as $company => $data) {
            $summary[$company]['breakdown_total'] =
                $data['mechanical_breakdown'] +
                $data['accident_related'] +
                $data['on_hold'];

            $summary[$company]['total_for_sale'] =
                $summary[$company]['breakdown_total'] +
                $summary[$company]['running_condition'];
        }

        return [
            'rows' => $summary,
            'mechanical_breakdown_total' => array_sum(array_column($summary, 'mechanical_breakdown')),
            'accident_related_total' => array_sum(array_column($summary, 'accident_related')),
            'on_hold_total' => array_sum(array_column($summary, 'on_hold')),
            'breakdown_total' => array_sum(array_column($summary, 'breakdown_total')),
            'running_condition_total' => array_sum(array_column($summary, 'running_condition')),
            'total_for_sale' => array_sum(array_column($summary, 'total_for_sale')),
        ];
    }

    private function overallTotals(): array
    {
        $totalUnits = Bus::query()->count();

        $forSale = $this->applyForSaleConstraint(
            Bus::query()
        )->count();

        $notForSale = $this->applyNotForSaleConstraint(
            Bus::query()
        )->count();

        $mechanicalBreakdown = $this->applyNotForSaleConstraint(
            Bus::query()->where('operational_status', Bus::STATUS_MECHANICAL_BREAKDOWN)
        )->count();

        $accidentRelated = $this->applyNotForSaleConstraint(
            Bus::query()->where('operational_status', Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN)
        )->count();

        $onHold = $this->applyNotForSaleConstraint(
            Bus::query()->where('operational_status', Bus::STATUS_ON_HOLD_PLATE_REGISTRATION)
        )->count();

        /*
         * Required rule:
         * Active = Not For Sale - Mechanical - Accident - On Hold
         */
        $active = max(
            $notForSale - $mechanicalBreakdown - $accidentRelated - $onHold,
            0
        );

        $activeForSale = $this->applyForSaleConstraint(
            Bus::query()->where('operational_status', Bus::STATUS_ACTIVE)
        )->count();

        return [
            'total_units' => $totalUnits,
            'active' => $active,
            'active_for_sale' => $activeForSale,
            'mechanical_breakdown' => $mechanicalBreakdown,
            'accident_related' => $accidentRelated,
            'on_hold' => $onHold,
            'for_sale' => $forSale,
            'not_for_sale' => $notForSale,
        ];
    }

    private function applyForSaleConstraint(Builder $query): Builder
    {
        $busTable = $this->busTable();
        $forSaleTable = $this->forSaleTable();

        return $query->whereExists(function ($subQuery) use ($busTable, $forSaleTable): void {
            $subQuery->selectRaw('1')
                ->from($forSaleTable)
                ->whereRaw($this->forSaleMatchRaw($busTable, $forSaleTable));
        });
    }

    private function applyNotForSaleConstraint(Builder $query): Builder
    {
        $busTable = $this->busTable();
        $forSaleTable = $this->forSaleTable();

        return $query->whereNotExists(function ($subQuery) use ($busTable, $forSaleTable): void {
            $subQuery->selectRaw('1')
                ->from($forSaleTable)
                ->whereRaw($this->forSaleMatchRaw($busTable, $forSaleTable));
        });
    }

    private function countActiveNotForSaleSql(string $busReference, ?string $forSaleAlias = null): string
    {
        $forSaleTable = $this->forSaleTable();
        $forSaleReference = $forSaleAlias ?: $forSaleTable;

        return '
            SUM(
                CASE
                    WHEN '.$this->qualifiedColumn($busReference, 'operational_status').' = '.$this->quote(Bus::STATUS_ACTIVE).'
                    AND NOT EXISTS (
                        SELECT 1
                        FROM '.$this->tableReference($forSaleTable, $forSaleAlias).'
                        WHERE '.$this->forSaleMatchRaw($busReference, $forSaleReference).'
                    )
                    THEN 1
                    ELSE 0
                END
            )
        ';
    }

    private function countActiveForSaleSql(string $busReference, ?string $forSaleAlias = null): string
    {
        $forSaleTable = $this->forSaleTable();
        $forSaleReference = $forSaleAlias ?: $forSaleTable;

        return '
            SUM(
                CASE
                    WHEN '.$this->qualifiedColumn($busReference, 'operational_status').' = '.$this->quote(Bus::STATUS_ACTIVE).'
                    AND EXISTS (
                        SELECT 1
                        FROM '.$this->tableReference($forSaleTable, $forSaleAlias).'
                        WHERE '.$this->forSaleMatchRaw($busReference, $forSaleReference).'
                    )
                    THEN 1
                    ELSE 0
                END
            )
        ';
    }

    private function countForSaleSql(string $busReference, ?string $forSaleAlias = null): string
    {
        $forSaleTable = $this->forSaleTable();
        $forSaleReference = $forSaleAlias ?: $forSaleTable;

        return '
            SUM(
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM '.$this->tableReference($forSaleTable, $forSaleAlias).'
                        WHERE '.$this->forSaleMatchRaw($busReference, $forSaleReference).'
                    )
                    THEN 1
                    ELSE 0
                END
            )
        ';
    }

    private function countStatusSql(string $status, string $busReference): string
    {
        return '
            SUM(
                CASE
                    WHEN '.$this->qualifiedColumn($busReference, 'operational_status').' = '.$this->quote($status).'
                    THEN 1
                    ELSE 0
                END
            )
        ';
    }

    private function forSaleMatchRaw(string $busReference, string $forSaleReference): string
    {
        $busId = $this->qualifiedColumn($busReference, 'id');
        $forSaleBusId = $this->qualifiedColumn($forSaleReference, 'bus_id');

        return "{$forSaleBusId} IS NOT NULL AND {$forSaleBusId} = {$busId}";
    }

    private function normalizeBusData(array $data): array
    {
        return [
            'bus_no' => $this->uppercase($data['bus_no'] ?? null),
            'plate_no' => $this->uppercase($data['plate_no'] ?? null),
            'company' => $this->uppercase($data['company'] ?? null),
            'garage' => $this->uppercase($data['garage'] ?? null),
            'chassis_number' => $this->uppercase($data['chassis_number'] ?? null),
            'engine_number' => $this->uppercase($data['engine_number'] ?? null),
            'case_number' => $this->uppercase($data['case_number'] ?? null),
            'operational_status' => $data['operational_status'] ?? Bus::STATUS_ACTIVE,
            'sale_status' => $data['sale_status'] ?? Bus::SALE_NOT_FOR_SALE,
            'monitoring_remarks' => $this->nullableString($data['monitoring_remarks'] ?? null),
        ];
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

    private function forSaleBusIds(): Collection
    {
        return BusForSaleRecord::query()
            ->whereNotNull('bus_id')
            ->pluck('bus_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();
    }

    private function forSaleBusNumbers(): Collection
    {
        return BusForSaleRecord::query()
            ->whereNotNull('bus_no')
            ->where('bus_no', '!=', '')
            ->pluck('bus_no')
            ->map(fn ($busNo): string => strtoupper(trim((string) $busNo)))
            ->filter()
            ->unique()
            ->values();
    }

    private function getGarages(): Collection
    {
        return Bus::query()
            ->whereNotNull('garage')
            ->where('garage', '!=', '')
            ->distinct()
            ->orderBy('garage')
            ->pluck('garage');
    }

    private function getCompanies(): Collection
    {
        return Bus::query()
            ->whereNotNull('company')
            ->where('company', '!=', '')
            ->distinct()
            ->orderBy('company')
            ->pluck('company');
    }

    private function normalizeFilters(array $filters): array
    {
        return collect($filters)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->all();
    }

    private function displayGroup(?string $value): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : 'UNKNOWN';
    }

    private function filled(array $filters, string $key): bool
    {
        return isset($filters[$key]) && trim((string) $filters[$key]) !== '';
    }

    private function isNotForSaleStatus(string $saleStatus): bool
    {
        $normalized = strtolower(str_replace(' ', '_', trim($saleStatus)));

        return $saleStatus === Bus::SALE_NOT_FOR_SALE || $normalized === 'not_for_sale';
    }

    private function normalizeOperationalStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        $normalized = str_replace(['-', '/', '.'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $slug = str_replace(' ', '_', $normalized);

        return match ($slug) {
            'active',
            'running',
            'running_condition' => Bus::STATUS_ACTIVE,

            'mechanical_breakdown',
            'mechanical' => Bus::STATUS_MECHANICAL_BREAKDOWN,

            'accident_related_breakdown',
            'accident_breakdown',
            'accident_related',
            'accident' => Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN,

            'on_hold_plate_registration',
            'on_hold_due_to_plate_reg',
            'on_hold_due_to_plate_registration',
            'on_hold',
            'plate_registration' => Bus::STATUS_ON_HOLD_PLATE_REGISTRATION,

            default => $slug,
        };
    }

    private function statusVariants(string $status): array
    {
        $normalized = $this->normalizeOperationalStatus($status);

        return match ($normalized) {
            Bus::STATUS_ACTIVE => [
                Bus::STATUS_ACTIVE,
                'Active',
                'Running',
                'Running Condition',
                'running_condition',
            ],

            Bus::STATUS_MECHANICAL_BREAKDOWN => [
                Bus::STATUS_MECHANICAL_BREAKDOWN,
                'Mechanical Breakdown',
                'Mechanical',
            ],

            Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN => [
                Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN,
                'Accident Related Breakdown',
                'Accident Breakdown',
                'Accident Related',
                'Accident',
            ],

            Bus::STATUS_ON_HOLD_PLATE_REGISTRATION => [
                Bus::STATUS_ON_HOLD_PLATE_REGISTRATION,
                'On Hold due to Plate Reg.',
                'On Hold due to Plate Registration',
                'On Hold Plate Registration',
                'On Hold',
            ],

            Bus::STATUS_FOR_RENTAL_CHARTER => [
                Bus::STATUS_FOR_RENTAL_CHARTER,
                'For Rental/Charter',
                'Rental',
                'Charter',
            ],

            Bus::STATUS_INACTIVE => [
                Bus::STATUS_INACTIVE,
                'Inactive',
                'Not Active',
            ],

            default => [$status, $normalized],
        };
    }

    private function countNotForSaleSql(string $busReference, ?string $forSaleAlias = null): string
    {
        $forSaleTable = $this->forSaleTable();
        $forSaleReference = $forSaleAlias ?: $forSaleTable;

        return '
        SUM(
            CASE
                WHEN NOT EXISTS (
                    SELECT 1
                    FROM '.$this->tableReference($forSaleTable, $forSaleAlias).'
                    WHERE '.$this->forSaleMatchRaw($busReference, $forSaleReference).'
                )
                THEN 1
                ELSE 0
            END
        )
    ';
    }

    private function countStatusNotForSaleSql(
        string $status,
        string $busReference,
        ?string $forSaleAlias = null
    ): string {
        $forSaleTable = $this->forSaleTable();
        $forSaleReference = $forSaleAlias ?: $forSaleTable;

        return '
        SUM(
            CASE
                WHEN '.$this->qualifiedColumn($busReference, 'operational_status').' = '.$this->quote($status).'
                AND NOT EXISTS (
                    SELECT 1
                    FROM '.$this->tableReference($forSaleTable, $forSaleAlias).'
                    WHERE '.$this->forSaleMatchRaw($busReference, $forSaleReference).'
                )
                THEN 1
                ELSE 0
            END
        )
    ';
    }

    public function createBus(array $data): Bus
    {
        return DB::transaction(function () use ($data): Bus {
            $data = $this->normalizeBusData($data);
            $data['status_updated_at'] = now();

            $bus = Bus::query()->create($data);

            $this->busForSaleSyncService->syncFromBus($bus);

            return $bus->fresh(['currentForSaleRecord']);
        });
    }

    public function updateBus(Bus $bus, array $data): Bus
    {
        return DB::transaction(function () use ($bus, $data): Bus {
            $data = $this->normalizeBusData($data);

            $bus->fill($data);

            if ($bus->isDirty([
                'bus_no',
                'plate_no',
                'company',
                'garage',
                'operational_status',
                'sale_status',
                'monitoring_remarks',
            ])) {
                $bus->status_updated_at = now();
            }

            $bus->save();

            $this->busForSaleSyncService->syncFromBus($bus);

            return $bus->fresh(['currentForSaleRecord']);
        });
    }

    private function busTable(): string
    {
        return (new Bus)->getTable();
    }

    private function forSaleTable(): string
    {
        return (new BusForSaleRecord)->getTable();
    }

    private function quote(string $value): string
    {
        return DB::getPdo()->quote($value);
    }

    private function qualifiedColumn(string $reference, string $column): string
    {
        return $this->quotedIdentifier($reference).'.'.$this->quotedIdentifier($column);
    }

    private function tableReference(string $table, ?string $alias = null): string
    {
        $reference = $this->quotedIdentifier($table);

        if ($alias === null || trim($alias) === '') {
            return $reference;
        }

        return $reference.' as '.$this->quotedIdentifier($alias);
    }

    private function quotedIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }
}
