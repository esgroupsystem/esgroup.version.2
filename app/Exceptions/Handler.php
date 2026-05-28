<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The inputs that are never flashed for validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register exception handling callbacks.
     */
    public function register(): void
    {
        //
    }

    /**
     * Handle unauthenticated users.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
            ? response()->json([
                'message' => $exception->getMessage(),
            ], 401)
            : redirect()
                ->guest(route('landing'))
                ->with('error', 'Please login first.');
    }

    /**
     * Global exception renderer.
     */
    public function render($request, Throwable $exception)
    {
        // Handle HTTP errors globally
        if ($exception instanceof HttpException) {

            $status = $exception->getStatusCode();

            // 401 Unauthorized
            if ($status === 401) {
                return redirect()
                    ->back()
                    ->with('error', 'Please login first.');
            }

            // 403 Forbidden
            if ($status === 403) {
                return redirect()
                    ->back()
                    ->with('error', 'You are not allowed to access this page.');
            }

            // 404 Not Found
            if ($status === 404) {
                return redirect()
                    ->back()
                    ->with('error', 'Page not found.');
            }

            // 419 Page Expired
            if ($status === 419) {
                return redirect()
                    ->back()
                    ->with('warning', 'Session expired. Please try again.');
            }
        }

        return parent::render($request, $exception);
    }
}
