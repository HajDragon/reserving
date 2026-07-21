<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add security-related HTTP headers to all responses.
     * Based on OWASP recommended security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS filter in older browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy — don't leak full URL to external sites
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions policy — restrict browser features we don't use
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content Security Policy (restrictive default)
        $cspSources = [
            "default-src 'self'",
            "img-src 'self' data: blob:",                       // Product images + placeholders
            "font-src 'self' https://fonts.bunny.net",          // Bunny Fonts
            "frame-ancestors 'none'",                           // Additional clickjacking protection
        ];

        if (config('app.debug')) {
            // Dev mode: allow Vite dev server (HMR + asset serving) + external font stylesheets
            $viteDev = config('app.vite_dev_url', 'http://localhost:5173');
            $cspSources[] = "style-src 'self' 'unsafe-inline' {$viteDev} https://fonts.bunny.net";
            $cspSources[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' {$viteDev}";
            $cspSources[] = "connect-src 'self' ws: {$viteDev}";     // Livewire AJAX + Vite HMR WebSocket
        } else {
            $cspSources[] = "style-src 'self' 'unsafe-inline' https://fonts.bunny.net"; // Font stylesheets
            $cspSources[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval'";  // Livewire
            $cspSources[] = "connect-src 'self'";                               // Livewire AJAX
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $cspSources));

        // HSTS — only on HTTPS (won't hurt on HTTP)
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
