<?php

use Modules\Base\Notification\Channels\SmsChannel;
use Modules\TelegramBot\Notifications\Channels\TelegramChannel;

return [
    'name' => 'User',

    'notifications' => [

        'default' => [

            'welcome_message' => [
                'email' => true,
                'sms' => true,
                'telegram' => true,
            ],

        ],

        'channels' => [
            'email' => 'mail',
            'sms' => SmsChannel::class,
            'telegram' => TelegramChannel::class,
            'database' => 'database'
        ],

    ],
];
