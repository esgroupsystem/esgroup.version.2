<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UnlockRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Throwable;

final class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        $seconds = RateLimiter::availableIn($this->ipThrottleKey($request));

        return view('landing.login', compact('seconds'));
    }

    public function showLockscreen(): View|RedirectResponse
    {
        Session::put('unlocked', false);

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return view('landing.lockscreen');
    }

    public function unlock(UnlockRequest $request): RedirectResponse
    {
        $user = $request->user();
        $throttleKey = $this->unlockThrottleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors([
                'password' => 'Too many unlock attempts. Please try again in '.RateLimiter::availableIn($throttleKey).' seconds.',
            ]);
        }

        if (! $user || ! Hash::check((string) $request->input('password'), (string) $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors(['password' => 'Incorrect password']);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        Session::put('unlocked', true);
        Session::put('last_activity_time', now()->timestamp);

        return redirect()->route('dashboard.index');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $ipThrottleKey = $this->ipThrottleKey($request);
        $usernameThrottleKey = $this->usernameThrottleKey($request);

        if (RateLimiter::tooManyAttempts($ipThrottleKey, 20) || RateLimiter::tooManyAttempts($usernameThrottleKey, 5)) {
            $seconds = max(
                RateLimiter::availableIn($ipThrottleKey),
                RateLimiter::availableIn($usernameThrottleKey)
            );

            return back()
                ->with('throttle', $seconds)
                ->withInput($request->only('username', 'remember'));
        }

        try {
            $turnstile = Http::asForm()
                ->timeout(5)
                ->connectTimeout(3)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => config('services.turnstile.secret_key'),
                    'response' => $request->string('cf-turnstile-response')->toString(),
                    'remoteip' => $request->ip(),
                ]);

            if (! $turnstile->successful() || ! $turnstile->json('success')) {
                RateLimiter::hit($ipThrottleKey, 15);
                RateLimiter::hit($usernameThrottleKey, 60);

                return back()
                    ->withErrors(['turnstile' => 'Security verification failed. Please try again.'])
                    ->withInput($request->only('username', 'remember'));
            }

            $credentials = [
                'username' => $request->string('username')->toString(),
                'password' => $request->string('password')->toString(),
                'account_status' => 'active',
            ];

            if (! Auth::attempt($credentials, $request->boolean('remember'))) {
                RateLimiter::hit($ipThrottleKey, 15);
                RateLimiter::hit($usernameThrottleKey, 60);

                flash('Invalid username or password.')->error();

                return back()->withInput($request->only('username', 'remember'));
            }

            RateLimiter::clear($ipThrottleKey);
            RateLimiter::clear($usernameThrottleKey);

            $request->session()->regenerate();
            Session::put('unlocked', true);
            Session::put('last_activity_time', now()->timestamp);

            $user = $request->user();
            $user?->update([
                'status' => 'online',
                'last_online' => now(),
            ]);

            flash('Logged in successfully!')->success();

            return redirect()->route('dashboard.index');
        } catch (Throwable $e) {
            Log::error('Login failed', [
                'message' => $e->getMessage(),
                'ip' => $request->ip(),
                'username' => $request->string('username')->toString(),
            ]);

            RateLimiter::hit($ipThrottleKey, 15);
            RateLimiter::hit($usernameThrottleKey, 60);

            flash('Something went wrong while logging in.')->error();

            return back()->withInput($request->only('username', 'remember'));
        }
    }

    public function logout(Request $request): RedirectResponse
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
            Session::forget('last_activity_time');
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            flash('Logged out successfully!')->success();

            return redirect()->route('login');
        } catch (Throwable $e) {
            Log::error('Logout failed', ['message' => $e->getMessage()]);

            return redirect()->route('login');
        }
    }

    public function changePasswordForm(): View
    {
        return view('auth.change-password');
    }

    public function changePasswordUpdate(ChangePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->string('password')->toString()),
            'must_change_password' => false,
        ]);

        // A password change invalidates mobile/API bearer tokens so stolen tokens cannot survive it.
        $user->tokens()->delete();

        $request->session()->regenerate();
        Session::put('unlocked', true);
        Session::put('last_activity_time', now()->timestamp);

        flash('Password updated successfully!')->success();

        return redirect()->route('dashboard.index');
    }

    private function ipThrottleKey(Request $request): string
    {
        return 'login:ip:'.$request->ip();
    }

    private function usernameThrottleKey(Request $request): string
    {
        return 'login:user:'.sha1(strtolower(trim($request->string('username')->toString())));
    }

    private function unlockThrottleKey(Request $request): string
    {
        return 'unlock:user:'.($request->user()?->getAuthIdentifier() ?? 'unknown').':ip:'.$request->ip();
    }
}
