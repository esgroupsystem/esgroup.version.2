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
use Inertia\Inertia;
use Inertia\Response;

/** Fleet → Bus Analytics (fleet.buses.*). */
final class BusController extends Controller
{
    public function __construct(
        private readonly BusService $busService,
        private readonly FleetFolderDashboardService $folderService,
    ) {}

    public function index(Request $request): Response
    {
        $data = $this->busService->getMonitoringDashboard($request->only(['search', 'garage', 'company', 'operational_status', 'sale_status']));
        $folders = $this->folderService->getFolderDashboardData([
            'search' => trim((string) $request->query('search', '')),
            'company' => trim((string) $request->query('company', '')),
            'operational_status' => trim((string) $request->query('operational_status', '')),
        ]);
        $user = $request->user();
        $query = $request->query();

        $summaryRows = fn (Collection $summary): array => $summary
            ->map(fn (array $row, int|string $name): array => ['name' => (string) $name, ...$row])
            ->values()
            ->all();

        return Inertia::render('dashboards/fleet/index', [
            'filters' => collect(['search', 'garage', 'company', 'operational_status', 'sale_status'])
                ->mapWithKeys(fn (string $key): array => [$key => (string) ($data['filters'][$key] ?? '')])
                ->all(),
            'options' => [
                'garages' => $data['garages']->values(),
                'companies' => $data['companies']->values(),
                'operational_statuses' => $this->options(Bus::operationalStatusOptions()),
                'sale_statuses' => $this->options(Bus::saleStatusOptions()),
            ],
            'totals' => $data['totals'],
            'filteredCount' => $data['filtered_count'],
            'garageSummary' => $summaryRows($data['garage_summary']),
            'companySummary' => $summaryRows($data['company_summary']),
            'forSaleSummary' => [
                ...collect($data['for_sale_summary'])->except('rows')->all(),
                'rows' => collect($data['for_sale_summary']['rows'])
                    ->map(fn (array $row, int|string $company): array => ['name' => (string) $company, ...$row])
                    ->values(),
            ],
            'folders' => $folders['tabs']->map(fn (array $tab): array => $this->folderTab($tab, $query))->values(),
            'folderTotals' => ['units' => $folders['total_units'], 'for_sale' => $folders['total_for_sale']],
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
        $bus = $this->busService->createBus($request->validated());

        return redirect()->route('fleet.buses.index')->with('success', "Bus {$bus->bus_no} added successfully.");
    }

    public function edit(Request $request, Bus $bus): Response
    {
        return $this->form($request, $bus);
    }

    public function update(UpdateBusRequest $request, Bus $bus): RedirectResponse
    {
        $this->busService->updateBus($bus, $request->validated());

        return redirect()->route('fleet.buses.index', $request->query())->with('success', "Bus {$bus->bus_no} updated successfully.");
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
                // Suggestions only; the fields stay free text.
                ...$this->busService->formOptions(),
            ],
            'urls' => [
                'submit' => $bus ? route('fleet.buses.update', ['bus' => $bus->id, ...$query]) : route('fleet.buses.store'),
                'back' => route('fleet.buses.index', $query),
            ],
        ]);
    }

    /**
     * @param  array<string, string>  $options
     * @return list<array{value: string, label: string}>
     */
    private function options(array $options): array
    {
        return collect($options)
            ->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }

    /**
     * One folder tab as plain rows.
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
                'groups' => $tab['records']->map(fn (Collection $rows, int|string $company): array => [
                    'name' => (string) $company,
                    'rows' => $rows->map(fn (array $row): array => [
                        'id' => (int) $row['record']->id,
                        'bus_no' => (string) $row['record']->bus_no,
                        'plate_no' => $row['record']->plate_no,
                        'company' => $row['record']->company,
                        'garage' => $row['record']->garage,
                        'status' => $row['record']->status,
                        'status_label' => $row['status_label'],
                        'storage_area' => $row['record']->storage_area,
                        'breakdown_start' => $row['record']->breakdown_start_date?->format('M d, Y'),
                        'breakdown_end' => $row['record']->breakdown_end_date?->format('M d, Y'),
                        'days' => $row['days'],
                        'unit_location' => $row['record']->unit_location,
                        'progress' => $row['record']->progress,
                        'remarks' => $row['record']->remarks,
                        'edit_url' => route('fleet.for-sale-units.edit', $row['record']->id),
                    ])->values(),
                ])->values(),
            ];
        }

        return [
            'type' => 'garage',
            'key' => $tab['key'],
            'label' => $tab['label'],
            'count' => (int) $tab['count'],
            'groups' => $tab['companies']->map(fn (Collection $rows, int|string $company): array => [
                'name' => (string) $company,
                'rows' => $rows->map(fn (array $row): array => [
                    'id' => $row['bus']->id,
                    'bus_no' => (string) $row['bus']->bus_no,
                    'plate_no' => $row['bus']->plate_no,
                    'company' => $row['bus']->company,
                    'garage' => $row['bus']->garage,
                    'operational_status' => (string) $row['bus']->operational_status,
                    'operational_status_label' => $row['bus']->operational_status_label,
                    'for_sale' => $row['for_sale'],
                    'sale_status_label' => $row['bus']->sale_status_label,
                    'chassis_number' => $row['bus']->chassis_number,
                    'engine_number' => $row['bus']->engine_number,
                    'case_number' => $row['bus']->case_number,
                    'remarks' => $row['bus']->monitoring_remarks,
                    'edit_url' => route('fleet.buses.edit', ['bus' => $row['bus']->id, ...$query]),
                ])->values(),
            ])->values(),
        ];
    }
}
