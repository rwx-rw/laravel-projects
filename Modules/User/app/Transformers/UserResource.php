<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'telegram_bot_register_command' => ! $this->telegram_chat_id ? $this->makeTelegramBotRegisterCommand() : null,
            // 'chat' => $this->telegramChat,
        ];
    }
}
