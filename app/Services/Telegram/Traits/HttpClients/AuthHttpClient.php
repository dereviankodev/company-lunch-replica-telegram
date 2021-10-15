<?php

namespace App\Services\Telegram\Traits\HttpClients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

trait AuthHttpClient
{
    /**
     * @throws GuzzleException
     */
    public static function getAuthData($uri, $query)
    {
        $client = new Client([
            'base_uri' => config('telebot.bots.QuartSoftLunchBot.request_base_uri'),
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);

        $response = $client->get($uri, [
            'query' => $query
        ]);

        $json = $response->getBody()->getContents();
        $body = json_decode($json);

        return $body->data;
    }
}