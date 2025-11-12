<?php

namespace App\Http\Controllers\HR_Department;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('positions')->latest()->get();
        return view('hr_department.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        Department::create(['name' => $request->name]);
        return back()->with('success', 'Department added successfully!');
    }

    public function storePosition(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'title' => 'required|string|max:255',
        ]);

        Position::create($request->only('department_id', 'title'));
        return back()->with('success', 'Position added successfully!');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return back()->with('success', 'Department deleted successfully!');
    }

    public function destroyPosition(Position $position)
    {
        $position->delete();
        return back()->with('success', 'Position deleted successfully!');
    }
}
