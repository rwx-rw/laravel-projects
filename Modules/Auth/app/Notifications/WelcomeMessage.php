<?php

namespace Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Modules\Base\Notification\Channels\SmsChannel;
use Modules\TelegramBot\Notifications\Channels\TelegramChannel;
use Modules\User\Notifications\PreferenceAware;
use Modules\User\Services\NotificationPereferenceService;

class WelcomeMessage extends Notification
// class WelcomeMessage extends Notification implements ShouldQueue
{
    use Queueable;
    use PreferenceAware;


    protected string $type = "welcome_message";

    protected ?array $forceChanels = [TelegramChannel::class];

    /**
     * Create a new notification instance.
     */
    public function __construct() {}

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
