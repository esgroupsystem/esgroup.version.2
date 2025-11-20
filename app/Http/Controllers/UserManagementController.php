<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::where('role', '!=', 'Developer')
            ->orderBy('id', 'DESC')
            ->get();

        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'role' => 'required|string',
        ]);

        $autoPassword = $this->generatePassword($request->full_name);

        User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($autoPassword),
            'account_status' => 'active',
            'must_change_password' => true,
        ]);

        flash('User created successfully!')->success();

        return redirect()->route('authentication.users.index');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'full_name' => 'required|string',
            'username' => 'required|string|unique:users,username,'.$user->id,
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|string',
        ]);

        $user->update([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'account_status' => $request->account_status,
        ]);

        flash('User updated successfully!')->success();

        return redirect()->route('authentication.users.index');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $autoPassword = $this->generatePassword($user->full_name);

        $user->update([
            'password' => Hash::make($autoPassword),
            'must_change_password' => true,
        ]);

        flash('Password reset successfully!')->success();

        return redirect()->back();
    }

    public function status($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'account_status' => $user->account_status === 'active'
                ? 'deactivated'
                : 'active',
        ]);

        flash('Account status updated!')->success();

        return redirect()->back();
    }

    private function generatePassword($fullName)
    {
        $fullName = strtolower($fullName);
        $parts = explode(' ', $fullName);

        $initials = '';
        foreach ($parts as $p) {
            if (trim($p) !== '') {
                $initials .= substr($p, 0, 1);
            }
        }

        return $initials.'123456';
    }
}
