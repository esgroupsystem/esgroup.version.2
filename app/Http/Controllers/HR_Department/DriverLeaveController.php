<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\DriverLeave;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DriverLeaveController extends Controller
{
    public function index()
    {
        // Force PH time
        date_default_timezone_set('Asia/Manila');

        $today = Carbon::now('Asia/Manila')->startOfDay();

        // ⭐ Load all leaves EXCEPT completed
        $leaves = DriverLeave::with('employee')
            ->where(function ($q) {
                $q->where('status', '!=', 'completed')
                    ->orWhereNull('status');
            })
            ->latest()
            ->get();

        // ⭐ Add remaining status
        foreach ($leaves as $leave) {

            $start = $leave->start_date
                ? Carbon::parse($leave->start_date)->startOfDay()
                : null;

            $end = $leave->end_date
                ? Carbon::parse($leave->end_date)->startOfDay()
                : null;

            if ($start && $today->lt($start)) {
                // BEFORE START DATE
                $leave->remaining_status = '<span class="badge bg-secondary">Not started</span>';

            } elseif ($end && $today->gt($end)) {
                // AFTER END DATE
                $leave->remaining_status = '<span class="text-muted">Expired</span>';

            } else {
                // STARTS TODAY OR ONGOING
                $remaining_days = $end ? ($today->diffInDays($end) + 1) : 1;

                $leave->remaining_status =
                    '<span class="badge bg-success">'.$remaining_days.' days</span>';
            }
        }

        // ⭐ dashboard counts
        $counts = [
            'active' => 0,
            'first' => 0,
            'second' => 0,
            'termination' => 0,
        ];

        foreach ($leaves as $l) {

            $level = $l->offense_level ?? $l->offense ?? null;
            $status = strtolower($l->status ?? '');

            if ($level == 1) {
                $counts['first']++;
            } elseif ($level == 2) {
                $counts['second']++;
            } elseif ($level >= 3) {
                $counts['termination']++;
            } else {
                if ($status !== 'cancelled' && $status !== 'terminated') {
                    $counts['active']++;
                }
            }
        }

        return view('hr_department.leaves.driver.index', compact('leaves', 'counts', 'today'));
    }

    /**
     * Handle modal submission to apply action to a leave.
     * action_type: first | second | terminate | cancel
     */
    public function action(Request $request, DriverLeave $leave)
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

        // ⭐ Load employee
        $employee = $leave->employee;

        if ($action === 'first') {
            $leave->offense_level = 1;
            $leave->last_action_note = $note;
            $leave->save();

            flash('1st Offense recorded.')->success();

        } elseif ($action === 'second') {
            $leave->offense_level = 2;
            $leave->last_action_note = $note;
            $leave->save();

            flash('2nd Offense recorded.')->success();

        } elseif ($action === 'terminate') {
            $leave->offense_level = 3;
            $leave->status = 'terminated';
            $leave->last_action_note = $note;
            $leave->save();

            if ($employee) {
                $employee->status = 'Terminated';
                $employee->save();
            }

            flash('Employee termination recorded.')->success();

        } elseif ($action === 'cancel') {
            $leave->status = 'cancelled';
            $leave->last_action_note = $note;
            $leave->save();

            // ⭐ EMPLOYEE BACK TO ACTIVE
            if ($employee) {
                $employee->status = 'Active';
                $employee->save();
            }

            flash('Leave cancelled & employee returned to Active.')->success();
        } elseif ($action === 'ready') {

            // ⭐ READY FOR DUTY
            $leave->status = 'completed';
            $leave->last_action_note = $note;
            $leave->save();

            if ($employee) {
                $employee->status = 'Active';
                $employee->save();
            }

            flash('Employee marked as Ready for Duty.')->success();
        }

        return redirect()->route('driver-leave.driver.index');
    }

    public function create()
    {
        $drivers = Employee::whereHas('position', function ($q) {
            $q->where('title', 'Driver');
        })
            ->where('status', 'Active')   // ⭐ Only active drivers allowed
            ->get();

        return view('hr_department.leaves.driver.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'leave_type' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $days = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

        $leave = DriverLeave::create([
            'employee_id' => $request->employee_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days' => $days,
            'reason' => $request->reason,
        ]);

        // ⭐ UPDATE EMPLOYEE STATUS TO "On Leave"
        $employee = Employee::find($request->employee_id);
        if ($employee) {
            $employee->status = 'On Leave';
            $employee->save();
        }

        flash('Driver Leave Created Successfully!')->success();

        return redirect()->route('driver-leave.driver.index');
    }

    public function edit(DriverLeave $leave)
    {
        $drivers = Employee::whereHas('position', function ($q) {
            $q->where('title', 'Driver');
        })->get();

        return view('hr_department.leaves.driver.edit', compact('leave', 'drivers'));
    }

    public function update(Request $request, DriverLeave $leave)
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

        return redirect()->route('driver-leave.driver.index');
    }
}
