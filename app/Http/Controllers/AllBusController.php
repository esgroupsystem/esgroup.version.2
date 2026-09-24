<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BusDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AllBusController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->search;

        $buses = BusDetail::query()
            ->when($search, function ($query) use ($search) {
                $query->where('garage', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('body_number', 'like', "%{$search}%")
                    ->orWhere('plate_number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $user = $request->user();

        return Inertia::render('fleet/all-bus/index', [
            'buses' => $buses->through(fn (BusDetail $bus): array => [
                'id' => $bus->id,
                'garage' => $bus->garage,
                'name' => $bus->name,
                'body_number' => $bus->body_number,
                'plate_number' => $bus->plate_number,
                'edit_url' => route('allbus.edit', ['bus' => $bus->id]),
                'destroy_url' => route('allbus.destroy', ['bus' => $bus->id]),
            ]),
            'filters' => ['search' => (string) ($search ?? '')],
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

    public function store(Request $request)
    {
        $request->validate([
            'garage' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'body_number' => 'required|string|max:255|unique:bus_details,body_number',
            'plate_number' => 'required|string|max:255|unique:bus_details,plate_number',
        ]);

        BusDetail::create($request->only([
            'garage',
            'name',
            'body_number',
            'plate_number',
        ]));

        return redirect()
            ->route('allbus.index')
            ->with('success', 'Bus added successfully.');
    }

    public function edit(BusDetail $bus): Response
    {
        return $this->form($bus);
    }

    public function update(Request $request, BusDetail $bus)
    {
        $request->validate([
            'garage' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'body_number' => 'required|string|max:255|unique:bus_details,body_number,'.$bus->id,
            'plate_number' => 'required|string|max:255|unique:bus_details,plate_number,'.$bus->id,
        ]);

        $bus->update($request->only([
            'garage',
            'name',
            'body_number',
            'plate_number',
        ]));

        return redirect()->route('allbus.index')->with('success', 'Bus updated successfully.');
    }

    public function destroy(BusDetail $bus)
    {
        $bus->delete();

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
            'garages' => BusDetail::query()->whereNotNull('garage')->where('garage', '!=', '')->distinct()->orderBy('garage')->pluck('garage'),
            'urls' => [
                'index' => route('allbus.index'),
                'submit' => $bus ? route('allbus.update', ['bus' => $bus->id]) : route('allbus.store'),
            ],
        ]);
    }
}
