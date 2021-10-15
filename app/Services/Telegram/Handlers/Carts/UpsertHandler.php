<?php

namespace App\Services\Telegram\Handlers\Carts;

use App\Models\TelegramUser;
use App\Services\Telegram\Handlers\BaseHandler;
use App\Services\Telegram\Traits\GraphQl\Mutators\CartMutator;
use App\Services\Telegram\Traits\GraphQl\Queries\CartQuery;
use App\Services\Telegram\Traits\HttpClients\GraphQlHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class UpsertHandler extends UpdateHandler
{
    use GraphQlHttpClient, CartMutator, CartQuery;

    private static Collection $callbackData;
    private static mixed $cartData;

    private const QUERY_ACCESSES = [
        'upsertIntoCart',
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
        $user = $this->update->user();
        $hashId = BaseHandler::hashBySecret($user->id);
        $telegramUser = TelegramUser::find($hashId);

        $requestUpsert = static::upsertIntoCart(static::$callbackData->last());
        $dataUpsert = static::getGraphQlData($requestUpsert, $telegramUser->token);

        $requestGetCart = static::getCart();
        $dataGetCart = static::getGraphQlData($requestGetCart, $telegramUser->token);
        static::$cartData = $dataGetCart->getCart;
        $countData = count(static::$cartData);

        $this->answerCallbackQuery([
            'callback_query_id' => $this->update->callback_query->id,
            'text' => 'Добавлено в корзину.'.PHP_EOL.'В Вашей корзине позиций: '.$countData.'.'
        ]);

        $this->editMessageReplyMarkup([
            'message_id' => $this->update->callback_query->message->message_id,
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => "❌  Убрать",
                            'callback_data' => 'deleteDishFromCart='.$dataUpsert->upsertIntoCart->id
                        ],
                    ]
                ]
            ]
        ]);
    }
}