<?php

use App\Services\Telegram\Commands\StartCommand;

return [
    'default' => 'QuartSoftLunchBot',

    'bots' => [
        'QuartSoftLunchBot' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'name' => env('TELEGRAM_BOT_NAME', null),
            'api_url' => 'http://localhost:8081',
            'exceptions' => true,
            'async' => false,

            'webhook' => [],

            'poll' => [],

            'handlers' => [
                StartCommand::class
            ],
        ],
    ],
];
