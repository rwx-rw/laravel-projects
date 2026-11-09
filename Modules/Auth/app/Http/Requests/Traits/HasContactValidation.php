<?php

namespace Modules\Auth\Http\Requests\Traits;

use Illuminate\Support\Facades\Auth;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Rules\ContactBelongsToAuthenticatedUser;

trait HasContactValidation
{
    public function getContactValidationRules(): array
    {
        if ($this->contactType === ContactType::EMAIL) {
            return $this->getEmailValidationRules();
        }

        return $this->getPhoneValidationRules();
    }

    public function getEmailValidationRules(): array
    {
        $rules = ['email:rfc,dns'];

        if ($this->action) {
            if ($this->action->isContactNeedToBeUnique()) {
                $rules[] = 'unique:users,email';
            }
            if ($this->action->isContactNeedToBeExists()) {
                $rules[] = 'exists:users,email';
            }
        }

        if ($this->action === VerificationActionType::VERIFY) {
            $rules[] = new ContactBelongsToAuthenticatedUser();
        }

        return $rules;
    }

    public function getPhoneValidationRules(): array
    {
        $rules = ['phone:mobile'];

        if ($this->action) {
            if ($this->action->isContactNeedToBeUnique()) {
                $rules[] = 'unique:users,phone';
            }
            if ($this->action->isContactNeedToBeExists()) {
                $rules[] = 'exists:users,phone';
            }

            if ($this->action === VerificationActionType::VERIFY) {
                $rules[] = new ContactBelongsToAuthenticatedUser();
            }
        }

        return $rules;
    }
}
