<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * A locked session (session "unlocked" = false) gets no data at all:
 *   - page requests render the React lock screen (blurred screen + password),
 *     remembering the page so unlocking returns to it;
 *   - writes are refused and bounce back to that lock screen;
 *   - JSON requests get 423 Locked.
 */
class ForceLockscreen
{
    public const INTENDED = 'lock_intended';

    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->routeIs('login', 'login.post', 'logout', 'lockscreen.show', 'lockscreen.unlock', 'lockscreen.lock')
            || ! Auth::check()
            || Session::get('unlocked', false)
        ) {
            return $next($request);
        }

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['message' => 'Your session is locked. Enter your password to continue.'], 423);
        }

        if (! $request->isMethod('GET')) {
            return redirect()->route('lockscreen.show');
        }

        Session::put(self::INTENDED, $request->fullUrl());

        return self::render($request);
    }

    /** The lock screen page (also used by GET /lockscreen). */
    public static function render(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('auth/lock', [
            'user' => [
                'name' => $user?->full_name ?: ($user?->name ?: $user?->username),
                'username' => $user?->username,
            ],
            'urls' => [
                'unlock' => route('lockscreen.unlock'),
                'logout' => route('logout'),
            ],
        ])->toResponse($request);
    }
}
