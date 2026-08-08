<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) ($request->year ?: now('Asia/Manila')->year);
        $month = (int) ($request->month ?: now('Asia/Manila')->month);
        $search = trim((string) $request->search);

        $query = Holiday::query()
            ->whereYear('observed_date', $year)
            ->when($month, fn ($q) => $q->whereMonth('observed_date', $month))
            ->when($search, function ($q) use ($search): void {
                $q->where(function ($inner) use ($search): void {
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
            ->groupBy(fn ($holiday) => $holiday->observed_date->format('Y-m-d'));

        return view('payroll.holidays.index', compact(
            'holidays',
            'calendar',
            'year',
            'month',
            'search'
        ));
    }

    public function create(): View
    {
        return view('payroll.holidays.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedPayload($request);

        Holiday::create($validated);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday created successfully. Payroll multiplier was assigned automatically from the holiday type.');
    }

    public function edit(Holiday $holiday): View
    {
        return view('payroll.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday): RedirectResponse
    {
        $validated = $this->validatedPayload($request);

        $holiday->update($validated);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $holiday->delete();

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'actual_date' => ['required', 'date'],
            'observed_date' => ['required', 'date'],
            'holiday_type' => ['required', 'in:'.Holiday::TYPE_REGULAR.','.Holiday::TYPE_SPECIAL],
            'is_moved' => ['nullable', 'boolean'],
            'override_multipliers' => ['nullable', 'boolean'],
            'not_worked_multiplier' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'worked_multiplier' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'source_proclamation' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_moved'] = $request->boolean('is_moved');
        $validated['is_active'] = $request->boolean('is_active', true);

        if (! $request->boolean('override_multipliers')) {
            $validated = array_merge(
                $validated,
                Holiday::standardMultipliers((string) $validated['holiday_type'])
            );
        } else {
            $validated['not_worked_multiplier'] = round((float) ($validated['not_worked_multiplier'] ?? 0), 2);
            $validated['worked_multiplier'] = round((float) ($validated['worked_multiplier'] ?? 0), 2);
        }

        unset($validated['override_multipliers']);

        return $validated;
    }
}
