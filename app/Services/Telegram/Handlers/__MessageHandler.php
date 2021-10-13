<?php

namespace App\Services\Telegram\Handlers;

use App\Models\TelegramUser;
use App\Services\Telegram\Traits\Clients\Client;
use App\Services\Telegram\Traits\GraphQl\Queries\Catalog;
use GuzzleHttp\Exception\GuzzleException;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class MessageHandler extends UpdateHandler
{
    use Catalog;
    use Client;

    private const ENTITY_NAME = [
        'Категории',
    ];

    public static function trigger(Update $update, TeleBot $bot): bool
    {
        if (!isset($update?->message->text)) {
            return false;
        }

        return collect(static::ENTITY_NAME)->contains($update->message->text);
    }

    /**
     * @throws GuzzleException
     */
    public function handle()
    {
        $user = $this->update->user();
        $hashId = BaseHandler::hashBySecret($user->id);
        $telegramUser = TelegramUser::find($hashId);

        $request = static::categories();
        $data = static::clientGraphQl($request, $telegramUser->token);
        $categories = collect($data->categories)->filter(function ($value) {
            return !empty($value->actualMenu);
        })->values()->all();

        $inlineKeyboard = [];
        $line = [];

        foreach ($categories as $key => $category) {
            $item = [
                'text' => $category->name,
                'callback_data' => 'category='.$category->id
            ];
            $line[] = $item;

            // Is odd
            if ($key & 1) {
                $inlineKeyboard[] = $line;
                $line = [];
            }
        }

        $this->sendMessage([
            'text' => '<strong>Меню на '.date('Y-m-d').'</strong>',
            'parse_mode' => 'HTML',
            'reply_markup' => [
                'inline_keyboard' => $inlineKeyboard
            ]
        ]);
    }
}