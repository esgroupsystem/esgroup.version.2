<?php

declare(strict_types=1);

namespace App\Repositories\Fleet;

use App\Models\DieselStock;
use App\Repositories\Contracts\Fleet\DieselStockRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class DieselStockRepository implements DieselStockRepositoryInterface
{
    public function liters(string $type, ?string $from = null, ?string $to = null): float
    {
        return (float) DieselStock::query()
            ->where('type', $type)
            ->when($from !== null && $to !== null, fn (Builder $query) => $query->whereBetween('date', [$from, $to]))
            ->sum('liters');
    }

    public function movements(?int $busId, string $from, string $to): Collection
    {
        return DieselStock::query()
            ->with(['bus:id,garage,name,body_number,plate_number', 'encoder:id,full_name'])
            ->when($busId !== null, fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('bus_detail_id', $busId)
                ->orWhereNull('bus_detail_id')))
            ->whereBetween('date', [$from, $to])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
    }

    public function create(array $attributes): DieselStock
    {
        return DieselStock::query()->create($attributes);
    }

    public function updateOdometerDeduction(int $submissionId, float $liters, string $date): void
    {
        DieselStock::query()
            ->where('reference_no', 'ODO-'.$submissionId)
            ->where('type', 'out')
            ->update(['liters' => $liters, 'date' => $date]);
    }

    public function deleteOdometerDeduction(int $submissionId, ?int $busId): void
    {
        DieselStock::query()
            ->where('reference_no', 'ODO-'.$submissionId)
            ->where('type', 'out')
            ->where('bus_detail_id', $busId)
            ->delete();
    }
}
