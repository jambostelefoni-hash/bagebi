<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;

class AddContentSecurityPolicy
{
    public function handle($request, Closure $next)
    {
        $nonce = base64_encode(random_bytes(16));
        View::share('cspNonce', $nonce);

        $response = $next($request);
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "script-src 'self' 'nonce-{$nonce}' https://cdn.datatables.net https://gyrocode.github.io",
            "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com https://cdn.datatables.net https://gyrocode.github.io",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data:",
            "connect-src 'self'",
        ]));

        return $response;
    }
}
