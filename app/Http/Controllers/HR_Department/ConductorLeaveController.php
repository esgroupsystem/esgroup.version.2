<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR_Department\ConductorLeaveActionRequest;
use App\Http\Requests\HR_Department\LeaveRecordRequest;
use App\Models\ConductorLeave;
use App\Services\HR_Department\ConductorLeaveActionService;
use App\Services\HR_Department\LeaveDirectoryService;
use App\Services\HR_Department\LeaveRecordService;
use App\Support\HR\LeavePagePresenter;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Response;
use Throwable;

class ConductorLeaveController extends Controller
{
    public function __construct(
        private readonly ConductorLeaveActionService $conductorLeaveActionService,
        private readonly LeaveRecordService $leaveRecordService,
        private readonly LeaveDirectoryService $leaveDirectoryService
    ) {}

    public function index(Request $request): Response
    {
        return LeavePagePresenter::index('conductor', $this->leaveDirectoryService->conductorIndex($request), $request);
    }

    public function create(): Response
    {
        return LeavePagePresenter::form('conductor', null, $this->leaveDirectoryService->conductors());
    }

    public function store(LeaveRecordRequest $request): RedirectResponse
    {
        try {
            $this->leaveRecordService->createConductorLeave($request->validated());
            flash('Conductor leave created successfully.')->success();
        } catch (DomainException $exception) {
            flash($exception->getMessage())->warning();

            return back()->withInput();
        }

        return redirect()->route('conductor-leave.conductor.index');
    }

    public function edit(ConductorLeave $leave): Response
    {
        $leave->load(['employee.position']);

        return LeavePagePresenter::form('conductor', $leave, $this->leaveDirectoryService->conductors($leave));
    }

    public function update(
        LeaveRecordRequest $request,
        ConductorLeave $leave
    ): RedirectResponse {
        try {
            $this->leaveRecordService->updateConductorLeave($leave, $request->validated());
            flash('Conductor leave updated successfully.')->success();
        } catch (DomainException $exception) {
            flash($exception->getMessage())->warning();

            return back()->withInput();
        }

        return redirect()->route('conductor-leave.conductor.index');
    }

    public function action(
        ConductorLeaveActionRequest $request,
        ConductorLeave $leave
    ): RedirectResponse {
        try {
            $message = $this->conductorLeaveActionService->handle(
                $leave,
                (string) $request->validated('action_type'),
                $request->validated('note'),
                $request->file('proof_image')
            );

            flash($message)->success();
        } catch (DomainException $exception) {
            flash($exception->getMessage())->warning();
        } catch (Throwable $exception) {
            Log::error('Conductor leave action failed.', [
                'conductor_leave_id' => $leave->id,
                'action_type' => $request->input('action_type'),
                'user_id' => auth()->id(),
                'exception' => $exception,
            ]);

            flash('The conductor leave action could not be completed. Check the log for details.')->error();
        }

        return redirect()->route('conductor-leave.conductor.index');
    }
}
