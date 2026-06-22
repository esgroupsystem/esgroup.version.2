<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Services\Fleet\BusService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BusController extends Controller
{
    public function __construct(
        private readonly BusService $busService
    ) {}

    /**
     * Combined monitoring and analytics dashboard.
     */
    public function index(Request $request): View
    {
        $filters = $request->only([
            'search',
            'garage',
            'company',
            'operational_status',
            'sale_status',
        ]);

        return view('fleet.buses.index', $this->busService->getMonitoringDashboard($filters));
    }

    /**
     * Keep old analytics route working.
     * It now opens the same combined monitoring dashboard.
     */
    public function analytics(Request $request): RedirectResponse
    {
        return redirect()->route('fleet.buses.index', $request->query());
    }
}
