<?php

namespace Modules\TelegramBot\Notifications\Channels;

use DefStudio\Telegraph\Enums\ButtonStyle;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Notifications\Notification;

class TelegramChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toTelegram')) {
            throw new \Exception('Notification does not support Telegram channel.');
        }
        if (! $notifiable->telegramChat) {
            return;
        }
        $message = $notification->toTelegram($notifiable);

        // $result = Telegraph::chat($notifiable->telegramChat)->message($message)
        //     ->keyboard(Keyboard::make()->buttons([
        //         Button::make('خواندم')->action('read')->param('read', true),
        //     ]))->send();

        $keyboard = Keyboard::make()
            ->row([
                Button::make('حذف')->action('delete')->style(ButtonStyle::danger),
                Button::make('پرداخت')->action('payment')->style(ButtonStyle::success),
                Button::make('مشاهده')->action('view')->style(ButtonStyle::primary)
            ]);

        $result = Telegraph::chat($notifiable->telegramChat)->message($message)->keyboard($keyboard)->send();

        // $result = Telegraph::chat($notifiable->telegramChat)->message($message)->send();

        if ($result->failed() && json_decode($result->body())->description === 'Forbidden: bot was blocked by the user') {
            $notifiable->telegramChat()->delete();
        }
    }
}
