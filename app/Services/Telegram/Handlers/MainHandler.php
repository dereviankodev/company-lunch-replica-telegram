<?php

namespace App\Services\Telegram\Handlers;

use App\Services\Telegram\Commands\StartCommand;
use GuzzleHttp\Exception\GuzzleException;
use WeStacks\TeleBot\Exception\TeleBotMehtodException;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class MainHandler extends BaseHandler
{
    /**
     * @throws TeleBotMehtodException
     * @throws GuzzleException
     */
    public static function trigger(Update $update, TeleBot $bot): bool
    {
        if (static::baseTrigger($update, $bot) === false) {
            return false;
        }

        $bot->callHandler(StartCommand::class, $update);
        $bot->callHandler(MenuHandler::class, $update);

        return false;
    }

    public function handle()
    {}
}