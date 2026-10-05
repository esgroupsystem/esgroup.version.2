<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmployeeHistoryRequest;
use App\Models\Employee;
use App\Services\HR\EmployeeHistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Violation history (IR cases) on the employee 201 profile.
 */
final class EmployeeHistoryController extends Controller
{
    public function __construct(
        private readonly EmployeeHistoryService $histories,
    ) {}

    public function store(EmployeeHistoryRequest $request, Employee $employee): RedirectResponse
    {
        try {
            $this->histories->createViolation($employee, $request->validated());
            flash('Violation history added successfully!')->success();

            return redirect()->route('employees.staff.show', $employee->id);
        } catch (Throwable $exception) {
            Log::error('Employee history store failed', ['employee_id' => $employee->id, 'message' => $exception->getMessage()]);
            flash('Unable to add violation history. Please try again.')->error();

            return back()->withInput();
        }
    }

    public function update(EmployeeHistoryRequest $request, Employee $employee, int $history): RedirectResponse
    {
        try {
            $this->histories->updateViolation($employee, $history, $request->validated());
            flash('Violation history updated successfully!')->success();

            return redirect()->route('employees.staff.show', $employee->id);
        } catch (Throwable $exception) {
            Log::error('Employee history update failed', ['employee_id' => $employee->id, 'history_id' => $history, 'message' => $exception->getMessage()]);
            flash('Unable to update violation history. Please try again.')->error();

            return back()->withInput();
        }
    }

    public function destroy(Employee $employee, int $history): RedirectResponse
    {
        try {
            $this->histories->delete($employee, $history);
            flash('History removed successfully!')->success();
        } catch (Throwable $exception) {
            Log::error('Employee history delete failed', ['employee_id' => $employee->id, 'history_id' => $history, 'message' => $exception->getMessage()]);
            flash('Unable to remove history.')->error();
        }

        return redirect()->route('employees.staff.show', $employee->id);
    }
}
