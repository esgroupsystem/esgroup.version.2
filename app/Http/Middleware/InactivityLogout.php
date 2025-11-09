<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InactivityLogout
{
    public function handle(Request $request, Closure $next, $minutes = 30)
    {
        if (Auth::check()) {
            $last = session('last_activity_time');
            $now  = now()->timestamp;

            if ($last && ($now - $last) > ($minutes * 60)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/')
                    ->with('error', 'You were logged out due to inactivity.');
            }

            session(['last_activity_time' => $now]);
        }
        return $next($request);
    }
}

