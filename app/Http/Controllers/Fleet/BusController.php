<?php

declare(strict_types=1);

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\StoreBusRequest;
use App\Http\Requests\Fleet\UpdateBusRequest;
use App\Models\Bus;
use App\Services\Fleet\BusService;
use App\Services\Fleet\FleetFolderDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BusController extends Controller
{
    public function __construct(
        private readonly BusService $busService,
        private readonly FleetFolderDashboardService $fleetFolderDashboardService
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search',
            'garage',
            'company',
            'operational_status',
            'sale_status',
        ]);

        $dashboardData = $this->busService->getMonitoringDashboard($filters);
        $folderData = $this->fleetFolderDashboardService->getFolderDashboardData($request);
        $user = $request->user();
        $query = $request->query();

        $summaryRows = fn (Collection $summary): array => $summary
            ->map(fn (array $data, int|string $name): array => ['name' => (string) $name, ...$data])
            ->values()
            ->all();

        return Inertia::render('dashboards/fleet/index', [
            'filters' => [
                'search' => (string) ($dashboardData['filters']['search'] ?? ''),
                'garage' => (string) ($dashboardData['filters']['garage'] ?? ''),
                'company' => (string) ($dashboardData['filters']['company'] ?? ''),
                'operational_status' => (string) ($dashboardData['filters']['operational_status'] ?? ''),
                'sale_status' => (string) ($dashboardData['filters']['sale_status'] ?? ''),
            ],
            'options' => [
                'garages' => collect($dashboardData['garages'])->values(),
                'companies' => collect($dashboardData['companies'])->values(),
                'operational_statuses' => $this->options(Bus::operationalStatusOptions()),
                'sale_statuses' => $this->options(Bus::saleStatusOptions()),
            ],
            'totals' => $dashboardData['totals'],
            'filteredCount' => $dashboardData['filtered_count'],
            'garageSummary' => $summaryRows($dashboardData['garage_summary']),
            'companySummary' => $summaryRows($dashboardData['company_summary']),
            'forSaleSummary' => [
                ...collect($dashboardData['for_sale_summary'])->except('rows')->all(),
                'rows' => collect($dashboardData['for_sale_summary']['rows'])
                    ->map(fn (array $data, int|string $company): array => ['name' => (string) $company, ...$data])
                    ->values(),
            ],
            'folders' => collect($folderData['tabs'] ?? [])->map(fn (array $tab): array => $this->folderTab($tab, $query))->values(),
            'folderTotals' => [
                'units' => (int) ($folderData['total_units'] ?? 0),
                'for_sale' => (int) ($folderData['total_for_sale'] ?? 0),
            ],
            'can' => [
                'create' => (bool) $user?->can('fleet.manage.create.view'),
                'edit' => (bool) $user?->can('fleet.manage.edit'),
                'for_sale_create' => (bool) $user?->can('fleet.manage.view'),
                'for_sale_edit' => (bool) $user?->can('fleet.manage.edit'),
            ],
            'urls' => [
                'index' => route('fleet.buses.index'),
                'create' => route('fleet.buses.create', $query),
                'forSaleIndex' => route('fleet.for-sale-units.index'),
                'forSaleCreate' => route('fleet.for-sale-units.create'),
            ],
        ]);
    }

    public function analytics(Request $request): RedirectResponse
    {
        return redirect()->route('fleet.buses.index', $request->query());
    }

    public function create(Request $request): Response
    {
        return $this->form($request, null);
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

    public function edit(Request $request, Bus $bus): Response
    {
        return $this->form($request, $bus);
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

    private function form(Request $request, ?Bus $bus): Response
    {
        $query = $request->query();

        return Inertia::render('dashboards/fleet/form', [
            'bus' => $bus ? [
                'id' => $bus->id,
                'bus_no' => (string) $bus->bus_no,
                'plate_no' => (string) ($bus->plate_no ?? ''),
                'company' => (string) ($bus->company ?? ''),
                'garage' => (string) ($bus->garage ?? ''),
                'chassis_number' => (string) ($bus->chassis_number ?? ''),
                'engine_number' => (string) ($bus->engine_number ?? ''),
                'case_number' => (string) ($bus->case_number ?? ''),
                'operational_status' => (string) $bus->operational_status,
                'sale_status' => (string) $bus->sale_status,
                'monitoring_remarks' => (string) ($bus->monitoring_remarks ?? ''),
                'updated_at' => $bus->updated_at?->format('M d, Y h:i A'),
            ] : null,
            'options' => [
                'operational_statuses' => $this->options(Bus::operationalStatusOptions()),
                'sale_statuses' => $this->options(Bus::saleStatusOptions()),
                // Suggestions only; the fields stay free text like before.
                'garages' => Bus::query()->whereNotNull('garage')->where('garage', '!=', '')->distinct()->orderBy('garage')->pluck('garage'),
                'companies' => Bus::query()->whereNotNull('company')->where('company', '!=', '')->distinct()->orderBy('company')->pluck('company'),
            ],
            'urls' => [
                'submit' => $bus
                    ? route('fleet.buses.update', ['bus' => $bus->id, ...$query])
                    : route('fleet.buses.store'),
                'back' => route('fleet.buses.index', $query),
            ],
        ]);
    }

    /** @param array<string, string> $options @return list<array{value: string, label: string}> */
    private function options(array $options): array
    {
        return collect($options)
            ->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }

    /**
     * One folder tab from FleetFolderDashboardService as plain rows.
     *
     * @param  array<string, mixed>  $tab
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function folderTab(array $tab, array $query): array
    {
        if ($tab['type'] === 'for_sale') {
            return [
                'type' => 'for_sale',
                'key' => $tab['key'],
                'label' => $tab['label'],
                'count' => (int) $tab['count'],
                'groups' => collect($tab['records'])->map(fn (Collection $records, int|string $company): array => [
                    'name' => (string) $company,
                    'rows' => $records->map(fn ($record): array => [
                        'id' => (int) $record->id,
                        'bus_no' => (string) $record->bus_no,
                        'plate_no' => $record->plate_no,
                        'company' => $record->company,
                        'garage' => $record->garage,
                        'status' => $record->status,
                        'status_label' => $record->status_label,
                        'storage_area' => $record->storage_area,
                        'breakdown_start' => $record->breakdown_start_date ? \Carbon\Carbon::parse($record->breakdown_start_date)->format('M d, Y') : null,
                        'breakdown_end' => $record->breakdown_end_date ? \Carbon\Carbon::parse($record->breakdown_end_date)->format('M d, Y') : null,
                        'days' => (int) $record->live_days_in_breakdown,
                        'unit_location' => $record->unit_location,
                        'progress' => $record->progress,
                        'remarks' => $record->remarks,
                        'edit_url' => route('fleet.for-sale-units.edit', $record->id),
                    ])->values(),
                ])->values(),
            ];
        }

        $forSaleIds = collect($tab['for_sale_bus_ids'] ?? [])->map(fn ($id): int => (int) $id);
        $forSaleNumbers = collect($tab['for_sale_bus_numbers'] ?? []);

        return [
            'type' => 'garage',
            'key' => $tab['key'],
            'label' => $tab['label'],
            'count' => (int) $tab['count'],
            'groups' => collect($tab['companies'])->map(fn (Collection $buses, int|string $company): array => [
                'name' => (string) $company,
                'rows' => $buses->map(fn (Bus $bus): array => [
                    'id' => $bus->id,
                    'bus_no' => (string) $bus->bus_no,
                    'plate_no' => $bus->plate_no,
                    'company' => $bus->company,
                    'garage' => $bus->garage,
                    'operational_status' => (string) $bus->operational_status,
                    'operational_status_label' => $bus->operational_status_label,
                    // Same rule as the Blade folder view: linked by id, or by bus number.
                    'for_sale' => $forSaleIds->contains((int) $bus->id) || $forSaleNumbers->contains(Str::upper(trim((string) $bus->bus_no))),
                    'sale_status_label' => $bus->sale_status_label,
                    'chassis_number' => $bus->chassis_number,
                    'engine_number' => $bus->engine_number,
                    'case_number' => $bus->case_number,
                    'remarks' => $bus->monitoring_remarks,
                    'edit_url' => route('fleet.buses.edit', ['bus' => $bus->id, ...$query]),
                ])->values(),
            ])->values(),
        ];
    }
}
