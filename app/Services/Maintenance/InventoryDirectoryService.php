<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Models\BusDetail;
use App\Models\Location;
use App\Models\PartsOut;
use App\Models\Product;
use App\Models\Receiving;
use App\Models\StockTransfer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class InventoryDirectoryService
{
    public function partsOuts(string $search, ?int $locationId): LengthAwarePaginator
    {
        return PartsOut::query()
            ->with(['vehicle', 'creator', 'location'])
            ->withCount('items')
            ->when($locationId, fn (Builder $query) => $query->where('location_id', $locationId))
            ->when(trim($search) !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('parts_out_number', 'like', "%{$search}%")
                        ->orWhere('mechanic_name', 'like', "%{$search}%")
                        ->orWhere('requested_by', 'like', "%{$search}%")
                        ->orWhere('job_order_no', 'like', "%{$search}%")
                        ->orWhere('issued_date', 'like', "%{$search}%")
                        ->orWhereHas('location', fn (Builder $locationQuery) => $locationQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('vehicle', function (Builder $vehicleQuery) use ($search): void {
                            $vehicleQuery->where('plate_number', 'like', "%{$search}%")
                                ->orWhere('body_number', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('garage', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function receivings(string $search, ?int $locationId): LengthAwarePaginator
    {
        return Receiving::query()
            ->with(['location', 'receiver'])
            ->withCount('items')
            ->when($locationId, fn (Builder $query) => $query->where('location_id', $locationId))
            ->when(trim($search) !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('receiving_number', 'like', "%{$search}%")
                        ->orWhere('delivered_by', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhereHas('location', fn (Builder $locationQuery) => $locationQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function stockTransfers(string $search): LengthAwarePaginator
    {
        return StockTransfer::query()
            ->with(['fromLocation', 'toLocation', 'creator'])
            ->withCount('items')
            ->when(trim($search) !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('transfer_number', 'like', "%{$search}%")
                        ->orWhere('requested_by', 'like', "%{$search}%")
                        ->orWhere('received_by', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    /** @return Collection<int, Location> */
    public function activeLocations(?int $locationId = null): Collection
    {
        return Location::query()
            ->where('is_active', true)
            ->when($locationId, fn (Builder $query) => $query->whereKey($locationId))
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, BusDetail> */
    public function vehicles(): Collection
    {
        return BusDetail::query()->orderBy('plate_number')->get();
    }

    /** @return Collection<int, Product> */
    public function products(): Collection
    {
        return Product::query()
            ->with('category')
            ->select(['id', 'category_id', 'product_name', 'supplier_name', 'unit', 'part_number', 'details', 'stock_qty'])
            ->orderBy('product_name')
            ->get();
    }
}
