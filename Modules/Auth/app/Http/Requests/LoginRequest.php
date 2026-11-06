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

class LoginRequest extends FormRequest
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
            'login_type' => [
                'required',
                'string',
                'in:password,token',
            ],
            'contact' => [
                'required',
                'string',
                ...$this->getContactValidationRules(),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:255',
            ],
            'token' => [
                'nullable',
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
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $validatedData = $this->validated();

                if ($this->input('login_type') === 'password') {
                    $this->validatePasswordLogin($validator, $validatedData);
                } else {
                    $this->validateTokenLogin($validator, $validatedData);
                }
            },
        ];
    }

    public function validatePasswordLogin(Validator $validator, array $validatedData)
    {
        if (empty($validatedData['password'])) {
            $validator->errors()->add('password', __('auth::validation.password_required'));

            return;
        }

        $credentials = [
            $this->contactType->value => $validatedData['contact'],
            'password' => $validatedData['password'],
        ];

        if (! Auth::once($credentials)) {
            $validator->errors()->add('contact', __('auth::validation.failed_credentials'));
        }
    }

    public function validateTokenLogin(Validator $validator, array $validatedData)
    {
        if (empty($validatedData['token'])) {
            $validator->errors()->add('token', __('auth::validation.token_required'));

            return;
        }
        $tokenData = (new VerificationCodeService)->getVerificationToken(
            $validatedData['token'],
            [
                'email' => $validatedData['contact'],
                'phone' => $validatedData['contact'],
            ],
            VerificationActionType::LOGIN,
            hash('sha256', $this->userAgent().':'.$this->ip())
        );

        if (! $tokenData) {
            $validator->errors()->add('token', __('auth::validation.token_required'));

            return;
        }

        $user = User::where($this->contactType->value,$validatedData['contact'])
        ->first();

        if(!$user){
            $validator->errors()->add('token',"invalid user");
            return;
        }

        $user->verifiedContact($this->contactType);

        Auth::onceUsingId($user->id);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
