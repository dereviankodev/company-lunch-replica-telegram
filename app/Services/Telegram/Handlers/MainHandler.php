<?php

namespace App\Services\Telegram\Handlers;

use App\Services\Telegram\Commands\StartCommand;
use App\Services\Telegram\Handlers\Categories\IndexCatalogHandler;
use App\Services\Telegram\Handlers\Categories\ViewCatalogHandler;
use App\Services\Telegram\Handlers\Menus\IndexMenuHandler;
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
        $start = microtime(true);
        if (static::baseTrigger($update, $bot) === false) {
            return false;
        }
        var_dump('Auth: '.number_format((microtime(true) - $start), 3));

        $start = microtime(true);
        $bot->callHandler(StartCommand::class, $update);
        var_dump('Start: '.number_format((microtime(true) - $start), 3));

        $start = microtime(true);
        $bot->callHandler(IndexCatalogHandler::class, $update);
        var_dump('Callback catalog index: '.number_format((microtime(true) - $start), 3));

        $start = microtime(true);
        $bot->callHandler(ViewCatalogHandler::class, $update);
        var_dump('Callback catalog view: '.number_format((microtime(true) - $start), 3));

        $start = microtime(true);
        $bot->callHandler(IndexMenuHandler::class, $update);
        var_dump('Callback menu: '.number_format((microtime(true) - $start), 3));

//        $start = microtime(true);
//        $bot->callHandler(MessageHandler::class, $update);
//        var_dump('Message: '.number_format((microtime(true) - $start), 3));

        return false;
    }

    public function handle()
    {}
}