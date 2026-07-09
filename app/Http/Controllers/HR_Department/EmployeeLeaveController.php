<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeLeaveController extends Controller
{
    public function index(Request $request): View
    {
        date_default_timezone_set('Asia/Manila');

        $today = Carbon::now('Asia/Manila')->startOfDay();
        $search = $request->get('search');

        $baseQuery = EmployeeLeave::query()
            ->with(['employee.position'])
            ->when(! empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('employee', function ($employeeQuery) use ($search) {
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
                    WHEN LOWER(status) = 'active' THEN 1
                    WHEN LOWER(status) = 'on_leave' THEN 1
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
            $status = strtolower($leave->status ?? '');

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
            } elseif (! in_array($status, ['cancelled', 'terminated', 'completed', 'inactive'], true)) {
                $counts['active']++;
            }
        }

        $garageSummary = $allForCounts
            ->groupBy(fn (EmployeeLeave $leave) => $leave->employee?->garage ?: 'No Garage Assigned')
            ->map(function ($items, $garage) {
                return [
                    'garage' => $garage,
                    'total' => $items->count(),
                    'active' => $items->filter(fn ($leave) => in_array(strtolower($leave->status ?? ''), ['', 'active', 'on_leave'], true))->count(),
                    'first_notice' => $items->where('offense_level', 1)->count(),
                    'second_notice' => $items->where('offense_level', 2)->count(),
                    'inactive' => $items->filter(fn ($leave) => strtolower($leave->status ?? '') === 'inactive')->count(),
                    'terminated' => $items->filter(fn ($leave) => strtolower($leave->status ?? '') === 'terminated')->count(),
                ];
            })
            ->sortBy('garage')
            ->values();

        if ($request->ajax()) {
            return view('hr_department.leaves.employee.table', compact('leaves', 'today'));
        }

        return view('hr_department.leaves.employee.index', compact(
            'leaves',
            'counts',
            'today',
            'garageSummary'
        ));
    }

    public function create(): View
    {
        $employees = Employee::query()
            ->with('position')
            ->whereIn('status', ['Active', 'Active(Re-Entry)'])
            ->where(function ($query) {
                $query->whereDoesntHave('position')
                    ->orWhereHas('position', function ($positionQuery) {
                        $positionQuery->whereNotIn('title', ['Driver', 'Conductor']);
                    });
            })
            ->orderBy('garage')
            ->orderBy('full_name')
            ->get();

        return view('hr_department.leaves.employee.create', compact('employees'));
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

        DB::transaction(function () use ($validated, $days) {
            EmployeeLeave::create([
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

        flash('Employee leave created successfully.')->success();

        return redirect()->route('employee-leave.employee.index');
    }

    public function edit(EmployeeLeave $leave): View
    {
        $leave->load(['employee.position']);

        $employees = Employee::query()
            ->with('position')
            ->where(function ($query) use ($leave) {
                $query->whereIn('status', ['Active', 'Active(Re-Entry)'])
                    ->orWhereKey($leave->employee_id);
            })
            ->where(function ($query) {
                $query->whereDoesntHave('position')
                    ->orWhereHas('position', function ($positionQuery) {
                        $positionQuery->whereNotIn('title', ['Driver', 'Conductor']);
                    });
            })
            ->orderBy('garage')
            ->orderBy('full_name')
            ->get();

        return view('hr_department.leaves.employee.edit', compact('leave', 'employees'));
    }

    public function update(Request $request, EmployeeLeave $leave): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'leave_type' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldEmployeeId = $leave->employee_id;

        $days = Carbon::parse($validated['start_date'])
            ->diffInDays(Carbon::parse($validated['end_date'])) + 1;

        DB::transaction(function () use ($leave, $validated, $days, $oldEmployeeId) {
            $leave->update([
                'employee_id' => $validated['employee_id'],
                'leave_type' => $validated['leave_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'days' => $days,
                'reason' => $validated['reason'] ?? null,
            ]);

            if ((int) $oldEmployeeId !== (int) $validated['employee_id']) {
                Employee::whereKey($oldEmployeeId)->update([
                    'status' => 'Active',
                ]);

                Employee::whereKey($validated['employee_id'])->update([
                    'status' => $leave->status === 'Inactive' ? 'Inactive' : 'On Leave',
                ]);
            }
        });

        flash('Employee leave updated successfully.')->success();

        return redirect()->route('employee-leave.employee.index');
    }

    public function action(Request $request, EmployeeLeave $leave): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'action_type' => ['required', 'in:first,second,terminate,cancel,ready'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $action = $request->input('action_type');
        $note = $request->input('note');

        $leave->load('employee');

        if ($action === 'first') {
            return $this->markFirstNotice($leave, $note);
        }

        if ($action === 'second') {
            return $this->markSecondNoticeAndDeactivate($leave, $note);
        }

        if ($action === 'terminate') {
            return $this->markFinalNoticeAndTerminate($leave, $note);
        }

        if ($action === 'cancel') {
            return $this->cancelLeave($leave, $note);
        }

        if ($action === 'ready') {
            return $this->markReadyForDuty($leave, $note);
        }

        return redirect()->route('employee-leave.employee.index');
    }

    private function markFirstNotice(EmployeeLeave $leave, ?string $note): RedirectResponse
    {
        if ($leave->first_notice_sent_at) {
            flash('1st Notice already sent.')->info();

            return back();
        }

        DB::transaction(function () use ($leave, $note) {
            $leave->update([
                'first_notice_sent_at' => now('Asia/Manila'),
                'offense_level' => 1,
                'last_action_note' => $note,
            ]);
        });

        flash('1st Notice marked as sent.')->success();

        return redirect()->route('employee-leave.employee.index');
    }

    private function markSecondNoticeAndDeactivate(EmployeeLeave $leave, ?string $note): RedirectResponse
    {
        if (! $leave->first_notice_sent_at) {
            flash('Send 1st Notice first.')->warning();

            return back();
        }

        if ($leave->second_notice_sent_at) {
            flash('2nd Notice already sent.')->info();

            return back();
        }

        DB::transaction(function () use ($leave, $note) {
            $leave->update([
                'second_notice_sent_at' => now('Asia/Manila'),
                'offense_level' => 2,
                'status' => 'Inactive',
                'last_action_note' => $note,
            ]);

            if ($leave->employee) {
                $leave->employee->update([
                    'status' => 'Inactive',
                ]);
            }
        });

        flash('2nd Notice marked as sent. Employee record is now automatically Inactive.')->success();

        return redirect()->route('employee-leave.employee.index');
    }

    private function markFinalNoticeAndTerminate(EmployeeLeave $leave, ?string $note): RedirectResponse
    {
        if (! $leave->second_notice_sent_at) {
            flash('Send 2nd Notice first.')->warning();

            return back();
        }

        if ($leave->final_notice_sent_at) {
            flash('Final Notice already sent.')->info();

            return back();
        }

        DB::transaction(function () use ($leave, $note) {
            $leave->update([
                'final_notice_sent_at' => now('Asia/Manila'),
                'offense_level' => 3,
                'status' => 'Terminated',
                'last_action_note' => $note,
            ]);

            if ($leave->employee) {
                $leave->employee->update([
                    'status' => 'Terminated',
                ]);
            }
        });

        flash('Final Notice marked as sent. Employee record is now Terminated.')->success();

        return redirect()->route('employee-leave.employee.index');
    }

    private function cancelLeave(EmployeeLeave $leave, ?string $note): RedirectResponse
    {
        DB::transaction(function () use ($leave, $note) {
            $leave->update([
                'status' => 'Cancelled',
                'last_action_note' => $note,
            ]);

            if ($leave->employee) {
                $leave->employee->update([
                    'status' => 'Active',
                ]);
            }
        });

        flash('Leave cancelled. Employee returned to Active.')->success();

        return redirect()->route('employee-leave.employee.index');
    }

    private function markReadyForDuty(EmployeeLeave $leave, ?string $note): RedirectResponse
    {
        DB::transaction(function () use ($leave, $note) {
            $leave->update([
                'status' => 'Completed',
                'offense_level' => 0,
                'ready_for_duty_notified_at' => now('Asia/Manila'),
                'last_action_note' => $note,
            ]);

            if ($leave->employee) {
                $leave->employee->update([
                    'status' => 'Active',
                ]);
            }
        });

        flash('Employee marked as Ready for Duty.')->success();

        return redirect()->route('employee-leave.employee.index');
    }

    private function decorateLeaveRows($leaves, Carbon $today): void
    {
        foreach ($leaves as $leave) {
            $rawStatus = strtolower($leave->status ?? '');
            $statusLabel = $leave->status ? ucfirst($leave->status) : 'Active';

            $statusColor = match ($rawStatus) {
                'completed' => 'success',
                'cancelled' => 'secondary',
                'terminated' => 'danger',
                'inactive' => 'warning',
                default => 'primary',
            };

            $leave->record_status_badge = '<span class="badge rounded-pill badge-subtle-'.$statusColor.' text-'.$statusColor.'">'.e($statusLabel).'</span>';

            $start = $leave->start_date
                ? Carbon::parse($leave->start_date, 'Asia/Manila')->startOfDay()
                : null;

            $end = $leave->end_date
                ? Carbon::parse($leave->end_date, 'Asia/Manila')->startOfDay()
                : null;

            if (in_array($rawStatus, ['cancelled', 'terminated', 'completed', 'inactive'], true)) {
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
                $leave->remaining_status = '<span class="badge rounded-pill badge-subtle-secondary text-secondary">No schedule</span>';

                continue;
            }

            if ($today->lt($start)) {
                $leave->remaining_status = '<span class="badge rounded-pill badge-subtle-secondary text-secondary">Not started</span>';

                continue;
            }

            if ($today->lte($end)) {
                $remainingDays = $today->diffInDays($end) + 1;
                $leave->remaining_status = '<span class="badge rounded-pill badge-subtle-success text-success">On Leave: '.$remainingDays.' day'.($remainingDays > 1 ? 's' : '').' left</span>';

                continue;
            }

            $daysAfterEnd = $end->diffInDays($today);

            if ($daysAfterEnd === 1) {
                $leave->remaining_status = '<span class="badge rounded-pill badge-subtle-primary text-primary">Ready for Duty</span>';
            } elseif ($daysAfterEnd >= 2 && $daysAfterEnd <= 9) {
                $leave->remaining_status = '<span class="badge rounded-pill badge-subtle-info text-info">Warning for 1st Notice</span>';
            } elseif ($daysAfterEnd >= 10 && $daysAfterEnd <= 22) {
                $leave->remaining_status = '<span class="badge rounded-pill badge-subtle-warning text-warning">Warning for 2nd Notice</span>';
            } else {
                $leave->remaining_status = '<span class="badge rounded-pill badge-subtle-danger text-danger">Subject for Final Notice</span>';
            }
        }
    }
}
