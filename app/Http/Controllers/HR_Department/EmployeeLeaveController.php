<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR_Department\EmployeeLeaveActionRequest;
use App\Http\Requests\HR_Department\LeaveRecordRequest;
use App\Models\EmployeeLeave;
use App\Services\HR_Department\EmployeeLeaveActionService;
use App\Services\HR_Department\LeaveDirectoryService;
use App\Services\HR_Department\LeaveRecordService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmployeeLeaveController extends Controller
{
    public function __construct(
        private readonly EmployeeLeaveActionService $employeeLeaveActionService,
        private readonly LeaveRecordService $leaveRecordService,
        private readonly LeaveDirectoryService $leaveDirectoryService
    ) {}

    public function index(Request $request): View
    {
        $data = $this->leaveDirectoryService->employeeIndex($request);

        if ($request->ajax()) {
            return view('hr_department.leaves.employee.table', [
                'leaves' => $data['leaves'],
                'today' => $data['today'],
            ]);
        }

        return view('hr_department.leaves.employee.index', $data);
    }

    public function create(): View
    {
        return view('hr_department.leaves.employee.create', [
            'employees' => $this->leaveDirectoryService->employees(),
        ]);
    }

    public function store(LeaveRecordRequest $request): RedirectResponse
    {
        try {
            $this->leaveRecordService->createEmployeeLeave($request->validated());
            flash('Employee leave created successfully.')->success();
        } catch (DomainException $exception) {
            flash($exception->getMessage())->warning();

            return back()->withInput();
        }

        return redirect()->route('employee-leave.employee.index');
    }

    public function edit(EmployeeLeave $leave): View
    {
        $leave->load(['employee.position']);

        return view('hr_department.leaves.employee.edit', [
            'leave' => $leave,
            'employees' => $this->leaveDirectoryService->employees($leave),
        ]);
    }

    public function update(
        LeaveRecordRequest $request,
        EmployeeLeave $leave
    ): RedirectResponse {
        try {
            $this->leaveRecordService->updateEmployeeLeave($leave, $request->validated());
            flash('Employee leave updated successfully.')->success();
        } catch (DomainException $exception) {
            flash($exception->getMessage())->warning();

            return back()->withInput();
        }

        return redirect()->route('employee-leave.employee.index');
    }

    public function action(
        EmployeeLeaveActionRequest $request,
        EmployeeLeave $leave
    ): RedirectResponse {
        $validated = $request->validated();

        try {
            $message = $this->employeeLeaveActionService->handle(
                $leave,
                (string) $validated['action_type'],
                $validated['note'] ?? null,
                $request->file('proof_image')
            );

            flash($message)->success();
        } catch (DomainException $exception) {
            flash($exception->getMessage())->warning();
        } catch (Throwable $exception) {
            Log::error('Employee leave action failed.', [
                'employee_leave_id' => $leave->id,
                'action_type' => $request->input('action_type'),
                'user_id' => auth()->id(),
                'exception' => $exception,
            ]);

            flash(
                'The employee leave action could not be completed. Check the application log for details.'
            )->error();
        }

        return redirect()->route('employee-leave.employee.index');
    }
}
