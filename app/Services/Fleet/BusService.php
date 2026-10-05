<?php

declare(strict_types=1);

namespace App\Services\Fleet;

use App\Models\Bus;
use App\Repositories\Contracts\Fleet\BusForSaleRecordRepositoryInterface;
use App\Repositories\Contracts\Fleet\BusRepositoryInterface;
use App\Support\Fleet\FleetValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Fleet → Bus Analytics: unit counts (active, breakdowns, on hold, for sale) overall, per garage and
 * per company, the for-sale summary per company, and bus create / edit (synced to for-sale records).
 * "Active" = not for sale and not in a breakdown, on hold or open maintenance job order.
 */
final class BusService
{
    /** operational_status values that mean "running" (old rows used free text). */
    public const ACTIVE_STATUS_VALUES = [
        Bus::STATUS_ACTIVE, 'Active', 'ACTIVE', 'active', 'Running', 'RUNNING', 'running',
        'Running Condition', 'RUNNING CONDITION', 'running_condition',
    ];

    public function __construct(
        private readonly BusRepositoryInterface $buses,
        private readonly BusForSaleRecordRepositoryInterface $records,
        private readonly BusForSaleSyncService $sync,
    ) {}

    /**
     * @param  array<string, mixed>  $filters  search, garage, company, operational_status, sale_status
     * @return array{filters: array<string, mixed>, garages: Collection<int, string>, companies: Collection<int, string>, garage_summary: Collection<string, array<string, int>>, company_summary: Collection<string, array<string, int>>, for_sale_summary: array<string, mixed>, totals: array<string, int>, filtered_count: int}
     */
    public function getMonitoringDashboard(array $filters = []): array
    {
        $filters = collect($filters)->map(fn (mixed $value): mixed => is_string($value) ? trim($value) : $value)->all();

        return [
            'filters' => $filters,
            'garages' => $this->buses->garages(),
            'companies' => $this->buses->companies(),
            'garage_summary' => $this->summaryBy('garage'),
            'company_summary' => $this->summaryBy('company'),
            'for_sale_summary' => $this->forSaleSummary(),
            'totals' => $this->totals(),
            'filtered_count' => $this->buses->countFiltered($filters),
        ];
    }

    /** @return array{garages: Collection<int, string>, companies: Collection<int, string>} suggestions for the bus form */
    public function formOptions(): array
    {
        return ['garages' => $this->buses->garages(), 'companies' => $this->buses->companies()];
    }

    /** @param array<string, mixed> $data validated StoreBusRequest */
    public function createBus(array $data): Bus
    {
        return DB::transaction(function () use ($data): Bus {
            $bus = $this->buses->create([...$this->normalize($data), 'status_updated_at' => now()]);
            $this->sync->syncFromBus($bus);

            return $bus->fresh(['currentForSaleRecord']);
        });
    }

    /** @param array<string, mixed> $data validated UpdateBusRequest */
    public function updateBus(Bus $bus, array $data): Bus
    {
        return DB::transaction(function () use ($bus, $data): Bus {
            $bus->fill($this->normalize($data));
            if ($bus->isDirty(['bus_no', 'plate_no', 'company', 'garage', 'operational_status', 'sale_status', 'monitoring_remarks'])) {
                $bus->status_updated_at = now();
            }
            $this->buses->save($bus);
            $this->sync->syncFromBus($bus);

            return $bus->fresh(['currentForSaleRecord']);
        });
    }

    /** @return Collection<string, array<string, int>> */
    private function summaryBy(string $column): Collection
    {
        return $this->buses->summaryBy($column)->mapWithKeys(function (object $row): array {
            $notForSale = (int) $row->not_for_sale;
            $mechanical = (int) $row->mechanical_breakdown;
            $accident = (int) $row->accident_related;
            $onHold = (int) $row->on_hold;
            $active = max($notForSale - $mechanical - $accident - $onHold, 0);

            return [$row->group_name => [
                'active' => $active,
                'active_not_for_sale' => $active,
                'mechanical_breakdown' => $mechanical,
                'accident_related' => $accident,
                'on_hold' => $onHold,
                'for_sale' => (int) $row->for_sale,
                'not_for_sale' => $notForSale,
                'total_units' => (int) $row->total_units,
                'total' => $notForSale,
            ]];
        });
    }

