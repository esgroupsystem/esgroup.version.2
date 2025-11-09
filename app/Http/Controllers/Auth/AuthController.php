<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;


class AuthController extends Controller
{
    // Show login page
    public function showLogin()
    {
        return view('landing.landing');
    }

    // Handle login form
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|min:4',
            ]);

            $remember = $request->has('remember');

            if (Auth::attempt([
                'username' => $request->username,
                'password' => $request->password
            ], $remember)) {

                $request->session()->regenerate();

                flash('Logged in successfully!')->success();
                return redirect()->route('dashboard.index');
            }

            flash('Invalid username or password.')->error();
            return back()->withInput();

        } catch (\Exception $e) {
            Log::error('Login failed', ['error' => $e->getMessage(),'input' => $request->except('password'),]);
            flash('Something went wrong while logging in.')->error();
            return back()->withInput();
        }
    }

    // Handle registration
    public function register(Request $request)
    {
        try {

            $request->validate([
                'full_name' => 'required|string|max:255',
                'username'  => 'required|string|max:255|unique:users',
                'email'     => 'required|email|unique:users',
                'password'  => 'required|min:4|confirmed',
            ]);

            User::create([
                'full_name'      => $request->full_name,
                'username'       => $request->username,
                'email'          => $request->email,
                'password'       => Hash::make($request->password),
                'role'           => 'Developer',
                'status'         => 'offline',
                'account_status' => 'active',
            ]);

            flash('Account created successfully!')->success();
            return redirect()->back();

        } catch (\Exception $e) {
            flash('Something went wrong while creating the account.')->error();
            return redirect()->back()->withInput();
        }
    }

    // Logout
    public function logout(Request $request)
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
                $user->update([
                    'last_out' => now(),
                    'status'   => 'offline',
                ]);
            }
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            flash('Logged out successfully!')->success();

            return redirect('/');
            
        } catch (\Exception $e) {

            Log::error('Logout failed', [
                'error' => $e->getMessage(),
                'user' => Auth::user()->id ?? null
            ]);

            flash('Logout failed. Please try again.')->error();

            return redirect()->back();
        }
    }

}
