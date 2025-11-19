<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ForceLockscreen
{
    public function handle($request, Closure $next)
    {
        // 1. Allow login, logout and lockscreen routes
        if (
            $request->routeIs('login') ||
            $request->routeIs('login.post') ||
            $request->routeIs('logout') ||
            $request->routeIs('lockscreen.show') ||
            $request->routeIs('lockscreen.unlock')
        ) {
            return $next($request);
        }

        // 2. If not authenticated → allow (guest)
        if (!Auth::check()) {
            return $next($request);
        }

        // 3. If authenticated but not unlocked → force lockscreen
        if (!Session::get('unlocked', false)) {
            return redirect()->route('lockscreen.show');
        }

        // 4. Continue to the requested page
        return $next($request);
    }
}
