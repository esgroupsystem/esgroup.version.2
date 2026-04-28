<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $nonce = base64_encode(random_bytes(16));
        app()->instance('csp_nonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        $isAuthPage = $request->is('login') || $request->is('lockscreen');

        if ($isAuthPage) {
            $csp = implode(' ', [
                "default-src 'self';",
                "base-uri 'self';",
                "form-action 'self';",
                "img-src 'self' data: blob: https:;",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;",
                "font-src 'self' data: https://fonts.gstatic.com;",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://challenges.cloudflare.com https://static.cloudflareinsights.com;",
                'frame-src https://challenges.cloudflare.com;',
                "connect-src 'self' https://challenges.cloudflare.com https://static.cloudflareinsights.com;",
            ]);
        } else {
            $csp = implode(' ', [
                "default-src 'self';",
                "base-uri 'self';",
                "form-action 'self';",
                "img-src 'self' data: blob: https:;",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;",
                "font-src 'self' data: https://fonts.gstatic.com;",
                "script-src 'self' 'nonce-{$nonce}' https://static.cloudflareinsights.com;",
                "connect-src 'self' https://static.cloudflareinsights.com;",
            ]);
        }

        $response->headers->remove('Require-Trusted-Types-For');
        $response->headers->remove('Trusted-Types');
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
