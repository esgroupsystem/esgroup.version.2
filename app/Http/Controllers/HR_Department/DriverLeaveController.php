<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR_Department\DriverLeaveActionRequest;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Services\HR_Department\DriverLeaveActionService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DriverLeaveController extends Controller
{
    public function __construct(
        private readonly DriverLeaveActionService $driverLeaveActionService
    ) {}

    public function index(Request $request): View
    {
        $today = Carbon::now('Asia/Manila')->startOfDay();
        $search = trim((string) $request->get('search', ''));

        $baseQuery = DriverLeave::query()
            ->with(['employee.position'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($leaveQuery) use ($search): void {
                    $leaveQuery
                        ->whereHas('employee', function ($employeeQuery) use ($search): void {
                            $employeeQuery
                                ->where('full_name', 'like', "%{$search}%")
                                ->orWhere('employee_id', 'like', "%{$search}%")
                                ->orWhere('employee_id_permanent', 'like', "%{$search}%")
                                ->orWhere('garage', 'like', "%{$search}%")
                                ->orWhere('company', 'like', "%{$search}%");
                        })
                        ->orWhere('leave_type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%");
                });
            });

        $leaves = (clone $baseQuery)
            ->orderByRaw("
                CASE
                    WHEN status IS NULL OR status = '' THEN 1
                    WHEN LOWER(status) IN ('active', 'on_leave') THEN 1
                    WHEN LOWER(status) = 'inactive' THEN 2
                    WHEN LOWER(status) = 'completed' THEN 3
                    WHEN LOWER(status) = 'cancelled' THEN 4
                    WHEN LOWER(status) = 'terminated' THEN 5
                    ELSE 3
                END ASC
            ")
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $this->decorateLeaveRows($leaves, $today);

        $allForCounts = (clone $baseQuery)->get();

        $counts = [
            'active' => 0,
            'first' => 0,
            'second' => 0,
            'inactive' => 0,
            'termination' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'total' => $allForCounts->count(),
        ];

        foreach ($allForCounts as $leave) {
            $level = (int) ($leave->offense_level ?? 0);
            $status = strtolower((string) ($leave->status ?? ''));

            if ($status === 'inactive') {
                $counts['inactive']++;
            }

            if ($status === 'completed') {
                $counts['completed']++;
            }

            if ($status === 'cancelled') {
                $counts['cancelled']++;
            }

            if ($level === 1) {
                $counts['first']++;
            } elseif ($level === 2) {
                $counts['second']++;
            } elseif ($level >= 3 || $status === 'terminated') {
                $counts['termination']++;
            } elseif (! in_array(
                $status,
                ['cancelled', 'terminated', 'completed', 'inactive'],
                true
            )) {
                $counts['active']++;
            }
        }

        $garageSummary = $allForCounts
            ->groupBy(
                fn (DriverLeave $leave) => $leave->employee?->garage ?: 'No Garage Assigned'
            )
            ->map(function ($items, $garage): array {
                return [
                    'garage' => $garage,
                    'total' => $items->count(),
                    'active' => $items
                        ->filter(
                            fn ($leave) => in_array(
                                strtolower((string) ($leave->status ?? '')),
                                ['', 'active', 'on_leave'],
                                true
                            )
                        )
                        ->count(),
                    'first_notice' => $items->where('offense_level', 1)->count(),
                    'second_notice' => $items->where('offense_level', 2)->count(),
                    'inactive' => $items
                        ->filter(
                            fn ($leave) => strtolower((string) ($leave->status ?? '')) === 'inactive'
                        )
                        ->count(),
                    'terminated' => $items
                        ->filter(
                            fn ($leave) => strtolower((string) ($leave->status ?? '')) === 'terminated'
                        )
                        ->count(),
                ];
            })
            ->sortBy('garage')
            ->values();

        if ($request->ajax()) {
            return view(
                'hr_department.leaves.driver.table',
                compact('leaves', 'today')
            );
        }

        return view(
            'hr_department.leaves.driver.index',
            compact('leaves', 'counts', 'today', 'garageSummary')
        );
    }

    public function create(): View
    {
        $drivers = Employee::query()
            ->with('position')
            ->whereHas(
                'position',
                fn ($query) => $query->where('title', 'Driver')
            )
            ->whereIn('status', ['Active', 'Active(Re-Entry)'])
            ->orderBy('garage')
            ->orderBy('full_name')
            ->get();

        return view(
            'hr_department.leaves.driver.create',
            compact('drivers')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'leave_type' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $days = Carbon::parse($validated['start_date'])
            ->diffInDays(Carbon::parse($validated['end_date'])) + 1;

        DB::transaction(function () use ($validated, $days): void {
            DriverLeave::create([
                'employee_id' => $validated['employee_id'],
                'leave_type' => $validated['leave_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'days' => $days,
                'reason' => $validated['reason'] ?? null,
                'offense_level' => 0,
                'status' => 'Active',
            ]);

            Employee::whereKey($validated['employee_id'])->update([
                'status' => 'On Leave',
            ]);
        });

        flash('Driver leave created successfully.')->success();

        return redirect()->route('driver-leave.driver.index');
    }

    public function edit(DriverLeave $leave): View
    {
        $leave->load(['employee.position']);

        $drivers = Employee::query()
            ->with('position')
            ->whereHas(
                'position',
                fn ($query) => $query->where('title', 'Driver')
            )
            ->where(function ($query) use ($leave): void {
                $query
                    ->whereIn('status', ['Active', 'Active(Re-Entry)', 'On Leave'])
                    ->orWhereKey($leave->employee_id);
            })
            ->orderBy('garage')
            ->orderBy('full_name')
            ->get();

        return view(
            'hr_department.leaves.driver.edit',
            compact('leave', 'drivers')
        );
    }

    public function update(
        Request $request,
        DriverLeave $leave
    ): RedirectResponse {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'leave_type' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldEmployeeId = (int) $leave->employee_id;

        $days = Carbon::parse($validated['start_date'])
            ->diffInDays(Carbon::parse($validated['end_date'])) + 1;

        DB::transaction(function () use (
            $leave,
            $validated,
            $days,
            $oldEmployeeId
        ): void {
            $leave->update([
                'employee_id' => $validated['employee_id'],
                'leave_type' => $validated['leave_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'days' => $days,
                'reason' => $validated['reason'] ?? null,
            ]);

            if ($oldEmployeeId !== (int) $validated['employee_id']) {
                Employee::whereKey($oldEmployeeId)->update([
                    'status' => 'Active',
                ]);

                Employee::whereKey($validated['employee_id'])->update([
                    'status' => strtolower((string) $leave->status) === 'inactive'
                            ? 'Inactive'
                            : 'On Leave',
                ]);
            }
        });

        flash('Driver leave updated successfully.')->success();

        return redirect()->route('driver-leave.driver.index');
    }

    public function action(
        DriverLeaveActionRequest $request,
        DriverLeave $leave
    ): RedirectResponse {
        try {
            $message = $this->driverLeaveActionService->handle(
                $leave,
                (string) $request->validated('action_type'),
                $request->validated('note'),
                $request->file('proof_image')
            );

            flash($message)->success();
        } catch (DomainException $exception) {
            flash($exception->getMessage())->warning();
        } catch (Throwable $exception) {
            Log::error('Driver leave action failed.', [
                'driver_leave_id' => $leave->id,
                'action_type' => $request->input('action_type'),
                'user_id' => auth()->id(),
                'exception' => $exception,
            ]);

            flash('The driver leave action could not be completed. Check the log for details.')->error();
        }

        return redirect()->route('driver-leave.driver.index');
    }

    private function decorateLeaveRows($leaves, Carbon $today): void
    {
        foreach ($leaves as $leave) {
            $rawStatus = strtolower((string) ($leave->status ?? ''));
            $statusLabel = $leave->status
                ? ucfirst((string) $leave->status)
                : 'Active';

            $statusColor = match ($rawStatus) {
                'completed' => 'success',
                'cancelled' => 'secondary',
                'terminated' => 'danger',
                'inactive' => 'warning',
                default => 'primary',
            };

            $leave->record_status_badge =
                '<span class="badge rounded-pill badge-subtle-'
                .$statusColor
                .' text-'
                .$statusColor
                .'">'
                .e($statusLabel)
                .'</span>';

            $start = $leave->start_date?->copy()->startOfDay();
            $end = $leave->end_date?->copy()->startOfDay();

            if (in_array(
                $rawStatus,
                ['cancelled', 'terminated', 'completed', 'inactive'],
                true
            )) {
                $leave->remaining_status = match ($rawStatus) {
                    'completed' => '<span class="badge rounded-pill badge-subtle-success text-success">Completed / Ready</span>',
                    'cancelled' => '<span class="badge rounded-pill badge-subtle-secondary text-secondary">Cancelled</span>',
                    'terminated' => '<span class="badge rounded-pill badge-subtle-danger text-danger">Terminated</span>',
                    'inactive' => '<span class="badge rounded-pill badge-subtle-warning text-warning">Inactive after 2nd Notice</span>',
                    default => '<span class="badge rounded-pill badge-subtle-secondary text-secondary">N/A</span>',
                };

                continue;
            }

            if (! $start || ! $end) {
                $leave->remaining_status =
                    '<span class="badge rounded-pill badge-subtle-secondary text-secondary">No schedule</span>';

                continue;
            }

            if ($today->lt($start)) {
                $leave->remaining_status =
                    '<span class="badge rounded-pill badge-subtle-secondary text-secondary">Not started</span>';

                continue;
            }

            if ($today->lte($end)) {
                $remainingDays = $today->diffInDays($end) + 1;
                $label = $remainingDays === 1 ? 'day' : 'days';

                $leave->remaining_status =
                    '<span class="badge rounded-pill badge-subtle-success text-success">On Leave: '
                    .$remainingDays
                    .' '
                    .$label
                    .' left</span>';

                continue;
            }

            $daysAfterEnd = $end->diffInDays($today);

            if ($daysAfterEnd === 1) {
                $leave->remaining_status =
                    '<span class="badge rounded-pill badge-subtle-primary text-primary">Ready for Duty</span>';
            } elseif ($daysAfterEnd <= 9) {
                $leave->remaining_status =
                    '<span class="badge rounded-pill badge-subtle-info text-info">Warning for 1st Notice</span>';
            } elseif ($daysAfterEnd <= 22) {
                $leave->remaining_status =
                    '<span class="badge rounded-pill badge-subtle-warning text-warning">Warning for 2nd Notice</span>';
            } else {
                $leave->remaining_status =
                    '<span class="badge rounded-pill badge-subtle-danger text-danger">Subject for Final Notice</span>';
            }
        }
    }
}
