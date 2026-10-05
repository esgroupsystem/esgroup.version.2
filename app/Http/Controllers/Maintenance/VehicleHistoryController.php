<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Resources\Maintenance\VehicleHistoryRecordResource;
use App\Models\BusDetail;
use App\Models\PartsOut;
use App\Services\Maintenance\VehicleHistoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Maintenance → Vehicle History (buses.*). */
final class VehicleHistoryController extends Controller
{
    public function __construct(private readonly VehicleHistoryService $vehicleHistoryService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        return Inertia::render('fleet/vehicle-history/index', [
            'buses' => $this->vehicleHistoryService->buses($search)->through(fn (BusDetail $bus): array => [
                ...self::vehicle($bus),
                'id' => $bus->id,
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
        $mostUsed = $history['most_used'];

        return Inertia::render('fleet/vehicle-history/show', [
            'bus' => self::vehicle($busDetail),
            'records' => $history['records']->through(fn (PartsOut $record): array => VehicleHistoryRecordResource::make($record)->resolve($request)),
            'summary' => [
                'transactions' => $history['transactions'],
                'parts_used' => $history['parts_used'],
                'latest' => $history['latest'] ? Carbon::parse($history['latest'])->format('F d, Y') : null,
                'most_used' => $mostUsed?->product?->product_name,
                'most_used_qty' => $mostUsed ? (int) $mostUsed->getAttribute('total_used') : null,
            ],
            'filters' => ['search' => $search],
            'urls' => ['self' => route('buses.show', $busDetail->id), 'back' => route('buses.index')],
        ]);
    }

    /** @return array{plate_number: ?string, body_number: ?string, name: ?string, garage: ?string, status: mixed} */
    private static function vehicle(BusDetail $bus): array
    {
        return [
            'plate_number' => $bus->plate_number,
            'body_number' => $bus->body_number,
            'name' => $bus->name,
            'garage' => $bus->garage,
            'status' => $bus->status,
        ];
    }
}