    /** @return array<string, int> */
    private function totals(): array
    {
        $counts = $this->buses->counts(self::ACTIVE_STATUS_VALUES);
        $active = max($counts['not_for_sale'] - $counts['mechanical_breakdown'] - $counts['accident_related'] - $counts['on_hold'], 0);

        return [...$counts, 'active' => $active, 'active_not_for_sale' => $active];
    }

    /** @return array<string, mixed> per company: breakdown kinds, running condition and total for sale */
    private function forSaleSummary(): array
    {
        $summary = [];

        foreach ($this->records->countByCompanyAndStatus() as $row) {
            $company = (string) $row->company_name;
            $summary[$company] ??= ['mechanical_breakdown' => 0, 'accident_related' => 0, 'on_hold' => 0, 'breakdown_total' => 0, 'running_condition' => 0, 'total_for_sale' => 0];

            $key = match (self::normalizeStatus((string) $row->status)) {
                Bus::STATUS_MECHANICAL_BREAKDOWN => 'mechanical_breakdown',
                Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN => 'accident_related',
                Bus::STATUS_ON_HOLD_PLATE_REGISTRATION => 'on_hold',
                Bus::STATUS_ACTIVE => 'running_condition',
                default => null,
            };
            if ($key !== null) {
                $summary[$company][$key] += (int) $row->total;
            }
        }

        foreach ($summary as $company => $data) {
            $summary[$company]['breakdown_total'] = $data['mechanical_breakdown'] + $data['accident_related'] + $data['on_hold'];
            $summary[$company]['total_for_sale'] = $summary[$company]['breakdown_total'] + $data['running_condition'];
        }

        $total = fn (string $key): int => array_sum(array_column($summary, $key));

        return [
            'rows' => $summary,
            'mechanical_breakdown_total' => $total('mechanical_breakdown'),
            'accident_related_total' => $total('accident_related'),
            'on_hold_total' => $total('on_hold'),
            'breakdown_total' => $total('breakdown_total'),
            'running_condition_total' => $total('running_condition'),
            'total_for_sale' => $total('total_for_sale'),
        ];
    }

    /** Old free-text statuses ("Running", "Accident", "On Hold") mapped to the Bus::STATUS_* values. */
    private static function normalizeStatus(string $status): string
    {
        $slug = str_replace(' ', '_', (string) preg_replace('/\s+/', ' ', str_replace(['-', '/', '.'], ' ', strtolower(trim($status)))));

        return match ($slug) {
            'active', 'running', 'running_condition' => Bus::STATUS_ACTIVE,
            'mechanical_breakdown', 'mechanical' => Bus::STATUS_MECHANICAL_BREAKDOWN,
            'accident_related_breakdown', 'accident_breakdown', 'accident_related', 'accident' => Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN,
            'on_hold_plate_registration', 'on_hold_due_to_plate_reg', 'on_hold_due_to_plate_registration', 'on_hold', 'plate_registration' => Bus::STATUS_ON_HOLD_PLATE_REGISTRATION,
            default => $slug,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        return [
            'bus_no' => FleetValue::upper($data['bus_no'] ?? null),
            'plate_no' => FleetValue::upper($data['plate_no'] ?? null),
            'company' => FleetValue::upper($data['company'] ?? null),
            'garage' => FleetValue::upper($data['garage'] ?? null),
            'chassis_number' => FleetValue::upper($data['chassis_number'] ?? null),
            'engine_number' => FleetValue::upper($data['engine_number'] ?? null),
            'case_number' => FleetValue::upper($data['case_number'] ?? null),
            'operational_status' => $data['operational_status'] ?? Bus::STATUS_ACTIVE,
            'sale_status' => $data['sale_status'] ?? Bus::SALE_NOT_FOR_SALE,
            'monitoring_remarks' => FleetValue::text($data['monitoring_remarks'] ?? null),
        ];
    }
}
