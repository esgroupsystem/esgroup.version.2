<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HolidayController extends Controller
{
    public function index(Request $request): Response
    {
        $year = (int) ($request->year ?: now('Asia/Manila')->year);
        $month = (int) ($request->month ?: now('Asia/Manila')->month);
        $search = trim((string) $request->search);
        $type = in_array($request->type, [Holiday::TYPE_REGULAR, Holiday::TYPE_SPECIAL], true) ? (string) $request->type : '';

        $query = Holiday::query()
            ->whereYear('observed_date', $year)
            ->when($month, fn ($q) => $q->whereMonth('observed_date', $month))
            ->when($type, fn ($q) => $q->where('holiday_type', $type))
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
            ->groupBy(fn (Holiday $holiday): string => $holiday->observed_date->format('Y-m-d'))
            ->map(fn ($day) => $day->map(fn (Holiday $holiday): array => [
                'name' => $holiday->name,
                'type' => $holiday->holiday_type,
                'moved_from' => $holiday->is_moved ? $holiday->actual_date->format('M d') : null,
            ])->values());

        $user = $request->user();

        return Inertia::render('payroll/holidays/index', [
            'holidays' => $holidays->through(fn (Holiday $holiday): array => [
                'id' => $holiday->id,
                'name' => $holiday->name,
                'type' => $holiday->holiday_type,
                'actual_date' => $holiday->actual_date->format('M d, Y'),
                'observed_date' => $holiday->observed_date->format('M d, Y'),
                'is_moved' => (bool) $holiday->is_moved,
                'is_active' => (bool) $holiday->is_active,
                'not_worked_multiplier' => (float) $holiday->not_worked_multiplier,
                'worked_multiplier' => (float) $holiday->worked_multiplier,
                'source' => $holiday->source_proclamation,
                'urls' => [
                    'edit' => route('holidays.edit', $holiday),
                    'destroy' => route('holidays.destroy', $holiday),
                ],
            ]),
            'calendar' => $calendar,
            'filters' => ['year' => $year, 'month' => $month, 'search' => $search, 'type' => $type],
            'can' => [
                'create' => $user->can('holidays.create'),
                'update' => $user->can('holidays.update'),
                'delete' => $user->can('holidays.delete'),
            ],
            'urls' => [
                'index' => route('holidays.index'),
                'create' => route('holidays.create'),
            ],
        ]);
    }

    public function create(): Response
    {
        return $this->renderForm(null);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedPayload($request);

        Holiday::create($validated);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday created successfully. Payroll multiplier was assigned automatically from the holiday type.');
    }

    public function edit(Holiday $holiday): Response
    {
        return $this->renderForm($holiday);
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

    private function renderForm(?Holiday $holiday): Response
    {
        $type = $holiday->holiday_type ?? Holiday::TYPE_REGULAR;
        $standard = Holiday::standardMultipliers($type);
        $notWorked = $holiday ? (float) $holiday->not_worked_multiplier : (float) $standard['not_worked_multiplier'];
        $worked = $holiday ? (float) $holiday->worked_multiplier : (float) $standard['worked_multiplier'];

        return Inertia::render('payroll/holidays/form', [
            'holiday' => $holiday ? ['id' => $holiday->id, 'name' => $holiday->name] : null,
            'values' => [
                'name' => (string) ($holiday->name ?? ''),
                'holiday_type' => $type,
                'source_proclamation' => (string) ($holiday->source_proclamation ?? ''),
                'actual_date' => $holiday?->actual_date?->format('Y-m-d') ?? '',
                'observed_date' => $holiday?->observed_date?->format('Y-m-d') ?? '',
                // A saved holiday whose multipliers differ from the standard ones was customised.
                'override_multipliers' => $holiday !== null
                    && (abs($notWorked - (float) $standard['not_worked_multiplier']) > 0.001
                        || abs($worked - (float) $standard['worked_multiplier']) > 0.001),
                'not_worked_multiplier' => number_format($notWorked, 2, '.', ''),
                'worked_multiplier' => number_format($worked, 2, '.', ''),
                'notes' => (string) ($holiday->notes ?? ''),
                'is_moved' => (bool) ($holiday->is_moved ?? false),
                'is_active' => (bool) ($holiday->is_active ?? true),
            ],
            'standardMultipliers' => [
                Holiday::TYPE_REGULAR => Holiday::standardMultipliers(Holiday::TYPE_REGULAR),
                Holiday::TYPE_SPECIAL => Holiday::standardMultipliers(Holiday::TYPE_SPECIAL),
            ],
            'urls' => [
                'index' => route('holidays.index'),
                'submit' => $holiday ? route('holidays.update', $holiday) : route('holidays.store'),
            ],
        ]);
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
