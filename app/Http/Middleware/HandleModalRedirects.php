<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps saves made inside a modal on the page underneath it.
 *
 * React modal pages (resources/js/react/components/modal) submit with an
 * `X-Modal-Base` header: the URL of the page under the modal. When the
 * controller answers with a redirect (to the list, to a detail page, or
 * back()), the redirect goes to that base page instead, so the list refreshes
 * and the modal stays in charge. The original destination is flashed as
 * `modal_redirect`; the modal uses it to close, refresh itself, or open the
 * new record (for example "Generate payroll" -> the new payroll).
 *
 * Controllers need no changes, and a normal full-page request is unaffected.
 */
final class HandleModalRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $base = $request->headers->get('X-Modal-Base');

        if (! is_string($base) || $base === '' || ! $response instanceof RedirectResponse) {
            return $response;
        }

        // Only same-site bases; never turn this into an open redirect.
        if (! str_starts_with($base, $request->getSchemeAndHttpHost().'/')) {
            return $response;
        }

        $target = $response->getTargetUrl();

        // A redirect off to the login page (expired session) must still happen.
        if (str_starts_with($target, route('login'))) {
            return $response;
        }

        $request->session()->flash('modal_redirect', $target);
        $response->setTargetUrl($base);

        return $response;
    }
}
