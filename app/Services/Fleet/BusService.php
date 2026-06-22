<?php

namespace App\Services\Fleet;

use App\Models\Bus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BusService
{
    /**
     * Main dashboard data.
     */
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
            'totals' => $this->overallTotals(),

            'filtered_count' => $filteredBuses->count(),
        ];
    }

    /**
     * Backward compatibility if old view still calls this.
     */
    public function getGroupedByGarageAndCompany(): array
    {
        $dashboard = $this->getMonitoringDashboard();

        return [
            'mirasol' => $dashboard['grouped_buses']->get('MIRASOL', collect()),
            'balintawak' => $dashboard['grouped_buses']->get('BALINTAWAK', collect()),
        ];
    }

    /**
     * Backward compatibility if old analytics view still calls this.
     */
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

    private function summaryBy(string $column): Collection
    {
        $rows = Bus::query()
            ->selectRaw("COALESCE(NULLIF({$column}, ''), 'UNKNOWN') as group_name")
            ->selectRaw('operational_status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy($column, 'operational_status')
            ->orderBy($column)
            ->get();

        return $rows
            ->groupBy('group_name')
            ->map(function (Collection $items): array {
                $summary = $this->emptyOperationalSummary();

                foreach ($items as $item) {
                    $key = $this->summaryKeyFromOperationalStatus((string) $item->operational_status);

                    if (array_key_exists($key, $summary)) {
                        $summary[$key] += (int) $item->total;
                    }
                }

                $summary['total'] =
                    $summary['active'] +
                    $summary['mechanical_breakdown'] +
                    $summary['accident_related'] +
                    $summary['on_hold'];

                return $summary;
            });
    }

    private function forSaleSummary(): array
    {
        $companies = $this->getCompanies();

        $summary = $companies->mapWithKeys(function (string $company): array {
            return [
                $company => [
                    'mechanical_breakdown' => 0,
                    'accident_related' => 0,
                    'on_hold' => 0,
                    'breakdown_total' => 0,
                    'running_condition' => 0,
                    'total_for_sale' => 0,
                ],
            ];
        });

        $rows = Bus::query()
            ->where('sale_status', Bus::SALE_FOR_SALE)
            ->selectRaw("COALESCE(NULLIF(company, ''), 'UNKNOWN') as company_name")
            ->selectRaw('operational_status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('company', 'operational_status')
            ->orderBy('company')
            ->get();

        foreach ($rows as $row) {
            $company = (string) $row->company_name;

            if (! $summary->has($company)) {
                $summary[$company] = [
                    'mechanical_breakdown' => 0,
                    'accident_related' => 0,
                    'on_hold' => 0,
                    'breakdown_total' => 0,
                    'running_condition' => 0,
                    'total_for_sale' => 0,
                ];
            }

            $count = (int) $row->total;

            match ((string) $row->operational_status) {
                Bus::STATUS_ACTIVE => $summary[$company]['running_condition'] += $count,
                Bus::STATUS_MECHANICAL_BREAKDOWN => $summary[$company]['mechanical_breakdown'] += $count,
                Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN => $summary[$company]['accident_related'] += $count,
                Bus::STATUS_ON_HOLD_PLATE_REGISTRATION => $summary[$company]['on_hold'] += $count,
                default => null,
            };
        }

        $summary = $summary->map(function (array $row): array {
            $row['breakdown_total'] =
                $row['mechanical_breakdown'] +
                $row['accident_related'] +
                $row['on_hold'];

            $row['total_for_sale'] =
                $row['breakdown_total'] +
                $row['running_condition'];

            return $row;
        });

        return [
            'rows' => $summary,
            'mechanical_breakdown_total' => $summary->sum('mechanical_breakdown'),
            'accident_related_total' => $summary->sum('accident_related'),
            'on_hold_total' => $summary->sum('on_hold'),
            'breakdown_total' => $summary->sum('breakdown_total'),
            'running_condition_total' => $summary->sum('running_condition'),
            'total_for_sale' => $summary->sum('total_for_sale'),
        ];
    }

    private function overallTotals(): array
    {
        $statusCounts = Bus::query()
            ->selectRaw('operational_status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('operational_status')
            ->pluck('total', 'operational_status');

        $totalUnits = Bus::query()->count();
        $forSale = Bus::query()->where('sale_status', Bus::SALE_FOR_SALE)->count();

        return [
            'total_units' => $totalUnits,
            'active' => (int) ($statusCounts[Bus::STATUS_ACTIVE] ?? 0),
            'mechanical_breakdown' => (int) ($statusCounts[Bus::STATUS_MECHANICAL_BREAKDOWN] ?? 0),
            'accident_related' => (int) ($statusCounts[Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN] ?? 0),
            'on_hold' => (int) ($statusCounts[Bus::STATUS_ON_HOLD_PLATE_REGISTRATION] ?? 0),
            'for_sale' => $forSale,
            'not_for_sale' => max($totalUnits - $forSale, 0),
        ];
    }

    private function emptyOperationalSummary(): array
    {
        return [
            'active' => 0,
            'mechanical_breakdown' => 0,
            'accident_related' => 0,
            'on_hold' => 0,
            'total' => 0,
        ];
    }

    private function summaryKeyFromOperationalStatus(string $status): string
    {
        return match ($status) {
            Bus::STATUS_ACTIVE => 'active',
            Bus::STATUS_MECHANICAL_BREAKDOWN => 'mechanical_breakdown',
            Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN => 'accident_related',
            Bus::STATUS_ON_HOLD_PLATE_REGISTRATION => 'on_hold',
            default => 'active',
        };
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
