<?php

declare(strict_types=1);

namespace App\Repositories\Fleet;

use App\Enums\JobOrderStatus;
use App\Models\Bus;
use App\Repositories\Contracts\Fleet\BusRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class BusRepository implements BusRepositoryInterface
{
    /** Open maintenance job order statuses: the bus counts as a mechanical breakdown. */
    private const OPEN_JOB_ORDER_STATUSES = [JobOrderStatus::Standby, JobOrderStatus::WaitingParts, JobOrderStatus::OnGoingRepair];

    public function options(): Collection
    {
        return Bus::query()->select('id', 'bus_no', 'plate_no', 'company', 'garage')->orderBy('bus_no')->get();
    }

    public function optionsWithLastOdometer(): Collection
    {
        return Bus::query()
            ->with('latestJobOrderMaintenanceWithOdometer')
            ->select(['id', 'bus_no', 'plate_no', 'company', 'garage', 'operational_status', 'sale_status'])
            ->orderBy('bus_no')
            ->get();
    }

    public function pickerOptions(): Collection
    {
        return Bus::query()
            ->orderBy('bus_no')
            ->orderBy('plate_no')
            ->orderBy('company')
            ->orderBy('garage')
            ->get(['id', 'bus_no', 'plate_no', 'company', 'garage']);
    }

    public function find(int $id): ?Bus
    {
        return Bus::query()->find($id);
    }

    public function findForUpdate(int $id): Bus
    {
        return Bus::query()->lockForUpdate()->findOrFail($id);
    }

    public function findUnique(?string $busNo, ?string $plateNo, ?string $company, ?string $garage): ?Bus
    {
        $query = Bus::query()
            ->where('bus_no', $busNo)
            ->when($plateNo, fn (Builder $query) => $query->where('plate_no', $plateNo))
            ->when($company, fn (Builder $query) => $query->where('company', $company))
            ->when($garage, fn (Builder $query) => $query->where('garage', $garage));

        return $query->count() === 1 ? $query->first() : null;
    }

    public function countFiltered(array $filters): int
    {
        $value = fn (string $key): string => trim((string) ($filters[$key] ?? ''));

        return Bus::query()
            ->when($value('search') !== '', function (Builder $query) use ($value): void {
                $search = strtoupper($value('search'));
                $query->where(fn (Builder $inner) => $inner
                    ->where('bus_no', 'like', "%{$search}%")
                    ->orWhere('plate_no', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('garage', 'like', "%{$search}%")
                    ->orWhere('chassis_number', 'like', "%{$search}%")
                    ->orWhere('engine_number', 'like', "%{$search}%")
                    ->orWhere('case_number', 'like', "%{$search}%"));
            })
            ->when($value('garage') !== '', fn (Builder $query) => $query->where('garage', strtoupper($value('garage'))))
            ->when($value('company') !== '', fn (Builder $query) => $query->where('company', strtoupper($value('company'))))
            ->when($value('operational_status') !== '', fn (Builder $query) => $query->where('operational_status', $value('operational_status')))
            ->when($value('sale_status') !== '', function (Builder $query) use ($value): void {
                $saleStatus = $value('sale_status');

                match (true) {
                    $saleStatus === Bus::SALE_FOR_SALE => $this->forSale($query),
                    $saleStatus === Bus::SALE_NOT_FOR_SALE || strtolower(str_replace(' ', '_', $saleStatus)) === 'not_for_sale' => $this->notForSale($query),
                    default => $query->where('sale_status', $saleStatus),
                };
            })
            ->count();
    }

    public function folderRows(array $filters): Collection
    {
        return Bus::query()
            ->when($filters['search'] !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('bus_no', 'like', "%{$filters['search']}%")
                ->orWhere('plate_no', 'like', "%{$filters['search']}%")
                ->orWhere('chassis_number', 'like', "%{$filters['search']}%")
                ->orWhere('engine_number', 'like', "%{$filters['search']}%")
                ->orWhere('case_number', 'like', "%{$filters['search']}%")))
            ->when($filters['company'] !== '', fn (Builder $query) => $query->where('company', $filters['company']))
            ->when($filters['operational_status'] !== '', fn (Builder $query) => $query->where('operational_status', $filters['operational_status']))
            ->orderByRaw("CASE WHEN UPPER(garage) = 'MIRASOL' THEN 0 WHEN UPPER(garage) = 'BALINTAWAK' THEN 1 ELSE 2 END")
            ->orderBy('garage')
            ->orderBy('company')
            ->orderByRaw('CAST(bus_no AS UNSIGNED), bus_no')
            ->get();
    }

    public function garages(): Collection
    {
        return $this->distinct('garage');
    }

    public function companies(): Collection
    {
        return $this->distinct('company');
    }

    public function summaryBy(string $column): Collection
    {
        if (! in_array($column, ['garage', 'company'], true)) {
            throw new InvalidArgumentException('Invalid fleet summary column.');
        }

        $forSaleRow = '(SELECT 1 FROM `bus_for_sale_records` as `fs` WHERE `fs`.`bus_id` IS NOT NULL AND `fs`.`bus_id` = `b`.`id`)';
        $notForSale = 'NOT EXISTS '.$forSaleRow;
        $openJobOrder = 'EXISTS (SELECT 1 FROM `job_orders_maintenance` as `jom` WHERE `jom`.`bus_id` = `b`.`id` AND `jom`.`deleted_at` IS NULL AND `jom`.`status` IN ('
            .implode(', ', array_fill(0, count(self::OPEN_JOB_ORDER_STATUSES), '?')).'))';
        $openStatuses = array_map(fn (JobOrderStatus $status): string => $status->value, self::OPEN_JOB_ORDER_STATUSES);

        return DB::table('buses as b')
            ->selectRaw("COALESCE(NULLIF(`b`.`{$column}`, ''), 'UNKNOWN') as group_name")
            ->selectRaw('COUNT(*) as total_units')
            ->selectRaw("SUM(CASE WHEN {$notForSale} THEN 1 ELSE 0 END) as not_for_sale")
            ->selectRaw("SUM(CASE WHEN {$notForSale} AND {$openJobOrder} THEN 1 ELSE 0 END) as mechanical_breakdown", $openStatuses)
            ->selectRaw("SUM(CASE WHEN `b`.`operational_status` = ? AND {$notForSale} THEN 1 ELSE 0 END) as accident_related", [Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN])
            ->selectRaw("SUM(CASE WHEN `b`.`operational_status` = ? AND {$notForSale} THEN 1 ELSE 0 END) as on_hold", [Bus::STATUS_ON_HOLD_PLATE_REGISTRATION])
            ->selectRaw("SUM(CASE WHEN EXISTS {$forSaleRow} THEN 1 ELSE 0 END) as for_sale")
            ->groupBy('group_name')
            ->orderBy('group_name')
            ->get();
    }

    public function counts(array $activeStatuses): array
    {
        $openStatuses = array_map(fn (JobOrderStatus $status): string => $status->value, self::OPEN_JOB_ORDER_STATUSES);

        return [
            'total_units' => Bus::query()->count(),
            'for_sale' => $this->forSale(Bus::query())->count(),
            'not_for_sale' => $this->notForSale(Bus::query())->count(),
            'mechanical_breakdown' => $this->notForSale(Bus::query()->whereHas('jobOrderMaintenances', fn (Builder $query) => $query->whereIn('status', $openStatuses)))->count(),
            'accident_related' => $this->notForSale(Bus::query()->where('operational_status', Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN))->count(),
            'on_hold' => $this->notForSale(Bus::query()->where('operational_status', Bus::STATUS_ON_HOLD_PLATE_REGISTRATION))->count(),
            'active_for_sale' => $this->forSale(Bus::query()->whereIn('operational_status', $activeStatuses))->count(),
        ];
    }

    public function create(array $attributes): Bus
    {
        return Bus::query()->create($attributes);
    }

    public function save(Bus $bus): void
    {
        $bus->save();
    }

    /** @return Collection<int, string> */
    private function distinct(string $column): Collection
    {
        return Bus::query()->whereNotNull($column)->where($column, '!=', '')->distinct()->orderBy($column)->pluck($column);
    }

    /**
     * @param  Builder<Bus>  $query
     * @return Builder<Bus>
     */
    private function forSale(Builder $query): Builder
    {
        return $query->whereExists(fn (QueryBuilder $sub) => $this->forSaleRow($sub));
    }

    /**
     * @param  Builder<Bus>  $query
     * @return Builder<Bus>
     */
    private function notForSale(Builder $query): Builder
    {
        return $query->whereNotExists(fn (QueryBuilder $sub) => $this->forSaleRow($sub));
    }

    private function forSaleRow(QueryBuilder $sub): void
    {
        $sub->selectRaw('1')->from('bus_for_sale_records')->whereNotNull('bus_for_sale_records.bus_id')->whereColumn('bus_for_sale_records.bus_id', 'buses.id');
    }
}
