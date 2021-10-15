<?php

namespace App\Services\Telegram\Handlers\Carts;

use App\Models\TelegramUser;
use App\Services\Telegram\Handlers\BaseHandler;
use App\Services\Telegram\Traits\GraphQl\Mutators\CartMutator;
use App\Services\Telegram\Traits\GraphQl\Queries\CartQuery;
use App\Services\Telegram\Traits\HttpClients\GraphQlHttpClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class DeleteHandler extends UpdateHandler
{
    use GraphQlHttpClient, CartMutator, CartQuery;

    private static Collection $callbackData;
    private static mixed $cartData;

    private const QUERY_ACCESSES = [
        'deleteDishFromCart',
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
     * @inheritDoc
     */
    public function handle()
    {
        $user = $this->update->user();
        $hashId = BaseHandler::hashBySecret($user->id);
        $telegramUser = TelegramUser::find($hashId);

        $requestDelete = static::deleteDishFromCart(static::$callbackData->last());
        $dataDelete = static::getGraphQlData($requestDelete, $telegramUser->token);

        $requestGetCart = static::getCart();
        $dataGetCart = static::getGraphQlData($requestGetCart, $telegramUser->token);
        static::$cartData = $dataGetCart->getCart;
        $countData = count(static::$cartData);

        $this->answerCallbackQuery([
            'callback_query_id' => $this->update->callback_query->id,
            'text' => 'Удалено из корзины.'.PHP_EOL.'В Вашей корзине '.($countData === 0 ? 'нет позиций' : 'позиций '.$countData.'.')
        ]);

        $this->editMessageReplyMarkup([
            'message_id' => $this->update->callback_query->message->message_id,
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => "🛒  Добавить",
                            'callback_data' => 'upsertIntoCart='.$dataDelete->deleteDishFromCart->menu->id
                        ],
                    ]
                ]
            ]
        ]);
    }
}