<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * '*' - trust the immediate hop unconditionally. Azure App Service
     * always sits in front of this app (there is no way for a client to
     * reach it directly), so that hop IS the platform's own edge, not an
     * untrusted party - the $headers list below was already whitelisting
     * X-Forwarded-Proto/-Host/-For, but with $proxies left null Laravel
     * trusts NONE of them, so none of those headers were ever actually
     * read. Concretely: $request->isSecure() was always false server-side
     * (Azure terminates TLS at its edge and forwards over plain HTTP
     * internally), so every signed/absolute URL generated during a
     * request - e.g. NewsletterController's resubscribe link - was
     * validated against an "http://" reconstruction of the request even
     * though it was generated as "https://", and 403'd (Pablo, 2026-09-21,
     * found while testing newsletter unsubscribe/resubscribe).
     *
     * @var array|string|null
     */
    protected $proxies = '*';

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
}
