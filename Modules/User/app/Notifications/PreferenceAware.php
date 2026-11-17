<?php

namespace Modules\User\Notifications;

use Modules\User\Services\NotificationPereferenceService;

trait PreferenceAware
{
    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return (new NotificationPereferenceService)->allowedChannels($notifiable, $this->type,$this->forceChanels);
    }
}
