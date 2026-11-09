<?php

namespace Modules\User\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Auth\Enums\ContactType;

#[Fillable(['name', 'email', 'phone', 'password', 'email_verified_at', 'phone_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory,Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function verifiedContact(ContactType $contactType)
    {
        $dataVerification = [];
        if ($contactType === ContactType::EMAIL && is_null($this->email_verified_at)) {
            $dataVerification['email_verified_at'] = now();
        }
        if ($contactType === ContactType::PHONE && is_null($this->phone_verified_at)) {
            $dataVerification['phone_verified_at'] = now();
        }

        $this->forceFill($dataVerification)->save();
    }

    public function unverifiedContacts()
    {

        return collect([
            ContactType::EMAIL->value => $this->email_verified_at,
            ContactType::PHONE->value => $this->phone_verified_at,
        ])->filter(fn ($value) => is_null($value))->keys();
    }
}
