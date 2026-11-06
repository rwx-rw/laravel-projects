<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Http\Requests\SendVerificationRequest;
use Modules\Auth\Http\Requests\VerifyVerificationRequest;
use Modules\Auth\Services\VerificationCodeService;

class VerificationController extends Controller
{
    public function __construct(private VerificationCodeService $verificationCodeService) {}

    public function sendCode(SendVerificationRequest $request)
    {
        $code = $this->verificationCodeService->generateCode(
            contact: $request->input('contact'),
            action: $request->action,
            contactType: $request->contactType
        );

        $responseStatus = false;

        if ($request->contactType === ContactType::EMAIL) {
            $responseStatus = $this->verificationCodeService->sendCodeAsMail($request,$code);
        }

        if ($request->contactType === ContactType::PHONE) {
            $responseStatus = $this->verificationCodeService->sendCodeAsSms($request, $code);
        }

        if ($responseStatus) {
            return response()->json(__('auth::verification.success_code_send'));
        }

        return response()->json(__('auth::verification.failed_code_send'), 500);
    }

    public function verifyCode(VerifyVerificationRequest $request){
        $token = $this->verificationCodeService->createVerificationToken($request);

        return $token;
    }
}
 