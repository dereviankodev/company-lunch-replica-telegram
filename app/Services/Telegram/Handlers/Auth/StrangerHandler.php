<?php

namespace App\Services\Telegram\Handlers\Auth;

use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class StrangerHandler extends UpdateHandler
{
    public static function trigger(Update $update, TeleBot $bot): bool
    {
        return false;
    }

    public function handle()
    {
        $this->sendMessage([
            'text' => 'Hi stranger! If you think this is a mistake, perhaps you should link your telegram account'
                .' in the QuartSoft lunch admin panel. I am sure see you soon!',
            'reply_markup' => [
                'remove_keyboard' => true
            ]
        ]);
    }
}