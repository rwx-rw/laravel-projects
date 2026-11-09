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
    private const TOKEN_EXPIRY_MINUTES = 10;

    private const TOKEN_LENGTH = 100;

    private const CODE_MIN = 100000;

    private const CODE_MAX = 999999;

    private const EMAIL_EXPIRY_MINUTS = 5;

    private const SMS_EXPIRY_MINUTS = 1;

    private TokenStorageService $tokenService;

    private CodeStorageService $codeService;

    public function __construct()
    {
        $this->tokenService = app(TokenStorageService::class);
        $this->codeService = app(CodeStorageService::class);
    }

    public function createVerificationToken(VerifyVerificationRequest $request)
    {
        do {
            $token = str()->random(self::TOKEN_LENGTH);
        } while ($this->tokenService->has($token));

        $this->tokenService->save($token, $request, self::TOKEN_EXPIRY_MINUTES);

        return $token;
    }

    public function generateCode(string $contact, VerificationActionType $action, ContactType $contactType, ?int $expiryMinutes = null)
    {
        $expiryMinutes = $expiryMinutes ?? $this->getDefaultExpiryMinutes($contactType);

        $code = random_int(self::CODE_MIN, self::CODE_MAX);

        $this->codeService->save($contact, $action, $contactType, $code, now()->addMinutes($expiryMinutes));

        return $code;
    }

    public function getRetryTime(string $contact, VerificationActionType $action, ContactType $contactType): ?int
    {
        $codeData = $this->codeService->get($contact, $action, $contactType);
        return ( $codeData && now()->isAfter($codeData['expired_at']) ) ? now()->diffInSeconds($codeData['expired_at']) : null;
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
            $this->codeService->forget($request->input('contact'), $request->action, $request->contactType);

            return false;
        }

    }

    public function sendCodeAsMail(SendVerificationRequest $request, string $code): bool
    {
        try {
            Mail::to($request->input('contact'))->send(new VerificationCode($code));
        } catch (Throwable $th) {
            $this->codeService->forget($request->input('contact'), $request->action, $request->contactType);

            return false;
        }

        return true;

    }

    public function forgetCode(string $contact, VerificationActionType $action, ContactType $contactType)
    {
        $this->codeService->forget($contact, $action, $contactType);
    }

    public function verifyCode(string $contact, VerificationActionType $action, ContactType $contactType, string $code)
    {
        $cacheData = $this->codeService->get($contact,$action,$contactType);

        // If Using Database Chache Add This (&& now()->diffInSeconds($cacheValue['expired_at'])) If Using Redis we Dont Need this (&& now()->diffInSeconds($cacheValue['expired_at']))
        if ($cacheData && (string) $cacheData['code'] === (string) $code) {
            $this->codeService->forget($contact, $action, $contactType);

            return true;
        }

        return false;
    }

    public function getVerificationToken($token, array $contact, VerificationActionType $action, string $identifier): ?array
    {
        $tokenData = $this->tokenService->get($token);

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

    private function getDefaultExpiryMinutes(ContactType $contactType)
    {
        return match($contactType){
            ContactType::EMAIL => self::EMAIL_EXPIRY_MINUTS,
            ContactType::PHONE => self::SMS_EXPIRY_MINUTS
        };
    }
}
