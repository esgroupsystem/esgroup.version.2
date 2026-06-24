<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\StoreBusRequest;
use App\Http\Requests\Fleet\UpdateBusRequest;
use App\Models\Bus;
use App\Services\Fleet\BusService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BusController extends Controller
{
    public function __construct(
        private readonly BusService $busService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only([
            'search',
            'garage',
            'company',
            'operational_status',
            'sale_status',
        ]);

        return view(
            'fleet.buses.index',
            $this->busService->getMonitoringDashboard($filters)
        );
    }

    public function analytics(Request $request): RedirectResponse
    {
        return redirect()->route('fleet.buses.index', $request->query());
    }

    public function create(): View
    {
        return view('fleet.buses.create', [
            'operationalStatusOptions' => Bus::operationalStatusOptions(),
            'saleStatusOptions' => Bus::saleStatusOptions(),
        ]);
    }

    public function store(StoreBusRequest $request): RedirectResponse
    {
        $bus = $this->busService->createBus(
            data: $request->validated()
        );

        return redirect()
            ->route('fleet.buses.index')
            ->with('success', "Bus {$bus->bus_no} added successfully.");
    }

    public function edit(Request $request, Bus $bus): View
    {
        return view('fleet.buses.edit', [
            'bus' => $bus,
            'operationalStatusOptions' => Bus::operationalStatusOptions(),
            'saleStatusOptions' => Bus::saleStatusOptions(),
        ]);
    }

    public function update(UpdateBusRequest $request, Bus $bus): RedirectResponse
    {
        $this->busService->updateBus(
            bus: $bus,
            data: $request->validated()
        );

        return redirect()
            ->route('fleet.buses.index', $request->query())
            ->with('success', "Bus {$bus->bus_no} updated successfully.");
    }
}
