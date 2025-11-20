<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->must_change_password) {
            if (! $request->is('change-password') && ! $request->is('change-password/*')) {
                return redirect()->route('auth.change.password.form');
            }
        }

        return $next($request);
    }
}
