<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR_Department\DriverLeaveActionRequest;
use App\Http\Requests\HR_Department\LeaveRecordRequest;
use App\Models\DriverLeave;
use App\Services\HR_Department\DriverLeaveActionService;
use App\Services\HR_Department\LeaveDirectoryService;
use App\Services\HR_Department\LeaveRecordService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class DriverLeaveController extends Controller
{
    public function __construct(
        private readonly DriverLeaveActionService $driverLeaveActionService,
        private readonly LeaveRecordService $leaveRecordService,
        private readonly LeaveDirectoryService $leaveDirectoryService
    ) {}

    public function index(Request $request): View
    {
        $data = $this->leaveDirectoryService->driverIndex($request);

        if ($request->ajax()) {
            return view('hr_department.leaves.driver.table', [
                'leaves' => $data['leaves'],
                'today' => $data['today'],
            ]);
        }

        return view('hr_department.leaves.driver.index', $data);
    }

    public function create(): View
    {
        return view('hr_department.leaves.driver.create', [
            'drivers' => $this->leaveDirectoryService->drivers(),
        ]);
    }

    public function store(LeaveRecordRequest $request): RedirectResponse
    {
        try {
            $this->leaveRecordService->createDriverLeave($request->validated());
            flash('Driver leave created successfully.')->success();
        } catch (DomainException $exception) {
            flash($exception->getMessage())->warning();

            return back()->withInput();
        }

        return redirect()->route('driver-leave.driver.index');
    }

    public function edit(DriverLeave $leave): View
    {
        $leave->load(['employee.position']);

        return view('hr_department.leaves.driver.edit', [
            'leave' => $leave,
            'drivers' => $this->leaveDirectoryService->drivers($leave),
        ]);
    }

    public function update(
        LeaveRecordRequest $request,
        DriverLeave $leave
    ): RedirectResponse {
        try {
            $this->leaveRecordService->updateDriverLeave($leave, $request->validated());
            flash('Driver leave updated successfully.')->success();
        } catch (DomainException $exception) {
            flash($exception->getMessage())->warning();

            return back()->withInput();
        }

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
}
