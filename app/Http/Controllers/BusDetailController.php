<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BusDetail;
use App\Services\Maintenance\VehicleHistoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class BusDetailController extends Controller
{
    public function __construct(private readonly VehicleHistoryService $vehicleHistoryService) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        return view('maintenance.bus.index', [
            'buses' => $this->vehicleHistoryService->buses($search),
            'search' => $search,
        ]);
    }

    public function show(Request $request, BusDetail $busDetail): View
    {
        $search = trim((string) $request->input('search', ''));

        return view('maintenance.bus.show', [
            'busDetail' => $busDetail,
            'search' => $search,
            ...$this->vehicleHistoryService->history($busDetail, $search),
        ]);
    }
}
