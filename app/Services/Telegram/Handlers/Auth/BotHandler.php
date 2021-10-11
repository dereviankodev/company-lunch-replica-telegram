<?php

namespace App\Services\Telegram\Handlers\Auth;

use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class BotHandler extends UpdateHandler
{
    public static function trigger(Update $update, TeleBot $bot): bool
    {
        return false;
    }

    public function handle()
    {
        $this->sendMessage([
            'text' => 'Bots don\'t eat! Bye!'
        ]);
    }
}