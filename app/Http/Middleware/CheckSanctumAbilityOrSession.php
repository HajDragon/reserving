<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Laravel\Sanctum\Exceptions\MissingAbilityException;

class CheckSanctumAbilityOrSession
{
    /**
     * Handle the incoming request.
     */
    public function handle($request, Closure $next, ...$abilities)
    {
        if (! $request->user()) {
            throw new AuthenticationException;
        }

        // No testing shortcut here — enforce token abilities when a
        // current access token exists, and allow session-authenticated
        // users through when no token is present.

        // If the user is authenticated via session (no access token), allow.
        if (! method_exists($request->user(), 'currentAccessToken') || ! $request->user()->currentAccessToken()) {
            return $next($request);
        }

        foreach ($abilities as $ability) {
            if (! $request->user()->tokenCan($ability)) {
                throw new MissingAbilityException($ability);
            }
        }

        return $next($request);
    }
}
