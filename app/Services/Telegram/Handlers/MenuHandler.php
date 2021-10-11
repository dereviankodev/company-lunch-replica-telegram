<?php

namespace App\Services\Telegram\Handlers;

use App\Models\TelegramUser;
use App\Services\Telegram\Traits\Clients\Client;
use App\Services\Telegram\Traits\GraphQl\Queries\Catalog;
use GuzzleHttp\Exception\GuzzleException;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class MenuHandler extends UpdateHandler
{
    use Catalog;
    use Client;

    private const ENTITY_NAME = [
        'Ознакомиться с меню',
    ];

    public static function trigger(Update $update, TeleBot $bot): bool
    {
        if (!isset($update->message) || !isset($update->message->text)) {
            return false;
        }

        $entityNames = collect(static::ENTITY_NAME);

        return $entityNames->contains($update->message->text);
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

        $inlineKeyboard = [];
        foreach ($data->categories as $category) {
            $link = "http://lunch-replica.stage2.quartsoft.com/$category->img_path";
            $item = [
                'photo' => $link,
                'caption' => "<strong>$category->name</strong>",
                'parse_mode' => 'HTML',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => "Посмотреть блюда в категории $category->name",
                                'callback_data' => 'category/'.$category->id
                            ]
                        ]
                    ]
                ]
            ];
            array_push($inlineKeyboard, $item);
        }

        var_dump($inlineKeyboard);
        foreach ($inlineKeyboard as $item) {
            $this->sendPhoto($item);
        }


        $this->sendMessage([
            'text' => '<strong>Меню на '.date('Y-m-d').'</strong>'.PHP_EOL.'<a href="http://lunch-replica.stage2.quartsoft.com/images/category/e05de44cad6c1613c243b3f7dd787951.png">&#8205;</a>',
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false,
        ]);
    }
}