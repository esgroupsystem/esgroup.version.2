<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\HrOffense;
use Illuminate\Http\Request;

class HrOffenseController extends Controller
{
    public function index(Request $request)
    {
        $query = HrOffense::query();

        // Filter by ID
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        $offenses = $query
            ->orderBy('id') // important for numeric order
            ->paginate(10)
            ->withQueryString();

        return view('hr_department.offenses.index', compact('offenses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section' => 'required|string|max:255',
            'offense_description' => 'required|string',
            'offense_type' => 'required|string',
            'offense_gravity' => 'required|string',
        ]);

        HrOffense::create($request->all());

        return back()->with('success', 'Offense saved successfully.');
    }
}
