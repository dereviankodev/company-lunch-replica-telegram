<?php

namespace App\Services\Telegram\Commands;

use App\Models\TelegramUser;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Handlers\CommandHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class StartCommand extends CommandHandler
{
    protected static $aliases = ['/start'];
    protected static $description = 'It\'s time to eat';
    private const BASE_URI = 'http://lunch-replica.stage2.quartsoft.com/telegram/';
    private Client $client;

    public function __construct(TeleBot $bot, Update $update)
    {
        parent::__construct($bot, $update);

        $this->client = new Client([
            'base_uri' => static::BASE_URI
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function handle()
    {
        // 1 Пришел пользователь
        $user = $this->update->user();
        var_dump('1 '.$user);

        // 2 Ботам сразу пока
        if ($user->is_bot) {
            var_dump('2 '.$user);
            $this->messageForBot();
            return;
        }

        // 3 Хешируем ид
        $hashId = $this->hashBySecret($user->id);
        var_dump('3 '.$hashId);

        // 4 Пользователь привязан?
        $isFamiliarUser = $this->isFamiliarUser($hashId);
        var_dump('4 '.$isFamiliarUser);

        // 5 Пользователь в системе?
        $telegramUser = TelegramUser::find($hashId);
        var_dump('5 '.($telegramUser->username ?? null));

        // 6 пользователь не привязан - незнакомец или сделать незнакомцем
        if (!$isFamiliarUser) {
            var_dump('6 Пользователь не привязан');

            // 7 Если есть в нашей бд - удаляем
            if (!is_null($telegramUser)) {
                var_dump('7 Пользователь удален из бд');
                $telegramUser->delete();
            }

            $this->messageForStranger();
            return;
        }

        // Пользовтель привязан.
        // Он есть у нас?
        // У него есть токен?

        if (is_null($telegramUser)) {
            var_dump('8 Нет в БД');
            $token = $this->getToken($hashId);
            var_dump('9 '.$token);

            $telegramUser = TelegramUser::create([
                    'id' => $hashId,
                    'is_bot' => $user->is_bot,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'username' => $user->username,
                    'language_code' => $user->language_code,
                    'token' => $token,
            ]);
            var_dump('10 '.$telegramUser->username);
            $this->messageForNewUser();
            return;
        }

        $this->messageForFriend();
        var_dump('11 Это правда ты?)');
    }

    private function isFamiliarUser($hashId): bool
    {
        try {
            $res = $this->client->get( 'familiar-user', [
                'query' => ['id' => $hashId]
            ]);
        } catch (GuzzleException $e) {
            return false;
        }

        $body = json_decode($res->getBody(), true);

        return $body['data']['in_system'];
    }

    private function getToken($hashId): ?string
    {
        try {
            $res = $this->client->get('issuing-token', [
                'query' => ['id' => $hashId]
            ]);
        } catch (GuzzleException $e) {
            return null;
        }

        $body = json_decode($res->getBody(), true);

        return 'Bearer '.$body['data']['token'];
    }

    /**
     * @param $id
     * @return string
     */
    private function hashBySecret($id): string
    {
        if (is_null($botToken = config('telebot.bots.QuartSoftLunchBot.token'))) {
            Log::error('No telegram token');
        }

        $secretKey = hash('sha256', $botToken, true);

        return hash_hmac('sha256', $id, $secretKey);
    }

    private function messageForBot()
    {
        $this->sendMessage([
            'text' => 'Bots don\'t eat! Bye!'
        ]);
    }

    private function messageForStranger()
    {
        $this->sendMessage([
            'text' => 'Hi stranger! If you think this is a mistake, perhaps you should link your telegram account'
                .' in the QuartSoft lunch admin panel. I am sure see you soon!'
        ]);
    }

    private function messageForNewUser()
    {
        $this->sendMessage([
            'text' => 'Super! Nice to meet you!'
        ]);
    }

    private function messageForFriend()
    {
        $this->sendMessage([
            'text' => 'Glad to see again!'
        ]);
    }
}