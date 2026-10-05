<?php

declare(strict_types=1);

namespace App\Repositories\Maintenance;

use App\Enums\InventoryTransactionStatus;
use App\Models\BusDetail;
use App\Models\PartsOut;
use App\Models\PartsOutItem;
use App\Repositories\Contracts\Maintenance\PartsOutRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class PartsOutRepository implements PartsOutRepositoryInterface
{
    public function paginate(string $search, ?int $locationId, int $perPage = 10): LengthAwarePaginator
    {
        return PartsOut::query()
            ->with(['vehicle', 'creator', 'location'])
            ->withCount('items')
            ->when($locationId !== null, fn (Builder $query) => $query->where('location_id', $locationId))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('parts_out_number', 'like', "%{$search}%")
                ->orWhere('mechanic_name', 'like', "%{$search}%")
                ->orWhere('requested_by', 'like', "%{$search}%")
                ->orWhere('job_order_no', 'like', "%{$search}%")
                ->orWhere('issued_date', 'like', "%{$search}%")
                ->orWhereHas('location', fn (Builder $location) => $location->where('name', 'like', "%{$search}%"))
                ->orWhereHas('vehicle', fn (Builder $vehicle) => $vehicle
                    ->where('plate_number', 'like', "%{$search}%")
                    ->orWhere('body_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('garage', 'like', "%{$search}%"))))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function loadForShow(PartsOut $partsOut): PartsOut
    {
        return $partsOut->load(['vehicle', 'creator', 'location', 'items.product']);
    }

    public function findForUpdate(int $id): PartsOut
    {
        return PartsOut::query()->with('items.product')->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function create(array $attributes): PartsOut
    {
        return PartsOut::query()->create($attributes);
    }

    public function update(PartsOut $partsOut, array $attributes): void
    {
        $partsOut->update($attributes);
    }

    public function delete(PartsOut $partsOut): void
    {
        $partsOut->delete();
    }

    public function addItem(PartsOut $partsOut, array $attributes): PartsOutItem
    {
        return PartsOutItem::query()->create(['parts_out_id' => $partsOut->id] + $attributes);
    }

    public function paginatePostedForVehicle(BusDetail $vehicle, string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->posted($vehicle)
            ->with(['creator', 'items.product'])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('parts_out_number', 'like', "%{$search}%")
                ->orWhere('mechanic_name', 'like', "%{$search}%")
                ->orWhere('requested_by', 'like', "%{$search}%")
                ->orWhere('job_order_no', 'like', "%{$search}%")
                ->orWhere('odometer', 'like', "%{$search}%")
                ->orWhere('purpose', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%")
                ->orWhereHas('items.product', fn (Builder $product) => $product
                    ->where('product_name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%"))))
            ->orderByDesc('issued_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function vehicleTotals(BusDetail $vehicle): array
    {
        $itemsOfVehicle = fn () => PartsOutItem::query()->whereHas('partsOut', fn (Builder $query) => $query
            ->where('vehicle_id', $vehicle->id)
            ->where('status', InventoryTransactionStatus::Posted->value));

        $latest = $this->posted($vehicle)->max('issued_date');

        return [
            'transactions' => $this->posted($vehicle)->count(),
            'parts_used' => (int) $itemsOfVehicle()->sum('qty_used'),
            'latest' => $latest === null ? null : (string) $latest,
            'most_used' => $itemsOfVehicle()
                ->select('product_id', DB::raw('SUM(qty_used) as total_used'))
                ->with('product')
                ->groupBy('product_id')
                ->orderByDesc('total_used')
                ->first(),
        ];
    }

    /** @return Builder<PartsOut> */
    private function posted(BusDetail $vehicle): Builder
    {
        return PartsOut::query()->where('vehicle_id', $vehicle->id)->where('status', InventoryTransactionStatus::Posted->value);
    }
}
