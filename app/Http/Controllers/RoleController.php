<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        return view('roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        Role::create([
            'name' => $request->name
        ]);

        flash('Role created successfully!')->success();
        return back();
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
        ]);

        $role->update([
            'name' => $request->name
        ]);

        flash('Role updated successfully!')->success();
        return back();
    }

    public function destroy($id)
    {
        Role::findOrFail($id)->delete();

        flash('Role deleted successfully!')->success();
        return back();
    }
}
