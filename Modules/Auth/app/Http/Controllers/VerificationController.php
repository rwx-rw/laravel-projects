<?php

namespace Modules\Auth\Http\Controllers;

use Modules\Auth\Enums\ContactType;
use Modules\Auth\Http\Requests\SendVerificationRequest;
use Modules\Auth\Http\Requests\VerifyVerificationRequest;
use Modules\Auth\Services\VerificationCodeService;
use Modules\Base\Http\Controllers\ApiController;

class VerificationController extends ApiController
{
    public function __construct(private VerificationCodeService $verificationCodeService) {}

    public function sendCode(SendVerificationRequest $request)
    {
        $code = $this->verificationCodeService->generateCode(
            contact: $request->input('contact'),
            action: $request->action,
            contactType: $request->contactType
        );

        if (! $this->sendCodeByContactType($request, $code)) {
            return $this->errorResponse(null, ['contact' => [__('auth::verification.failed_code_send')]], code: 500);

        }

        return $this->successResponse(__('auth::verification.success_code_send'));

    }

    public function verifyCode(VerifyVerificationRequest $request)
    {
        $token = $this->verificationCodeService->createVerificationToken($request);

        return $this->successResponse(data: ['token' => $token]);
    }

    private function sendCodeByContactType(SendVerificationRequest $request, string $code)
    {
        return match ($request->contactType) {
            ContactType::EMAIL => $this->verificationCodeService->sendCodeAsMail($request, $code),
            ContactType::PHONE => $this->verificationCodeService->sendCodeAsSms($request, $code)
        };
    }
}
