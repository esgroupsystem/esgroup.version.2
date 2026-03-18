<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Mail\LeaveNoticeMail;
use App\Models\ConductorLeave;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ConductorLeaveController extends Controller
{
    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Manila');

        $today = Carbon::now('Asia/Manila')->startOfDay();
        $search = $request->get('search');

        $baseQuery = ConductorLeave::with('employee');

        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($qq) use ($search) {
                    $qq->where('full_name', 'like', "%{$search}%");
                })
                ->orWhere('leave_type', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $leaves = (clone $baseQuery)
            ->orderByRaw("
                CASE
                    WHEN status IS NULL OR status = '' THEN 1
                    WHEN status = 'active' THEN 1
                    WHEN status = 'on_leave' THEN 1
                    WHEN status = 'completed' THEN 2
                    WHEN status = 'cancelled' THEN 3
                    WHEN status = 'terminated' THEN 3
                    ELSE 2
                END ASC
            ")
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        foreach ($leaves as $leave) {
            $rawStatus = strtolower($leave->status ?? '');

            $statusLabel = $leave->status ? ucfirst($leave->status) : 'Active';
            $statusColor = match ($rawStatus) {
                'completed' => 'success',
                'cancelled' => 'secondary',
                'terminated' => 'danger',
                default => 'primary',
            };

            $leave->record_status_badge = '<span class="badge bg-' . $statusColor . '">' . e($statusLabel) . '</span>';

            $start = $leave->start_date
                ? Carbon::parse($leave->start_date, 'Asia/Manila')->startOfDay()
                : null;

            $end = $leave->end_date
                ? Carbon::parse($leave->end_date, 'Asia/Manila')->startOfDay()
                : null;

            if (in_array($rawStatus, ['cancelled', 'terminated', 'completed'], true)) {
                $leave->remaining_status = match ($rawStatus) {
                    'completed' => '<span class="badge bg-success">Completed</span>',
                    'cancelled' => '<span class="badge bg-secondary">Cancelled</span>',
                    'terminated' => '<span class="badge bg-danger">Terminated</span>',
                    default => '<span class="badge bg-secondary">N/A</span>',
                };

                continue;
            }

            if (!$start || !$end) {
                $leave->remaining_status = '<span class="badge bg-secondary">No schedule</span>';
                continue;
            }

            if ($today->lt($start)) {
                $leave->remaining_status = '<span class="badge bg-secondary">Not started</span>';
                continue;
            }

            if ($today->lte($end)) {
                $remainingDays = $today->diffInDays($end) + 1;
                $leave->remaining_status =
                    '<span class="badge bg-success">On Leave (' . $remainingDays . ' day' . ($remainingDays > 1 ? 's' : '') . ' left)</span>';
                continue;
            }

            $daysAfterEnd = $end->diffInDays($today);

            if ($daysAfterEnd == 1) {
                $leave->remaining_status = '<span class="badge bg-primary">Ready for Duty</span>';
            } elseif ($daysAfterEnd >= 2 && $daysAfterEnd <= 9) {
                $leave->remaining_status = '<span class="badge bg-info">Warning for 1st Notice</span>';
            } elseif ($daysAfterEnd >= 10 && $daysAfterEnd <= 22) {
                $leave->remaining_status = '<span class="badge bg-warning text-dark">Warning for 2nd Notice</span>';
            } else {
                $leave->remaining_status = '<span class="badge bg-danger">Subject for Termination</span>';
            }
        }

        $allForCounts = (clone $baseQuery)->get();

        $counts = [
            'active' => 0,
            'first' => 0,
            'second' => 0,
            'termination' => 0,
        ];

        foreach ($allForCounts as $l) {
            $level = (int) ($l->offense_level ?? 0);
            $status = strtolower($l->status ?? '');

            if ($level === 1) {
                $counts['first']++;
            } elseif ($level === 2) {
                $counts['second']++;
            } elseif ($level >= 3) {
                $counts['termination']++;
            } else {
                if (!in_array($status, ['cancelled', 'terminated', 'completed'], true)) {
                    $counts['active']++;
                }
            }
        }

        if ($request->ajax()) {
            return view('hr_department.leaves.conductor.table', compact('leaves', 'today'));
        }

        return view('hr_department.leaves.conductor.index', compact('leaves', 'counts', 'today'));
    }

    public function action(Request $request, ConductorLeave $leave)
    {
        $validator = Validator::make($request->all(), [
            'action_type' => 'required|in:first,second,terminate,cancel,ready',
            'note' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $action = $request->action_type;
        $note = $request->note;
        $employee = $leave->employee;

        if ($action === 'first') {

            if ($leave->first_notice_sent_at) {
                flash('1st Notice already sent.')->info();
                return back();
            }

            $leave->first_notice_sent_at = now('Asia/Manila');
            $leave->offense_level = 1;
            $leave->last_action_note = $note;
            $leave->save();

            flash('1st Notice marked as Sent.')->success();

        } elseif ($action === 'second') {

            if (!$leave->first_notice_sent_at) {
                flash('Send 1st Notice first.')->warning();
                return back();
            }

            if ($leave->second_notice_sent_at) {
                flash('2nd Notice already sent.')->info();
                return back();
            }

            $leave->second_notice_sent_at = now('Asia/Manila');
            $leave->offense_level = 2;
            $leave->last_action_note = $note;
            $leave->save();

            flash('2nd Notice marked as Sent.')->success();

        } elseif ($action === 'terminate') {

            if (!$leave->second_notice_sent_at) {
                flash('Send 2nd Notice first.')->warning();
                return back();
            }

            if ($leave->final_notice_sent_at) {
                flash('Final Notice already sent.')->info();
                return back();
            }

            $leave->final_notice_sent_at = now('Asia/Manila');
            $leave->offense_level = 3;
            $leave->status = 'terminated';
            $leave->last_action_note = $note;
            $leave->save();

            if ($employee) {
                $employee->update(['status' => 'Terminated']);
            }

            flash('Final Notice marked as Sent (Termination).')->success();

        } elseif ($action === 'cancel') {

            $leave->status = 'cancelled';
            $leave->last_action_note = $note;
            $leave->save();

            if ($employee) {
                $employee->status = 'Active';
                $employee->save();
            }

            flash('Leave cancelled & employee returned to Active.')->success();

        } elseif ($action === 'ready') {

            if ($leave->ready_for_duty_notified_at) {
                flash('Ready for Duty already notified.')->info();
                return back();
            }

            $leave->status = 'completed';
            $leave->last_action_note = $note;
            $leave->ready_for_duty_notified_at = now('Asia/Manila');
            $leave->save();

            if ($employee) {
                $employee->status = 'Active';
                $employee->save();
            }

            flash('Employee marked as Ready for Duty.')->success();
        }

        return redirect()->route('conductor-leave.conductor.index');
    }

    public function create()
    {
        $conductors = Employee::whereHas('position', function ($q) {
            $q->where('title', 'Conductor');
        })
        ->where('status', 'Active')
        ->get();

        return view('hr_department.leaves.conductor.create', compact('conductors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'leave_type' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $days = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

        $leave = ConductorLeave::create([
            'employee_id' => $request->employee_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days' => $days,
            'reason' => $request->reason,
            'status' => 'Active',
        ]);

        $employee = Employee::find($request->employee_id);
        if ($employee) {
            $employee->status = 'On Leave';
            $employee->save();
        }

        flash('Conductor Leave Created Successfully!')->success();

        return redirect()->route('conductor-leave.conductor.index');
    }

    public function edit(ConductorLeave $leave)
    {
        $conductors = Employee::whereHas('position', function ($q) {
            $q->where('title', 'Conductor');
        })->get();

        return view('hr_department.leaves.conductor.edit', compact('leave', 'conductors'));
    }

    public function update(Request $request, ConductorLeave $leave)
    {
        $request->validate([
            'employee_id' => 'required',
            'leave_type' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $days = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

        $leave->update([
            'employee_id' => $request->employee_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days' => $days,
            'reason' => $request->reason,
        ]);


        flash('Leave updated successfully')->success();

        return redirect()->route('conductor-leave.conductor.index');
    }
}