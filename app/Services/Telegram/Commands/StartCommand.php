<?php

namespace App\Services\Telegram\Commands;

use App\Models\TelegramUser;
use App\Services\Telegram\Handlers\BaseHandler;
use App\Services\Telegram\Traits\Clients\Client;
use App\Services\Telegram\Traits\GraphQl\Queries\Catalog;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Handlers\CommandHandler;

class StartCommand extends CommandHandler
{
    use Catalog;
    use Client;

    protected static $aliases = ['/start'];
    protected static $description = 'It\'s time to eat';

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
        })->all();

        $inlineKeyboard = [];

        foreach ($categories as $category) {
            $item = [
                [
                    'text' => $category->name,
                    'callback_data' => 'category='.$category->id
                ]
            ];

            $inlineKeyboard[] = $item;
        }

        $this->sendMessage([
            'text' => '<strong>Добро пожаловать!</strong>',
            'parse_mode' => 'HTML',
        ]);

        $this->sendMessage([
            'text' => '<strong>Меню на '.date('Y-m-d').'</strong>',
            'parse_mode' => 'HTML',
            'reply_markup' => [
                'inline_keyboard' => $inlineKeyboard
            ]
        ]);
    }
}