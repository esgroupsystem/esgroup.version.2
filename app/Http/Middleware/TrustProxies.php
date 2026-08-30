<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Trust Cloudflare / hosting proxy headers.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = null;

    public function __construct()
    {
        $this->proxies = config('security.trusted_proxies');
    }

    /**
     * Headers used to detect original request data behind proxy.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO;
}
