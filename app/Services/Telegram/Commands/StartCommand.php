<?php

namespace App\Services\Telegram\Commands;

use App\Models\TelegramUser;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Handlers\CommandHandler;

class StartCommand extends CommandHandler
{
    protected static $aliases = ['/start'];
    protected static $description = 'It\'s time to eat';

    public function handle()
    {
        $user = $this->update->user();
        $id = $user->id;
        var_dump($user);

        if ($user->is_bot) {
            $this->messageForBot();
            return;
        }

        $telegramUser = TelegramUser::where('id', $id)->whereNotNull('token')->first();
        var_dump($telegramUser);
        if ($telegramUser) {
            $this->sendMessage([
                'text' => 'Glad to see again!'
            ]);
            var_dump($telegramUser->token);
            return;
        }

        $token = $this->getRemoteToken($id);
        var_dump($token);

        if (is_null($token)) {
            $this->sendMessage([
                'text' => 'Hi stranger! If you think this is a mistake, perhaps you should link your telegram account in the QuartSoft lunch admin panel. I am sure see you soon!'
            ]);
            return;
        }

        TelegramUser::updateOrCreate(
            ['id' => $id],
            [
                'is_bot' => $user->is_bot,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'language_code' => $user->language_code,
                'token' => $token,
            ]
        );

        $this->sendMessage([
            'text' => 'Super! Nice to meet you!'
        ]);
    }

    private function messageForBot()
    {
        $this->sendMessage([
            'text' => 'Bots don\'t eat! Bye!'
        ]);
    }

    /**
     * @throws GuzzleException
     */
    private function getRemoteToken($id)
    {
        $hashId = $this->hashBySecret($id);
        var_dump($hashId);
        $client = new Client();
        try {
            $res = $client->request('GET', 'http://lunch-replica.stage2.quartsoft.com/telegram/token', [
                'query' => ['id' => $hashId]
            ]);
        } catch (ClientException $e) {
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
}