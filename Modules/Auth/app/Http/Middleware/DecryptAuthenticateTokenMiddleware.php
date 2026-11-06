<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class DecryptAuthenticateTokenMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if ($request->headers->has('Authorization')) {
                $token = $request->bearerToken();
                $decryptedToken = Crypt::decryptString($token);
                $request->headers->set('Authorization', 'Bearer '.$decryptedToken);
            }

            if ($request->cookies->has('x_web_token') && ! $request->headers->has('Authorization')) {
                $token = $request->cookies->get('x_web_token');
                $decryptedToken = Crypt::decryptString($token);
                $request->headers->set('Authorization', 'Bearer '.$decryptedToken);
            }

        } catch (\Exception $e) {

        }

        return $next($request);
    }
}
