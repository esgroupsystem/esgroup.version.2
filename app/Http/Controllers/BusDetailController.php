<?php

namespace App\Http\Controllers;

use App\Models\BusDetail;
use App\Models\PartsOut;
use App\Models\PartsOutItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusDetailController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $buses = BusDetail::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('plate_number', 'like', "%{$search}%")
                        ->orWhere('body_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('garage', 'like', "%{$search}%");
                });
            })
            ->orderBy('plate_number')
            ->paginate(10)
            ->withQueryString();

        return view('maintenance.bus.index', compact('buses', 'search'));
    }

    public function show(Request $request, BusDetail $busDetail)
    {
        $search = trim((string) $request->get('search', ''));

        /*
         * Important:
         * Only posted PartsOut records should appear in Vehicle History.
         * Rolled back records should not be shown or counted.
         */
        $partsOuts = PartsOut::with([
            'creator',
            'items.product',
        ])
            ->where('vehicle_id', $busDetail->id)
            ->where('status', 'posted')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('parts_out_number', 'like', "%{$search}%")
                        ->orWhere('mechanic_name', 'like', "%{$search}%")
                        ->orWhere('requested_by', 'like', "%{$search}%")
                        ->orWhere('job_order_no', 'like', "%{$search}%")
                        ->orWhere('odometer', 'like', "%{$search}%")
                        ->orWhere('purpose', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhereHas('items.product', function ($productQuery) use ($search) {
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

        $totalTransactions = PartsOut::query()
            ->where('vehicle_id', $busDetail->id)
            ->where('status', 'posted')
            ->count();

        $totalPartsUsed = PartsOutItem::query()
            ->whereHas('partsOut', function ($query) use ($busDetail) {
                $query->where('vehicle_id', $busDetail->id)
                    ->where('status', 'posted');
            })
            ->sum('qty_used');

        $latestMaintenanceDate = PartsOut::query()
            ->where('vehicle_id', $busDetail->id)
            ->where('status', 'posted')
            ->max('issued_date');

        $mostUsedPart = PartsOutItem::query()
            ->select(
                'product_id',
                DB::raw('SUM(qty_used) as total_used')
            )
            ->whereHas('partsOut', function ($query) use ($busDetail) {
                $query->where('vehicle_id', $busDetail->id)
                    ->where('status', 'posted');
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_used')
            ->first();

        return view('maintenance.bus.show', compact(
            'busDetail',
            'partsOuts',
            'search',
            'totalTransactions',
            'totalPartsUsed',
            'latestMaintenanceDate',
            'mostUsedPart'
        ));
    }
}
