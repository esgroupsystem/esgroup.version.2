<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Enums\InventoryTransactionStatus;
use App\Models\BusDetail;
use App\Models\PartsOut;
use App\Models\PartsOutItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class VehicleHistoryService
{
    public function buses(string $search): LengthAwarePaginator
    {
        return BusDetail::query()
            ->when(trim($search) !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('plate_number', 'like', "%{$search}%")
                        ->orWhere('body_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('garage', 'like', "%{$search}%");
                });
            })
            ->orderBy('plate_number')
            ->paginate(10)
            ->withQueryString();
    }

    /** @return array<string, mixed> */
    public function history(BusDetail $bus, string $search): array
    {
        $posted = InventoryTransactionStatus::Posted->value;
        $partsOuts = PartsOut::query()
            ->with(['creator', 'items.product'])
            ->where('vehicle_id', $bus->id)
            ->where('status', $posted)
            ->when(trim($search) !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('parts_out_number', 'like', "%{$search}%")
                        ->orWhere('mechanic_name', 'like', "%{$search}%")
                        ->orWhere('requested_by', 'like', "%{$search}%")
                        ->orWhere('job_order_no', 'like', "%{$search}%")
                        ->orWhere('odometer', 'like', "%{$search}%")
                        ->orWhere('purpose', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhereHas('items.product', function ($productQuery) use ($search): void {
                            $productQuery->where('product_name', 'like', "%{$search}%")
                                ->orWhere('part_number', 'like', "%{$search}%")
                                ->orWhere('supplier_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('issued_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $base = PartsOut::query()->where('vehicle_id', $bus->id)->where('status', $posted);
        $mostUsedPart = PartsOutItem::query()
            ->select('product_id', DB::raw('SUM(qty_used) as total_used'))
            ->whereHas('partsOut', fn ($query) => $query->where('vehicle_id', $bus->id)->where('status', $posted))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_used')
            ->first();

        return [
            'partsOuts' => $partsOuts,
            'totalTransactions' => (clone $base)->count(),
            'totalPartsUsed' => PartsOutItem::query()->whereHas('partsOut', fn ($query) => $query->where('vehicle_id', $bus->id)->where('status', $posted))->sum('qty_used'),
            'latestMaintenanceDate' => (clone $base)->max('issued_date'),
            'mostUsedPart' => $mostUsedPart,
        ];
    }
}
