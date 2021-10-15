<?php

use App\Services\Telegram\Commands\Blanks\StartBlankCommand;
use App\Services\Telegram\Handlers\MainHandler;

return [
    'default' => 'QuartSoftLunchBot',

    'bots' => [
        'QuartSoftLunchBot' => [
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'name' => env('TELEGRAM_BOT_NAME'),
            'api_url' => 'http://localhost:8081',
            'exceptions' => true,
            'async' => false,

            'webhook' => [],

            'poll' => [],

            'handlers' => [
                StartBlankCommand::class,
                MainHandler::class
            ],

            'request_base_uri' => env('TELEGRAM_REQUEST_BASE_URI')
        ],
    ],
];
