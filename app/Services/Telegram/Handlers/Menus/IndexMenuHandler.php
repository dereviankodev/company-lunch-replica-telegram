<?php

namespace App\Services\Telegram\Handlers\Menus;

use App\Services\Telegram\Traits\Clients\Client;
use App\Services\Telegram\Traits\GraphQl\Queries\Catalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class IndexMenuHandler extends UpdateHandler
{
    use Catalog;
    use Client;

    private static Collection $callbackData;

    private const QUERY_ACCESSES = [
        'menus',
    ];

    public static function trigger(Update $update, TeleBot $bot): bool
    {
        if (!isset($update?->callback_query)) {
            return false;
        }

        dump($update->callback_query);

        static::$callbackData = Str::of($update->callback_query->data)->trim()->explode('=');

        return collect(static::QUERY_ACCESSES)->contains(static::$callbackData->first());
    }

    public function handle()
    {
        $this->answerCallbackQuery([
            'callback_query_id' => $this->update->callback_query->id,
            'text' => 'Успешно добавлено в корзину'
        ]);

        $menuId = static::$callbackData->get(1);

        $this->editMessageReplyMarkup([
            'message_id' => $this->update->callback_query->message->message_id,
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => "❌  Убрать",
                            'callback_data' => 'menus='.$menuId.'=deleteDishFromCart'
                        ],
                    ]
                ]
            ]
        ]);
    }
}