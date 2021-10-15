<?php

namespace App\Services\Telegram\Handlers;

use App\Models\TelegramUser;
use App\Services\Telegram\Handlers\Auth\BotHandler;
use App\Services\Telegram\Handlers\Auth\StrangerHandler;
use App\Services\Telegram\Traits\HttpClients\AuthHttpClient;
use App\Services\Telegram\Traits\GraphQl\Queries\AuthQuery;
use App\Services\Telegram\Traits\HttpClients\GraphQlHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

abstract class BaseHandler extends UpdateHandler
{
    use AuthHttpClient, GraphQlHttpClient, AuthQuery;

    /**
     * @throws GuzzleException
     */
    public static function baseTrigger(Update $update, TeleBot $bot): bool
    {
        if ($update->user()->is_bot) {
            static::botMessage($bot, $update);

            return false;
        }

        $user = $update->user();
        $hashId = static::hashBySecret($user->id);
        $telegramUser = TelegramUser::find($hashId);

        if (is_null($telegramUser)) {
            $token = BaseHandler::getToken($hashId);

            if (is_null($token)) {
                static::strangerMessage($bot, $update);

                return false;
            }

            $telegramUser = TelegramUser::create([
                'id' => $hashId,
                'is_bot' => $user->is_bot,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'language_code' => $user->language_code,
                'token' => $token,
            ]);
        }

        $request = static::me();
        $data = static::getGraphQlData($request, $telegramUser->token);

        if (is_null($data->me)) {
            $token = BaseHandler::getToken($hashId);

            if (is_null($token)) {
                $telegramUser->delete();
                static::strangerMessage($bot, $update);

                return false;
            }

            $telegramUser->update([
                'token' => $token,
            ]);
        }

        return true;
    }

    public static function hashBySecret(int $id): string
    {
        if (is_null($botToken = config('telebot.bots.QuartSoftLunchBot.token'))) {
            Log::error('No telegram token');
        }

        $secretKey = hash('sha256', $botToken, true);

        return hash_hmac('sha256', $id, $secretKey);
    }

    public static function getToken($hashId): ?string
    {
        $uri = 'telegram/issuing-token';
        $query = ['id' => $hashId];

        try {
            $body = static::getAuthData($uri, $query);
        } catch (GuzzleException) {
            return null;
        }

        return 'Bearer '.$body->token;
    }

    private static function botMessage($bot, $update)
    {
        $botHandler = new BotHandler($bot, $update);
        $botHandler->handle();
    }

    private static function strangerMessage($bot, $update)
    {
        $strangerHandler = new StrangerHandler($bot, $update);
        $strangerHandler->handle();
    }
}