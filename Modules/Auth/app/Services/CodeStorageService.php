<?php

namespace Modules\Auth\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Enums\VerificationActionType;

class CodeStorageService
{
    public const VERIFICATION_CACHE_PREFIX = 'verification:';

    public function getKey(string $contact, VerificationActionType $action, ContactType $contactType)
    {
        $contact = hash('sha256', "{$action->value}:{$contactType->value}:{$contact}");

        return self::VERIFICATION_CACHE_PREFIX. $contact;
    }


    public function save(string $contact, VerificationActionType $action, ContactType $contactType,int $code,Carbon $expiredAt): void
    {

        Cache::put($this->getKey($contact,$action,$contactType), [
            'code' => $code,
            'expired_at' => $expiredAt->toDateTimeString(),
        ], $expiredAt);
    }

    public function get(string $contact, VerificationActionType $action, ContactType $contactType)
    {
        return Cache::pull($this->getKey($contact,$action,$contactType));
    }

    public function softGet(string $contact, VerificationActionType $action, ContactType $contactType)
    {
        return Cache::get($this->getKey($contact,$action,$contactType));
    }

    public function forget(string $contact, VerificationActionType $action, ContactType $contactType){
        Cache::forget($this->getKey($contact,$action,$contactType));
    }
}
