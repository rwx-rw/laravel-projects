<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Modules\Auth\Emails\VerificationCode;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Http\Requests\SendVerificationRequest;
use Modules\Auth\Http\Requests\VerifyVerificationRequest;
use Throwable;

class VerificationCodeService
{
    public function generateCacheKey(string $contact, VerificationActionType $action, ContactType $contactType)
    {
        $contact = hash('sha256', "{$action->value}:{$contactType->value}:{$contact}");

        return "verification:$contact";
    }

    public function generateCode(string $contact, VerificationActionType $action, ContactType $contactType, ?int $expiryMinutes = null)
    {
        if ($expiryMinutes === null) {
            $expiryMinutes = $contactType === ContactType::EMAIL ? 5 : 1;
        }
        $code = random_int(100000, 999999);

        $cacheKey = $this->generateCacheKey($contact, $action, $contactType);
        $expiredAt = now()->addMinutes($expiryMinutes);
        Cache::put($cacheKey, [
            'code' => $code,
            'expired_at' => $expiredAt->toDateTimeString(),
        ], now()->addMinutes($expiryMinutes));

        return $code;
    }

    public function getRetryTime(string $contact, VerificationActionType $action, ContactType $contactType): ?int
    {
        $cacheKey = $this->generateCacheKey($contact, $action, $contactType);

        $cacheValue = Cache::get($cacheKey);

        return $cacheValue && now()->diffInSeconds($cacheValue['expired_at']) > 0 ? now()->diffInSeconds($cacheValue['expired_at']) : null;
    }

    public function sendCodeAsSms(SendVerificationRequest $request, string $code): bool
    {
        try {
            $sendSmsApiRequest = Http::post(
                'https://console.melipayamak.com/api/send/shared/73f129d2aa9044c489495e2c803834c9',
                [
                    'bodyId' => 497287,
                    'to' => $request->input('contact'),
                    'args' => [(string) $code],
                ]
            );

            $sendSmsApiRequest->throw();

            return $sendSmsApiRequest->successful();
        } catch (Throwable $th) {
            $this->forgetCode($request->input('contact'), $request->action, $request->contactType);

            return false;
        }

    }

    public function sendCodeAsMail(SendVerificationRequest $request, string $code): bool
    {
        try {
            Mail::to($request->input('contact'))->send(new VerificationCode($code));
        } catch (Throwable $th) {
            $this->forgetCode($request->input('contact'), $request->action, $request->contactType);

            return false;
        }

        return true;

    }

    public function forgetCode(string $contact, VerificationActionType $action, ContactType $contactType)
    {
        $cacheKey = $this->generateCacheKey($contact, $action, $contactType);
        Cache::forget($cacheKey);
    }

    public function verifyCode(string $contact, VerificationActionType $action, ContactType $contactType, string $code)
    {
        $cacheKey = $this->generateCacheKey($contact, $action, $contactType);
        $cacheValue = Cache::get($cacheKey);

        // If Using Database Chache Add This (&& now()->diffInSeconds($cacheValue['expired_at'])) If Using Redis we Dont Need this (&& now()->diffInSeconds($cacheValue['expired_at']))
        if ($cacheValue && (string) $cacheValue['code'] === (string) $code) {
            $this->forgetCode($contact, $action, $contactType);

            return true;
        }

        return false;
    }

    public function createVerificationToken(VerifyVerificationRequest $request)
    {
        do {
            $token = str()->random(60);
        } while (Cache::has("verification:after_verify:token:$token"));

        Cache::put("verification:after_verify:token:$token", [
            'contact' => $request->input('contact'),
            'action' => $request->action,
            'contact_type' => $request->contactType,
            'identifier' => hash('sha256',$request->userAgent() . ":" . $request->ip())
        ], now()->addMinutes(10));

        return $token;
    }

    public function getVerificationToken($token, array $contact, VerificationActionType $action,string $identifier): ?array
    {
        $cacheKey = "verification:after_verify:token:$token";
        $tokenData = Cache::pull($cacheKey);

        if (! $tokenData) {
            return null;
        }

        $contact = $tokenData['contact_type'] === ContactType::EMAIL ? $contact['email'] : $contact['phone'];

        if (
        $contact === $tokenData['contact'] &&
        $action->value === $tokenData['action']->value &&
        $tokenData['identifier'] === $identifier
        ) {
            return $tokenData;
        }

        return null;

    }

    public function handle() {}
}
