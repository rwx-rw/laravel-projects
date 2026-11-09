<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Base\Traits\ApiResponse;

class EnsureUserVerifiedMiddleware
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return $next($request);
        }

        $unverifiedContact = $user->unverifiedContacts();
        if(!$unverifiedContact->isEmpty()){
            return $this->errorResponse('Please verify your contact before proceesing',[
                'unverified_contacts' => $unverifiedContact,
                'requires_verification' => true
            ],403);
        }

        return $next($request);
    }
}
