<?php

namespace App\Services\Telegram\Handlers;

use App\Services\Telegram\Commands\StartCommand;
use App\Services\Telegram\Handlers\Carts\DeleteHandler;
use App\Services\Telegram\Handlers\Carts\GetCartHandler;
use App\Services\Telegram\Handlers\Carts\UpsertHandler;
use App\Services\Telegram\Handlers\Categories\ListHandler;
use App\Services\Telegram\Handlers\Categories\ItemHandler;
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
        $mainStart = microtime(true);
        if (static::baseTrigger($update, $bot) === false) {
            return false;
        }
        var_dump('Auth: '.number_format((microtime(true) - $mainStart), 3));

        $start = microtime(true);
        $bot->callHandler(GetCartHandler::class, $update);
        var_dump('Get Cart: '.number_format((microtime(true) - $start), 3));

        $start = microtime(true);
        $bot->callHandler(StartCommand::class, $update);
        var_dump('Start: '.number_format((microtime(true) - $start), 3));

        $start = microtime(true);
        $bot->callHandler(ListHandler::class, $update);
        var_dump('Callback category list: '.number_format((microtime(true) - $start), 3));

        $start = microtime(true);
        $bot->callHandler(ItemHandler::class, $update);
        var_dump('Callback category dishes: '.number_format((microtime(true) - $start), 3));

        $start = microtime(true);
        $bot->callHandler(UpsertHandler::class, $update);
        var_dump('Upsert: '.number_format((microtime(true) - $start), 3));

        $start = microtime(true);
        $bot->callHandler(DeleteHandler::class, $update);
        var_dump('Delete: '.number_format((microtime(true) - $start), 3));

        var_dump('All Handlers: '.number_format((microtime(true) - $mainStart), 3));

        return false;
    }

    public function handle()
    {}
}