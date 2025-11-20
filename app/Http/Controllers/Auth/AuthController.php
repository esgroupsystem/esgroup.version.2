<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show Login Page
    |--------------------------------------------------------------------------
    */
    public function showLogin()
    {
        return view('landing.login');
    }

    public function showLockscreen()
    {
        Session::put('unlocked', false);

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return view('landing.lockscreen');
    }

    /*
    |--------------------------------------------------------------------------
    | Unlock Screen
    |--------------------------------------------------------------------------
    */
    public function unlock(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        // Validate unlock password using the current logged user
        if (! Auth::attempt([
            'username' => Auth::user()->username,
            'password' => $request->password,
        ])) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        // Unlock success
        Session::put('unlocked', true);

        return redirect()->route('dashboard.index');
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */
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
                'password' => $request->password,
            ], $remember)) {

                $user = Auth::user();

                // Save login activity
                $user->update([
                    'status' => 'online',
                    'last_online' => now(),
                    'account_status' => 'active',
                ]);

                // Unlock dashboard
                Session::put('unlocked', true);

                $request->session()->regenerate();

                flash('Logged in successfully!')->success();

                return redirect()->route('dashboard.index');
            }

            flash('Invalid username or password.')->error();

            return back()->withInput();

        } catch (\Exception $e) {

            Log::error('Login failed', [
                'error' => $e->getMessage(),
                'input' => $request->except('password'),
            ]);

            flash('Something went wrong while logging in.')->error();

            return back()->withInput();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'full_name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:4|confirmed',
            ]);

            User::create([
                'full_name' => $request->full_name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'Developer',
                'status' => 'offline',
                'account_status' => 'active',
            ]);

            flash('Account created successfully!')->success();

            return redirect()->back();

        } catch (\Exception $e) {

            flash('Something went wrong while creating the account.')->error();

            return redirect()->back()->withInput();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */
    public function logout(Request $request)
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();

                $user->update([
                    'last_out' => now(),
                    'status' => 'offline',
                ]);
            }

            Session::forget('unlocked');

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            flash('Logged out successfully!')->success();

            return redirect('/');

        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
                'user' => Auth::user()->id ?? null,
            ]);

            flash('Logout failed. Please try again.')->error();

            return redirect()->back();
        }
    }

    public function changePasswordForm()
    {
        return view('auth.change-password');
    }

    public function changePasswordUpdate(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        flash('Password updated successfully!')->success();

        return redirect()->route('dashboard.index');
    }
}
