<?php

namespace App\Services\Telegram\Handlers\Carts;

use App\Models\TelegramUser;
use App\Services\Telegram\Handlers\BaseHandler;
use App\Services\Telegram\Traits\GraphQl\Queries\CartQuery;
use App\Services\Telegram\Traits\HttpClients\GraphQlHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class GetCartHandler extends UpdateHandler
{
    use GraphQlHttpClient, CartQuery;

    private static mixed $cartData;

    /**
     * @throws GuzzleException
     */
    public static function trigger(Update $update, TeleBot $bot): bool
    {
        $user = $update->user();
        $hashId = BaseHandler::hashBySecret($user->id);
        $telegramUser = TelegramUser::find($hashId);

        $request = static::getCart();
        $data = static::getGraphQlData($request, $telegramUser->token);
        static::$cartData = $data->getCart;

        return true;
    }

    public function handle()
    {
        $localCommands = $this->bot->getLocalCommands();
        $commands = collect();

        foreach ($localCommands as $command) {
            $commands->add($command->toArray());
        }
        $countData = count(static::$cartData);

        if ($countData === 0) {
            $this->bot->setMyCommands(['commands' => $commands->all()]);
            return;
        }

        $commands->add([
            'command' => '/get_my_cart',
            'description' => 'Посмотреть корзину'
        ]);

        $this->bot->setMyCommands([
            'commands' => $commands->all()
        ]);
    }
}