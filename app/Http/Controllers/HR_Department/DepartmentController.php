<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index Routes
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $departments = Department::with('positions')->latest()->get();

            return view('hr_department.departments.index', compact('departments'));

        } catch (\Exception $e) {
            Log::error('Department Index Error: '.$e->getMessage());
            flash('Something went wrong while loading departments.')->error();
            return redirect()->back();
        }
    }

    public function positions($id)
    {
        $department = Department::with('positions')->find($id);

        if (! $department) {
            return response()->json([]);
        }

        return response()->json($department->positions);
    }

    /*
    |--------------------------------------------------------------------------
    | Saving/Create Routes
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Deleting Routes
    |--------------------------------------------------------------------------
    */

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
