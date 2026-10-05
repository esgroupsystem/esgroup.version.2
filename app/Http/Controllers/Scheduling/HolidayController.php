<?php

declare(strict_types=1);

namespace App\Http\Controllers\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scheduling\HolidayRequest;
use App\Http\Resources\Scheduling\HolidayFormResource;
use App\Http\Resources\Scheduling\HolidayResource;
use App\Models\Holiday;
use App\Services\Scheduling\HolidayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Scheduling & Rates → Holiday Calendar.
 */
final class HolidayController extends Controller
{
    public function __construct(
        private readonly HolidayService $holidays,
    ) {}

    public function index(Request $request): Response
    {
        $year = (int) ($request->input('year') ?: now('Asia/Manila')->year);
        $month = (int) ($request->input('month') ?: now('Asia/Manila')->month);
        $search = trim((string) $request->input('search', ''));
        $type = in_array($request->input('type'), Holiday::TYPES, true) ? (string) $request->input('type') : '';
        $user = $request->user();

        return Inertia::render('payroll/holidays/index', [
            'holidays' => $this->holidays->paginate($year, $month, $type, $search)
                ->through(fn (Holiday $holiday): array => HolidayResource::make($holiday)->resolve($request)),
            'calendar' => $this->holidays->calendar($year),
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

    public function create(Request $request): Response
    {
        return $this->form($request, null);
    }

    public function store(HolidayRequest $request): RedirectResponse
    {
        $this->holidays->create($request->validated());

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday created successfully. Payroll multiplier was assigned automatically from the holiday type.');
    }

    public function edit(Request $request, Holiday $holiday): Response
    {
        return $this->form($request, $holiday);
    }

    public function update(HolidayRequest $request, Holiday $holiday): RedirectResponse
    {
        $this->holidays->update($holiday, $request->validated());

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $this->holidays->delete($holiday);

        return redirect()->route('holidays.index')->with('success', 'Holiday deleted successfully.');
    }

    private function form(Request $request, ?Holiday $holiday): Response
    {
        return Inertia::render('payroll/holidays/form', [
            'holiday' => $holiday ? ['id' => $holiday->id, 'name' => $holiday->name] : null,
            'values' => HolidayFormResource::make($holiday)->resolve($request),
            'standardMultipliers' => collect(Holiday::TYPES)->mapWithKeys(fn (string $type): array => [$type => Holiday::standardMultipliers($type)])->all(),
            'urls' => [
                'index' => route('holidays.index'),
                'submit' => $holiday ? route('holidays.update', $holiday) : route('holidays.store'),
            ],
        ]);
    }
}
