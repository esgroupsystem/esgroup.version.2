<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HandleModalRedirects;
use App\Http\Middleware\PayrollGroupAccess;
use App\Providers\EventServiceProvider;
use App\Support\HttpStatusMessage;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // React pages (Inertia). Blade responses pass through untouched.
        $middleware->web(append: [
            HandleInertiaRequests::class,
            // Inner to Inertia: rewrites redirects of saves made inside a React modal.
            HandleModalRedirects::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'payroll.group' => PayrollGroupAccess::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Browser visits that fail get the React error page (pages/errors/show.tsx).
        // In-app Inertia visits and JSON calls keep the raw status (the client shows a toast),
        // and if the React page itself cannot render, Laravel's plain error page is used.
        $exceptions->respond(function (SymfonyResponse $response, Throwable $exception, Request $request): SymfonyResponse {
            $code = $response->getStatusCode();

            if ($code < 400 || $request->header('X-Inertia') || $request->expectsJson() || ($code >= 500 && config('app.debug'))) {
                return $response;
            }

            try {
                $status = HttpStatusMessage::for($code);
                $raw = trim((string) $exception->getMessage());
                $signedIn = auth()->check();

                Inertia::setRootView('app-react');

                return Inertia::render('errors/show', [
                    'status' => $status,
                    // Only deliberate abort(403|409|429, '...') messages, never internal class names.
                    'detail' => in_array($code, [403, 409, 429], true) && $raw !== '' && ! str_contains($raw, '\\') ? $raw : null,
                    'primary' => match (true) {
                        $code === 419 => ['label' => 'Refresh page', 'href' => url()->previous()],
                        $code === 503 => ['label' => 'Try again', 'href' => url('/')],
                        $signedIn => ['label' => 'Go to Dashboard', 'href' => route('dashboard.index')],
                        default => ['label' => 'Sign in', 'href' => route('login')],
                    },
                    'fallback' => $signedIn ? route('dashboard.index') : route('login'),
                    'logo' => asset('assets/img/favicons/esgroup-logo180x180.png'),
                ])->toResponse($request)->setStatusCode($code);
            } catch (Throwable) {
                return $response;
            }
        });
    })
    ->withProviders([
        EventServiceProvider::class,
    ])
    ->create();
