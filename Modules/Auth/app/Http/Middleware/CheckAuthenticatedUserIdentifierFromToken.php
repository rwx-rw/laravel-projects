<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAuthenticatedUserIdentifierFromToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::guard('sanctum')->check()){
            $user = Auth::guard('sanctum')->user();
            $currentToken = $user->currentAccessToken();
            $generateIdentifierFromRequest = $user->identifierFromRequest();

            if($currentToken->identifier !== $generateIdentifierFromRequest){
                return response()->json(['message' => 'unauthonticated'],401);
            }
        }
        return $next($request);
    }
}
