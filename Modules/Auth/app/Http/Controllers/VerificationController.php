<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Http\Requests\SendVerificationRequest;
use Modules\Auth\Services\VerificationCodeService;

class VerificationController extends Controller
{
    public function __construct(private VerificationCodeService $verificationCodeService) {}

    public function sendCode(SendVerificationRequest $request)
    {
        $code = $this->verificationCodeService->generateCode(
            contact: $request->input('contact'),
            action: VerificationActionType::tryFrom($request->input('action')),
            contactType: $request->contactType
        );

        dd($code);
    }
}
