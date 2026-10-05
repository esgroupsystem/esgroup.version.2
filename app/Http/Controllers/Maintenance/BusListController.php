<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\BusDetailRequest;
use App\Models\BusDetail;
use App\Services\Maintenance\BusListService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Maintenance → Bus List (allbus.*). */
final class BusListController extends Controller
{
    public function __construct(private readonly BusListService $busListService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $user = $request->user();

        return Inertia::render('fleet/all-bus/index', [
            'buses' => $this->busListService->paginate($search)->through(fn (BusDetail $bus): array => [
                'id' => $bus->id,
                'garage' => $bus->garage,
                'name' => $bus->name,
                'body_number' => $bus->body_number,
                'plate_number' => $bus->plate_number,
                'edit_url' => route('allbus.edit', ['bus' => $bus->id]),
                'destroy_url' => route('allbus.destroy', ['bus' => $bus->id]),
            ]),
            'filters' => ['search' => $search],
            'can' => [
                'create' => (bool) $user?->can('allbus.create'),
                'edit' => (bool) $user?->can('allbus.edit'),
                'delete' => (bool) $user?->can('allbus.delete'),
            ],
            'urls' => ['index' => route('allbus.index'), 'create' => route('allbus.create')],
        ]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(BusDetailRequest $request): RedirectResponse
    {
        $this->busListService->create($request->validated());

        return redirect()->route('allbus.index')->with('success', 'Bus added successfully.');
    }

    public function edit(BusDetail $bus): Response
    {
        return $this->form($bus);
    }

    public function update(BusDetailRequest $request, BusDetail $bus): RedirectResponse
    {
        $this->busListService->update($bus, $request->validated());

        return redirect()->route('allbus.index')->with('success', 'Bus updated successfully.');
    }

    public function destroy(BusDetail $bus): RedirectResponse
    {
        $this->busListService->delete($bus);

        return redirect()->route('allbus.index')->with('success', 'Bus deleted successfully.');
    }

    private function form(?BusDetail $bus): Response
    {
        return Inertia::render('fleet/all-bus/form', [
            'bus' => $bus ? ['id' => $bus->id, 'body_number' => $bus->body_number] : null,
            'values' => [
                'garage' => (string) ($bus->garage ?? ''),
                'name' => (string) ($bus->name ?? ''),
                'body_number' => (string) ($bus->body_number ?? ''),
                'plate_number' => (string) ($bus->plate_number ?? ''),
            ],
            'garages' => $this->busListService->garages(),
            'urls' => [
                'index' => route('allbus.index'),
                'submit' => $bus ? route('allbus.update', ['bus' => $bus->id]) : route('allbus.store'),
            ],
        ]);
    }
}
