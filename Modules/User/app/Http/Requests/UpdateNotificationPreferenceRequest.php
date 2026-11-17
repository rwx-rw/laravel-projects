<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationPreferenceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $defaultPreferences = config('user.notifications.default');
        $preferencesTypes = array_keys($defaultPreferences);

        $channels = array_keys(config('user.notifications.channels',[]));

        return [
            'notification_type' => ['required', 'string', Rule::in($preferencesTypes)],
            'channel' => ['required', 'string', Rule::in($channels)],
            'value' => ['required', 'boolean'],
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
