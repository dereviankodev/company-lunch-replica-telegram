<?php

namespace App\Services\Telegram\Handlers\Categories;

use App\Models\TelegramUser;
use App\Services\Telegram\Handlers\BaseHandler;
use App\Services\Telegram\Traits\Clients\Client;
use App\Services\Telegram\Traits\GraphQl\Queries\Catalog;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class ViewCatalogHandler extends UpdateHandler
{
    use Catalog;
    use Client;

    private static Collection $callbackData;

    private const QUERY_ACCESSES = [
        'category',
    ];

    public static function trigger(Update $update, TeleBot $bot): bool
    {
        if (!isset($update?->callback_query)) {
            return false;
        }

        static::$callbackData = Str::of($update->callback_query->data)->trim()->explode('=');

        return collect(static::QUERY_ACCESSES)->contains(static::$callbackData->first());
    }

    /**
     * @throws GuzzleException
     */
    public function handle()
    {
        $this->answerCallbackQuery([
            'callback_query_id' => $this->update->callback_query->id
        ]);

        $user = $this->update->user();
        $hashId = BaseHandler::hashBySecret($user->id);
        $telegramUser = TelegramUser::find($hashId);

        $request = static::category(static::$callbackData->last());
        $data = static::clientGraphQl($request, $telegramUser->token);
        $category = collect($data->category);

        $this->sendMessage([
            'text' => '🍽&#160;&#160;<strong>'.$category["name"].':</strong>',
            'parse_mode' => 'HTML'
        ]);

        foreach ($category->get('actualMenu') as $menu) {
            $this->sendMessage([
                'text' => '<strong>'.$menu->dish->name.'</strong>'
                    .(isset($menu->price) ? '&#160;-&#160;<strong>'.$menu->price.' грн.</strong>' : null)
                    .(isset($menu->dish->ingredients) ? PHP_EOL.'<em>('.$menu->dish->ingredients.')</em>' : null)
                    .(isset($menu->dish->weight) ? ', <em>'.$menu->dish->weight.' г</em>' : null)
                ,
                'parse_mode' => 'HTML',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => "🛒  Добавить",
                                'callback_data' => 'menus='.$menu->id.'=addDishToCart'
                            ],
                        ]
                    ]
                ]
            ]);
        }

//        $inlineKeyboard = [];
//
//        foreach ($category->get('actualMenu') as $menu) {
//            $item = [
//                [
//                    'text' => $menu->dish->name.' ('.$menu->price.' грн.)',
//                    'callback_data' => 'menus='.$menu->id
//                ],
//            ];
//
//            $inlineKeyboard[] = $item;
//        }
//
//        $inlineKeyboard[] = [
//            [
//                'text' => '🔙  Вернуться к выбору каталога',
//                'callback_data' => 'categories'
//            ]
//        ];
//
//        try {
//            $this->deleteMessage();
//        } catch (Exception $e) {
//            Log::error($e);
//        }

//        $this->sendMessage([
//            'text' => '<strong>Меню в категории '.$category["name"].'</strong>',
//            'parse_mode' => 'HTML',
//            'disable_web_page_preview' => false,
//            'reply_markup' => [
//                'inline_keyboard' => $inlineKeyboard
//            ]
//        ]);
    }
}