<?php

namespace Modules\Auth\Actions;

use Illuminate\Support\Facades\Crypt;
use Modules\User\Models\User;

class CreateUserToken
{
    public function handle(User $user, string $tokenName, int $expiresAt = 30, bool $isEncrypted = true) : string
    {
        $token = $user->createToken($tokenName, expiresAt: now()->addDays($expiresAt))->plainTextToken;

        $token = $isEncrypted ? Crypt::encryptString($token) : $token;

        return $token;
    }
}
