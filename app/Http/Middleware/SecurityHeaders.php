<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), xr-spatial-tracking=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        $csp = implode(' ', [
            "default-src 'self';",
            "base-uri 'self';",
            "form-action 'self';",
            "img-src 'self' data: blob: https:;",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://challenges.cloudflare.com https://static.cloudflareinsights.com;",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;",
            "font-src 'self' data: https://fonts.gstatic.com;",
            'frame-src https://challenges.cloudflare.com;',
            "connect-src 'self' https://challenges.cloudflare.com https://static.cloudflareinsights.com;",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
