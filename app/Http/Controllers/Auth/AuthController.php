<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
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

    public function unlock(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        if (! Auth::attempt([
            'username' => Auth::user()->username,
            'password' => $request->password,
        ])) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        Session::put('unlocked', true);

        return redirect()->route('dashboard.index');
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|min:4',
                'cf-turnstile-response' => 'required|string',
            ], [
                'cf-turnstile-response.required' => 'Please complete the security verification.',
            ]);

            $turnstile = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('services.turnstile.secret_key'),
                'response' => $request->input('cf-turnstile-response'),
                'remoteip' => $request->ip(),
            ]);

            if (! $turnstile->json('success')) {
                return back()
                    ->withErrors(['turnstile' => 'Security verification failed. Please try again.'])
                    ->withInput($request->only('username', 'remember'));
            }

            $remember = $request->has('remember');

            if (Auth::attempt([
                'username' => $request->username,
                'password' => $request->password,
            ], $remember)) {
                $user = Auth::user();

                if (! in_array(strtolower($user->account_status), ['active'])) {
                    Auth::logout();
                    Session::flush();

                    flash('Your account is deactivated, please contact administrator.')->error();

                    return back()->withInput();
                }

                $user->update([
                    'status' => 'online',
                    'last_online' => now(),
                    'account_status' => 'active',
                ]);

                Session::put('unlocked', true);

                $request->session()->regenerate();

                flash('Logged in successfully!')->success();

                return redirect()->route('dashboard.index');
            }

            flash('Invalid username or password.')->error();

            return back()->withInput($request->only('username', 'remember'));

        } catch (\Exception $e) {
            Log::error('Login failed', [
                'error' => $e->getMessage(),
                'input' => $request->except('password'),
            ]);

            flash('Something went wrong while logging in.')->error();

            return back()->withInput($request->only('username', 'remember'));
        }
    }

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

        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        flash('Password updated successfully!')->success();

        return redirect()->route('dashboard.index');
    }
}
