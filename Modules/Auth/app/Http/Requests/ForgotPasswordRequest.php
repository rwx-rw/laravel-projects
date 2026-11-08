<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Validation\Validator;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Http\Requests\Base\BaseAuthRequest;
use Override;

class ForgotPasswordRequest extends BaseAuthRequest
{
    #[Override]
    public function prepareForValidation()
    {
        $this->prepareContactType();
        $this->action = VerificationActionType::FORGOT_PASSWORD;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'contact' => [
                'bail',
                'required',
                'string',
                ...$this->getContactValidationRules(),
            ],
            'password' => [
                'bail',
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
            ],
            'token' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isnotEmpty()) {
                    return;
                }
                $validatedData = $this->validated();

                if(!$this->validateVerificationToken($validator,$validatedData,$this->action,$validatedData['contact'])){
                    return;
                }

                $this->validateAndAuthenticateUser($validator,$validatedData);
            },
        ];
    }
}
