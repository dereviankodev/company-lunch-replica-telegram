<?php

namespace App\Services\Telegram\Handlers\Categories;

use App\Models\TelegramUser;
use App\Services\Telegram\Handlers\BaseHandler;
use App\Services\Telegram\Traits\GraphQl\Queries\CartQuery;
use App\Services\Telegram\Traits\GraphQl\Queries\CategoryQuery;
use App\Services\Telegram\Traits\HttpClients\GraphQlHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class ItemHandler extends UpdateHandler
{
    use GraphQlHttpClient, CategoryQuery, CartQuery;

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

        $request = static::categoryDishes(static::$callbackData->last());
        $data = static::getGraphQlData($request, $telegramUser->token);
        $category = collect($data->category);

        $this->sendMessage([
            'text' => '🍽&#160;&#160;<strong>'.$category["name"].':</strong>',
            'parse_mode' => 'HTML'
        ]);

        $requestGetCart = static::getCart();
        $dataGetCart = static::getGraphQlData($requestGetCart, $telegramUser->token);
        $cartData = collect($dataGetCart->getCart);

        foreach ($category->get('actualMenu') as $menu) {
            $inCard = false;
            foreach ($cartData as $cartItem) {
                $cartItem->menu->id !== $menu->id || ($inCard = $cartItem->id);
            }

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
                            $inCard
                                ? [
                                    'text' => "❌  Убрать",
                                    'callback_data' => 'deleteDishFromCart='.$inCard
                                ]
                                : [
                                    'text' => "🛒  Добавить",
                                    'callback_data' => 'upsertIntoCart='.$menu->id
                                ]
                        ]
                    ]
                ]
            ]);
        }
    }
}