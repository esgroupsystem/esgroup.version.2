<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Navigation\MainNavigation;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    /**
     * Root template for every React page, including login, the lock screen and
     * error pages. Only server PDFs, a few print views and emails are Blade.
     */
    protected $rootView = 'app-react';

    /**
     * When a React page makes an Inertia visit that ends on a Blade page (a print page, a download, ...), the client
     * would otherwise show that HTML in an error modal. Turn it into a full
     * page load instead. Error statuses (4xx/5xx) are left alone: the client
     * turns them into a toast (resources/js/react/lib/notify.ts) and stays on
     * the page, instead of re-requesting a POST URL with GET.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = parent::handle($request, $next);

        if (
            $request->header('X-Inertia')
            && ! $response->headers->has('X-Inertia')
            && ! $response->isRedirection()
            && $response->getStatusCode() < 400
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
        ) {
            return Inertia::location($request->fullUrl());
        }

        return $response;
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'appName' => 'Jell Group',
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->full_name ?? $user->name ?? $user->username ?? 'User',
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->getRoleNames()->first() ?? $user->role,
                    'account_status' => $user->account_status ?? 'active',
                    'must_change_password' => (bool) ($user->must_change_password ?? false),
                    'last_online' => $user->last_online
                        ? Carbon::parse($user->last_online)->timezone('Asia/Manila')->toIso8601String()
                        : null,
                ] : null,
            ],
            'navigation' => fn () => MainNavigation::for($user, $request),
            'routes' => [
                'logout' => route('logout'),
                'changePassword' => route('auth.change.password.update'),
                'lock' => route('lockscreen.lock'),
            ],
            // Where a save made inside a React modal wanted to go (HandleModalRedirects).
            'modal' => [
                'redirect' => fn () => $request->session()->get('modal_redirect'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
                // Messages from the flash() helper (laracasts/flash).
                'messages' => fn () => collect($request->session()->get('flash_notification', []))
                    ->map(fn ($message): array => [
                        'message' => (string) data_get($message, 'message'),
                        'level' => (string) data_get($message, 'level', 'info'),
                    ])
                    ->values()
                    ->all(),
            ],
        ];
    }
}
