<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Services\VerificationCodeService;
use Modules\User\Models\User;
use Override;

class ForgotPasswordRequest extends FormRequest
{
    public ContactType $contactType;

    #[Override]
    public function prepareForValidation()
    {
        $this->contactType = ContactType::detectContactType($this->input('contact') ?? '');
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

    protected function getContactValidationRules(): array
    {

        if ($this->contactType === ContactType::EMAIL) {
            return [
                'email',
                'exists:users,email',
            ];
        }

        return [
            'phone:mobile',
            'exists:users,phone',
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

                $tokenData = (new VerificationCodeService)->getVerificationToken(
                    $validatedData['token'],
                    [
                        'email' => $validatedData['contact'],
                        'phone' => $validatedData['contact'],
                    ],
                    VerificationActionType::FORGOT_PASSWORD,
                    hash('sha256', $this->userAgent().':'.$this->ip())
                );

                if (! $tokenData) {
                    $validator->errors()->add('token', __('auth::verification.invalid_token'));

                    return;
                }

                $user = User::where($this->contactType->value, $validatedData['contact'])->first();

                if (! $user) {
                    $validator->errors()->add('contact', __('auth::validation.contact.not_found'));

                    return;
                }

                $user->verifiedContact($this->contactType);
                Auth::onceUsingId($user->id);
            },
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
