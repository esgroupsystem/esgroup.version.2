<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BusDetail;
use App\Models\PartsOut;
use App\Services\Maintenance\VehicleHistoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class BusDetailController extends Controller
{
    public function __construct(private readonly VehicleHistoryService $vehicleHistoryService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        return Inertia::render('fleet/vehicle-history/index', [
            'buses' => $this->vehicleHistoryService->buses($search)->through(fn (BusDetail $bus): array => [
                'id' => $bus->id,
                'plate_number' => $bus->plate_number,
                'body_number' => $bus->body_number,
                'name' => $bus->name,
                'garage' => $bus->garage,
                'status' => $bus->status,
                'show_url' => route('buses.show', $bus->id),
            ]),
            'filters' => ['search' => $search],
            'urls' => ['index' => route('buses.index')],
        ]);
    }

    public function show(Request $request, BusDetail $busDetail): Response
    {
        $search = trim((string) $request->input('search', ''));
        $history = $this->vehicleHistoryService->history($busDetail, $search);

        return Inertia::render('fleet/vehicle-history/show', [
            'bus' => [
                'plate_number' => $busDetail->plate_number,
                'body_number' => $busDetail->body_number,
                'name' => $busDetail->name,
                'garage' => $busDetail->garage,
                'status' => $busDetail->status,
            ],
            'records' => $history['partsOuts']->through(fn (PartsOut $record): array => [
                'id' => $record->id,
                'date' => $record->issued_date ? Carbon::parse($record->issued_date)->format('M d, Y') : 'N/A',
                'number' => $record->parts_out_number ?? 'N/A',
                'mechanic' => $record->mechanic_name ?? 'N/A',
                'requested_by' => $record->requested_by ?? 'N/A',
                'job_order_no' => $record->job_order_no ?? 'N/A',
                'odometer' => $record->odometer ?? 'N/A',
                'purpose' => $record->purpose ?? 'N/A',
                'remarks' => $record->remarks ?? 'N/A',
                'creator' => $record->creator->full_name ?? ($record->creator->name ?? 'N/A'),
                'items' => $record->items->map(fn ($item): array => [
                    'name' => $item->product->product_name ?? 'N/A',
                    'qty' => (int) ($item->qty_used ?? 0),
                    'unit' => $item->product->unit ?? '',
                    'part_number' => $item->product->part_number ?? null,
                    'remarks' => $item->remarks,
                ])->values(),
                'show_url' => route('parts-out.show', $record->id),
            ]),
            'summary' => [
                'transactions' => (int) $history['totalTransactions'],
                'parts_used' => (int) $history['totalPartsUsed'],
                'latest' => $history['latestMaintenanceDate'] ? Carbon::parse($history['latestMaintenanceDate'])->format('F d, Y') : null,
                'most_used' => $history['mostUsedPart']?->product?->product_name,
                'most_used_qty' => $history['mostUsedPart'] ? (int) $history['mostUsedPart']->total_used : null,
            ],
            'filters' => ['search' => $search],
            'urls' => ['self' => route('buses.show', $busDetail->id), 'back' => route('buses.index')],
        ]);
    }
}
