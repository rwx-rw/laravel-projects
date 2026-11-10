<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Http\Requests\Base\BaseAuthRequest;
use Override;

class SendVerificationRequest extends BaseAuthRequest
{


    #[Override]
    public function prepareForValidation()
    {
        $this->prepareContactType();
        $this->prepareAction();
    }

    #[Override]
    public function authorize(): bool
    {
        if($this->action === VerificationActionType::CHANGE_INFO && !Auth::guard('sanctum')->check()){
            return false;
        }
        return true;
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


    public function after(): array
    {
        return [
            function (Validator $validator) {

                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $contact = $this->input('contact');
                $action = $this->action;
                $contactType = $this->contactType;

                $this->checkRetryTime($validator,$this->validated(),$this->action,$this->contactType);
            },
        ];
    }

}
