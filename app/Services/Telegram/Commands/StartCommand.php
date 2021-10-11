<?php

namespace App\Services\Telegram\Commands;

use WeStacks\TeleBot\Handlers\CommandHandler;

class StartCommand extends CommandHandler
{
    protected static $aliases = ['/start'];
    protected static $description = 'It\'s time to eat';

    public function handle()
    {
        $this->messageForFriend();
    }

    private function messageForFriend()
    {
        $this->sendMessage([
            'text' => 'Ready to eat?',
            'reply_markup' => [
                'keyboard' => $this->getKeyboard(),
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
                'input_field_placeholder' => 'Закажи обед...',
            ]
        ]);
    }

    private function getKeyboard(): array
    {
        return [
            [
                ['text' => __('Ознакомиться с меню')],
            ],
        ];
    }
}