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
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",  // Vite/Livewire needs unsafe-inline+eval
            "style-src 'self' 'unsafe-inline'",                 // Tailwind + FluxUI inline styles
            "img-src 'self' data: blob:",                       // Product images + placeholders
            "font-src 'self' https://fonts.bunny.net",          // Bunny Fonts
            "connect-src 'self'",                               // Livewire AJAX
            "frame-ancestors 'none'",                           // Additional clickjacking protection
        ]));

        // HSTS — only on HTTPS (won't hurt on HTTP)
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
