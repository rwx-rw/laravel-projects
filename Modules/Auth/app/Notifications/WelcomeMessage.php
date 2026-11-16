<?php

namespace Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Base\Notification\Channels\SmsChannel;
use Modules\TelegramBot\Notifications\Channels\TelegramChannel;

class WelcomeMessage extends Notification
// class WelcomeMessage extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct() {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return [TelegramChannel::class];
        // return ['mail',SmsChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)->view('auth::mails.welcome');
    }

    /**
     * Get the sms representation of the notification.
     */
    public function toSms($notifiable): string
    {
        return $notifiable->name;
    }

    /**
     * Get the telegram representation of the notification.
     */
    public function toTelegram($notifiable): string
    {
        return 'سلام' . $notifiable->name . ' خوش آمدید';
    }

    
    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
