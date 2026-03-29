<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) ($request->year ?: now('Asia/Manila')->year);
        $month = (int) ($request->month ?: now('Asia/Manila')->month);
        $search = trim((string) $request->search);

        $query = Holiday::query()
            ->whereYear('observed_date', $year)
            ->when($month, fn ($q) => $q->whereMonth('observed_date', $month))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('holiday_type', 'like', "%{$search}%")
                        ->orWhere('source_proclamation', 'like', "%{$search}%");
                });
            })
            ->orderBy('observed_date');

        $holidays = $query->paginate(20)->withQueryString();

        $calendar = Holiday::query()
            ->whereYear('observed_date', $year)
            ->orderBy('observed_date')
            ->get()
            ->groupBy(fn ($h) => $h->observed_date->format('Y-m-d'));

        return view('payroll.holidays.index', compact(
            'holidays',
            'calendar',
            'year',
            'month',
            'search'
        ));
    }

    public function create()
    {
        return view('payroll.holidays.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'actual_date' => ['required', 'date'],
            'observed_date' => ['required', 'date'],
            'holiday_type' => ['required', 'in:regular,special'],
            'is_moved' => ['nullable', 'boolean'],
            'not_worked_multiplier' => ['required', 'numeric', 'min:0'],
            'worked_multiplier' => ['required', 'numeric', 'min:0'],
            'source_proclamation' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_moved'] = $request->boolean('is_moved');
        $validated['is_active'] = $request->boolean('is_active', true);

        Holiday::create($validated);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday created successfully.');
    }

    public function edit(Holiday $holiday)
    {
        return view('payroll.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'actual_date' => ['required', 'date'],
            'observed_date' => ['required', 'date'],
            'holiday_type' => ['required', 'in:regular,special'],
            'is_moved' => ['nullable', 'boolean'],
            'not_worked_multiplier' => ['required', 'numeric', 'min:0'],
            'worked_multiplier' => ['required', 'numeric', 'min:0'],
            'source_proclamation' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_moved'] = $request->boolean('is_moved');
        $validated['is_active'] = $request->boolean('is_active', true);

        $holiday->update($validated);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }
}
