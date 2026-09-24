<?php

declare(strict_types=1);

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\ForSaleUnitRequest;
use App\Models\Bus;
use App\Models\BusForSaleRecord;
use App\Services\Fleet\ForSaleUnitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ForSaleUnitController extends Controller
{
    public function __construct(
        private readonly ForSaleUnitService $forSaleUnitService
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search',
            'company',
            'garage',
            'status',
        ]);

        $data = $this->forSaleUnitService->getIndexData($filters);
        $user = $request->user();

        return Inertia::render('dashboards/fleet/for-sale-index', [
            'filters' => [
                'search' => (string) ($filters['search'] ?? ''),
                'company' => (string) ($filters['company'] ?? ''),
                'garage' => (string) ($filters['garage'] ?? ''),
                'status' => (string) ($filters['status'] ?? ''),
            ],
            'records' => $data['records']->through(fn (BusForSaleRecord $record): array => [
                'id' => $record->id,
                'bus_no' => (string) $record->bus_no,
                'plate_no' => $record->plate_no,
                'company' => $record->company,
                'garage' => $record->garage,
                'status' => (string) $record->status,
                'status_label' => $record->status_label,
                'storage_area' => $record->storage_area,
                'breakdown_start' => $record->breakdown_start_date?->format('M d, Y'),
                'breakdown_end' => $record->breakdown_end_date?->format('M d, Y'),
                'column_11' => $record->column_11,
                'days' => (int) $record->live_days_in_breakdown,
                'unit_location' => $record->unit_location,
                'progress' => $record->progress,
                'remarks' => $record->remarks,
                'edit_url' => route('fleet.for-sale-units.edit', $record),
                'destroy_url' => route('fleet.for-sale-units.destroy', $record),
            ]),
            'summary' => $data['summary'],
            'options' => [
                'companies' => collect($data['companies'])->values(),
                'garages' => collect($data['garages'])->values(),
                'statuses' => $this->options(BusForSaleRecord::statusOptions()),
            ],
            // Mirrors the route middleware on each for-sale-units.* route.
            'can' => [
                'create' => (bool) $user?->can('fleet.manage.view'),
                'edit' => (bool) $user?->can('fleet.manage.edit'),
                'delete' => (bool) $user?->can('fleet.manage.delete'),
            ],
            'urls' => [
                'index' => route('fleet.for-sale-units.index'),
                'create' => route('fleet.for-sale-units.create'),
                'fleet' => route('fleet.buses.index'),
            ],
        ]);
    }

    public function create(): Response
    {
        return $this->form(new BusForSaleRecord);
    }

    public function store(ForSaleUnitRequest $request): RedirectResponse
    {
        $record = $this->forSaleUnitService->create($request->validated());

        return redirect()
            ->route('fleet.for-sale-units.edit', $record)
            ->with('success', 'For sale unit has been created and synced to bus monitoring.');
    }

    public function edit(BusForSaleRecord $forSaleRecord): Response
    {
        return $this->form($forSaleRecord);
    }

    public function update(
        ForSaleUnitRequest $request,
        BusForSaleRecord $forSaleRecord
    ): RedirectResponse {
        $this->forSaleUnitService->update($forSaleRecord, $request->validated());

        return redirect()
            ->route('fleet.for-sale-units.edit', $forSaleRecord)
            ->with('success', 'For sale unit has been updated and synced to bus monitoring.');
    }

    public function destroy(BusForSaleRecord $forSaleRecord): RedirectResponse
    {
        $this->forSaleUnitService->delete($forSaleRecord);

        return redirect()
            ->route('fleet.for-sale-units.index')
            ->with('success', 'For sale unit has been deleted.');
    }

    private function form(BusForSaleRecord $record): Response
    {
        $formData = $this->forSaleUnitService->getFormData();
        $user = request()->user();

        return Inertia::render('dashboards/fleet/for-sale-form', [
            'record' => $record->exists ? [
                'id' => $record->id,
                'bus_id' => $record->bus_id ? (string) $record->bus_id : '',
                'bus_no' => (string) $record->bus_no,
                'plate_no' => (string) ($record->plate_no ?? ''),
                'company' => (string) ($record->company ?? ''),
                'garage' => (string) ($record->garage ?? ''),
                'status' => (string) $record->status,
                'storage_area' => (string) ($record->storage_area ?? ''),
                'breakdown_start_date' => $record->breakdown_start_date?->toDateString() ?? '',
                'breakdown_end_date' => $record->breakdown_end_date?->toDateString() ?? '',
                'column_11' => (string) ($record->column_11 ?? ''),
                'unit_location' => (string) ($record->unit_location ?? ''),
                'progress' => (string) ($record->progress ?? ''),
                'remarks' => (string) ($record->remarks ?? ''),
                'days' => (int) $record->live_days_in_breakdown,
                'destroy_url' => route('fleet.for-sale-units.destroy', $record),
            ] : null,
            'buses' => collect($formData['buses'])->map(fn (Bus $bus): array => [
                'value' => (string) $bus->id,
                'label' => collect([$bus->bus_no, $bus->plate_no, $bus->company, $bus->garage])->filter()->implode(' | '),
                'bus_no' => (string) $bus->bus_no,
                'plate_no' => (string) ($bus->plate_no ?? ''),
                'company' => (string) ($bus->company ?? ''),
                'garage' => (string) ($bus->garage ?? ''),
            ])->values(),
            'statuses' => $this->options(BusForSaleRecord::statusOptions()),
            'can' => ['delete' => $record->exists && (bool) $user?->can('fleet.manage.delete')],
            'urls' => [
                'submit' => $record->exists ? route('fleet.for-sale-units.update', $record) : route('fleet.for-sale-units.store'),
                'index' => route('fleet.for-sale-units.index'),
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
}
