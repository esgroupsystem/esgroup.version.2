<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Links an HR employee to its biometric record (or unlinks it) from the employee profile. */
class EmployeeBiometricLinkController extends Controller
{
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'employee_biometric_id' => [
                'nullable',
                'integer',
                'exists:employee_biometrics,id',
                Rule::unique('employees', 'employee_biometric_id')->ignore($employee->id),
            ],
        ], [
            'employee_biometric_id.unique' => 'That biometric record is already linked to another employee. Unlink it there first.',
        ]);

        $biometricId = $validated['employee_biometric_id'] ?? null;
        $employee->update(['employee_biometric_id' => $biometricId]);

        return back()->with('success', $biometricId
            ? 'Employee linked to the biometric record.'
            : 'Biometric link removed.');
    }
}
