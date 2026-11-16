<?php

namespace Modules\Base\Notification\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmsChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            throw new \Exception('Notification does not support SMS channel.');
        }
        $message = $notification->toSms($notifiable);

        try {
            $sendSmsApiRequest = Http::post(
                'https://console.melipayamak.com/api/send/shared/73f129d2aa9044c489495e2c803834c9',
                [
                    'bodyId' => 497287,
                    'to' => $notifiable->phone,
                    'args' => [$message],
                ]
            );

            $sendSmsApiRequest->throw();

        } catch (Throwable $th) {
            Log::error('Failed to send SMS notification: '.$th->getMessage(), [
                'exception' => $th,
                'notifiable' => $notifiable,
                'notification' => $notification,
            ]);

        }

        // Send notification to the $notifiable instance...
    }
}
