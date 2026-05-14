<?php

namespace App\Http\Controllers;

use App\Models\BusDetail;
use Illuminate\Http\Request;

class AllBusController extends Controller
{
    public function index(Request $request)
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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('maintenance.allbus.table', compact('buses'))->render(),
            ]);
        }

        return view('maintenance.allbus.index', compact('buses'));
    }

    public function create()
    {
        return view('maintenance.allbus.create');
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

    public function edit(BusDetail $bus)
    {
        return view('maintenance.allbus.edit', compact('bus'));
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
}
