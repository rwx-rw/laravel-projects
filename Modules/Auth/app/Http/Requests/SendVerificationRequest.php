<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Services\VerificationCodeService;
use Override;

class SendVerificationRequest extends FormRequest
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
            'action' => [
                'bail',
                'required',
                'string',
                new Enum(VerificationActionType::class),
            ],
            'contact' => [
                'bail',
                'required',
                'string',
                ...$this->getContactValidationRules(),
            ],
        ];
    }

    protected function getContactValidationRules(): array
    {
        $verificationAction = VerificationActionType::tryFrom($this->input('action'));

        if (! $verificationAction) {
            return [];
        }

        if ($this->contactType === ContactType::EMAIL) {
            return [
                'email:rfc,dns',
                Rule::when($verificationAction->isContactNeedToBeUnique(), ['unique:users,email']),
                Rule::when($verificationAction->isContactNeedToBeExists(), ['exists:users,email']),
            ];
        }

        return [
            'phone:mobile',
            Rule::when($verificationAction->isContactNeedToBeUnique(), ['unique:users,phone']),
            Rule::when($verificationAction->isContactNeedToBeExists(), ['exists:users,phone']),
        ];

    }

    public function after(): array
    {
        return [
            function (Validator $validator) {

                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $contact = $this->input('contact');
                $action = VerificationActionType::tryFrom($this->input('action'));
                $contactType = $this->contactType;

                $retryTime = (new VerificationCodeService)->getRetryTime($contact, $action, $contactType);

                if ($retryTime) {
                    $validator->errors()->add('contact', __('auth::validation.contact.retry_time', ['retry_time' => $retryTime]));
                }
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
