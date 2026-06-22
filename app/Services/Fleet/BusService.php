<?php

namespace App\Services\Fleet;

use App\Models\Bus;
use App\Models\BusForSaleRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BusService
{
    public function getMonitoringDashboard(array $filters = []): array
    {
        $filteredBuses = $this->filteredBusQuery($filters)
            ->orderBy('garage')
            ->orderBy('company')
            ->orderBy('bus_no')
            ->get();

        return [
            'filters' => $filters,

            'garages' => $this->getGarages(),
            'companies' => $this->getCompanies(),
            'operational_status_options' => Bus::operationalStatusOptions(),
            'sale_status_options' => Bus::saleStatusOptions(),

            'grouped_buses' => $filteredBuses
                ->groupBy('garage')
                ->map(fn (Collection $garageBuses): Collection => $garageBuses->groupBy('company')),

            'garage_summary' => $this->summaryBy('garage'),
            'company_summary' => $this->summaryBy('company'),
            'for_sale_summary' => $this->forSaleSummary(),
            'for_sale_records' => $this->dashboardForSaleRecords($filters),
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
                $query->where('sale_status', trim((string) $filters['sale_status']));
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
                $query->where('status', trim((string) $filters['operational_status']));
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
        $groupExpression = "COALESCE(NULLIF({$column}, ''), 'UNKNOWN')";

        $rows = Bus::query()
            ->selectRaw("{$groupExpression} as group_name")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw($this->countActiveNotForSaleSql().' as active')
            ->selectRaw($this->countStatusSql(Bus::STATUS_MECHANICAL_BREAKDOWN).' as mechanical_breakdown')
            ->selectRaw($this->countStatusSql(Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN).' as accident_related')
            ->selectRaw($this->countStatusSql(Bus::STATUS_ON_HOLD_PLATE_REGISTRATION).' as on_hold')
            ->selectRaw($this->countActiveForSaleSql().' as active_for_sale')
            ->selectRaw($this->countForSaleSql().' as for_sale')
            ->groupBy($column)
            ->orderBy($column)
            ->get();

        return $rows->mapWithKeys(function ($row): array {
            return [
                $row->group_name => [
                    'active' => (int) $row->active,
                    'mechanical_breakdown' => (int) $row->mechanical_breakdown,
                    'accident_related' => (int) $row->accident_related,
                    'on_hold' => (int) $row->on_hold,
                    'active_for_sale' => (int) $row->active_for_sale,
                    'for_sale' => (int) $row->for_sale,
                    'total' => (int) $row->total,
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
            ->get();

        $summary = [];

        foreach ($rows as $row) {
            $company = (string) $row->company_name;
            $count = (int) $row->total;

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

            switch ($row->status) {
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

        $forSale = BusForSaleRecord::query()->count();

        $forSaleBusNumbers = BusForSaleRecord::query()
            ->select('bus_no')
            ->whereNotNull('bus_no')
            ->where('bus_no', '!=', '')
            ->distinct();

        $active = Bus::query()
            ->where('operational_status', Bus::STATUS_ACTIVE)
            ->whereNotIn('bus_no', $forSaleBusNumbers)
            ->count();

        $mechanicalBreakdown = Bus::query()
            ->where('operational_status', Bus::STATUS_MECHANICAL_BREAKDOWN)
            ->count();

        $accidentRelated = Bus::query()
            ->where('operational_status', Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN)
            ->count();

        $onHold = Bus::query()
            ->where('operational_status', Bus::STATUS_ON_HOLD_PLATE_REGISTRATION)
            ->count();

        return [
            'total_units' => $totalUnits,
            'active' => $active,
            'mechanical_breakdown' => $mechanicalBreakdown,
            'accident_related' => $accidentRelated,
            'on_hold' => $onHold,

            // Source of truth: For Sale Unit Monitoring records.
            'for_sale' => $forSale,

            'not_for_sale' => max($totalUnits - $forSale, 0),
        ];
    }

    private function countActiveNotForSaleSql(): string
    {
        return '
            SUM(
                CASE
                    WHEN operational_status = '.DB::getPdo()->quote(Bus::STATUS_ACTIVE).'
                    AND (
                        sale_status IS NULL
                        OR sale_status != '.DB::getPdo()->quote(Bus::SALE_FOR_SALE).'
                    )
                    THEN 1
                    ELSE 0
                END
            )
        ';
    }

    private function countActiveForSaleSql(): string
    {
        return '
            SUM(
                CASE
                    WHEN operational_status = '.DB::getPdo()->quote(Bus::STATUS_ACTIVE).'
                    AND sale_status = '.DB::getPdo()->quote(Bus::SALE_FOR_SALE).'
                    THEN 1
                    ELSE 0
                END
            )
        ';
    }

    private function countForSaleSql(): string
    {
        return '
            SUM(
                CASE
                    WHEN sale_status = '.DB::getPdo()->quote(Bus::SALE_FOR_SALE).'
                    THEN 1
                    ELSE 0
                END
            )
        ';
    }

    private function countStatusSql(string $status): string
    {
        return '
            SUM(
                CASE
                    WHEN operational_status = '.DB::getPdo()->quote($status).'
                    THEN 1
                    ELSE 0
                END
            )
        ';
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

    private function filled(array $filters, string $key): bool
    {
        return isset($filters[$key]) && trim((string) $filters[$key]) !== '';
    }
}
