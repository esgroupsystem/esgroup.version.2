<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    /**
     * Display all employees.
     */
    public function index()
    {
        try {
            $employees = Employee::with(['department', 'position'])->latest()->paginate(10);
            $departments = Department::with('positions')->get();

            return view('hr_department.index', compact('employees', 'departments'));
        } catch (\Exception $e) {
            Log::error('Failed to load employee list', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Failed to load employee list.')->error();
            return back();
        }
    }

    /**
     * Store a new employee.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'full_name' => 'required|string|max:255',
                'department_id' => 'nullable|exists:departments,id',
                'position_id' => 'nullable|exists:positions,id',
                'email' => 'nullable|email|max:255',
                'phone_number' => 'nullable|string|max:20',
                'company' => 'required|in:MIRASOL,BALINTAWAK',
            ]);

            $lastId = Employee::latest()->value('id') ?? 0;
            $employee_id = 'EMP-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            Employee::create([
                'employee_id'   => $employee_id,
                'full_name'     => $request->full_name,
                'department_id' => $request->department_id,
                'position_id'   => $request->position_id,
                'email'         => $request->email,
                'phone_number'  => $request->phone_number,
                'company'       => $request->company,
            ]);

            flash('Employee added successfully!')->success();
            return back();
        } catch (ValidationException $e) {
            flash('Validation failed. Please check the input fields.')->warning();

            Log::warning('Employee validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);

            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error adding employee', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Something went wrong while adding the employee.')->error();
            return back()->withInput();
        }
    }

    /**
     * Delete employee.
     */
    public function destroy(Employee $employee)
    {
        try {
            $employee->delete();

            flash('Employee deleted successfully!')->success();
            return back();
        } catch (\Exception $e) {
            Log::error('Failed to delete employee', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Something went wrong while deleting the employee.')->error();
            return back();
        }
    }
}
