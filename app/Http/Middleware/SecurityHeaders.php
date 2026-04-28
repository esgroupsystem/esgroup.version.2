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

        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=()'
        );

        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        $csp = implode(' ', [
            "default-src 'self' https:;",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://challenges.cloudflare.com https://*.cloudflare.com;",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;",
            "font-src 'self' data: https://fonts.gstatic.com;",
            "img-src 'self' data: blob: https:;",
            "frame-src 'self' https://challenges.cloudflare.com https://*.cloudflare.com;",
            "connect-src 'self' https:;",
            "worker-src 'self' blob:;",
            "media-src 'self' data: blob:;",
            "object-src 'none';",
            "base-uri 'self';",
            "form-action 'self';",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
