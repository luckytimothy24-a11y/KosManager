<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Resolve the trusted proxies, falling back to the "TRUSTED_PROXIES"
     * configuration so deployments behind a reverse proxy can opt-in.
     */
    protected function proxies()
    {
        $value = $this->proxies ?? config('app.trusted_proxies');

        if (is_string($value) && $value !== '') {
            return $value === '*'
                ? '*'
                : array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return $value ?: null;
    }
}
