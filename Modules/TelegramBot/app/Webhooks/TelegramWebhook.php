<?php

namespace Modules\TelegramBot\Webhooks;

use App\Models\User;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Facades\Log;

class TelegramWebhook extends WebhookHandler
{
    public function start()
    {
        $this->chat->message('Welcome to the bot!')->send();
    }

    public function register()
    {

        $this->chat->message('you logged in')->send();
    }

    public function login(string $id)
    {
        Log::info($id, [
            'chat_id' => $this->chat->id,
        ]);
        if ($user = User::find($id)) {
            $user->update([
                'telegram_chat_id' => $this->chat->id,
            ]);
            $this->chat->message('you logged in with ID: '.$id)->send();
        }
        else {
            $this->chat->message('user not found')->send();
        }

    }

    public function delete()
    {
        $this->chat->message('you deleted the message')->send();
    }

    public function payment()
    {
        $this->chat->message('you clicked on the payment button')->send();
    }

    public function view()
    {
        $this->chat->message('you clicked on the view button')->send();
    }
}
