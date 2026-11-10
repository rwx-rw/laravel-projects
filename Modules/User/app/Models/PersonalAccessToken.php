<?php

namespace Modules\User\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use Override;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    #[Override]
    protected static function boot()
    {
         parent::boot();

        static::creating(function($token){
            $token->identifier = hash('sha256',request()->userAgent() . ':' . request()->ip() . ':' . $token->tokenable_id);
        });
    }
}