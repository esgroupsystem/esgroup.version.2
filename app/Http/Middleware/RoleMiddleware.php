<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // ✅ Redirect if not logged in
        if (!Auth::check()) {
            return redirect('/')
                ->withErrors(['auth' => 'You must be logged in to access this page.']);
        }

        $user = Auth::user();

        // ✅ Check if the logged-in user's role is allowed
        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized access.');
        }

        // ✅ Continue request if authorized
        return $next($request);
    }
}
