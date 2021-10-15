<?php

namespace App\Services\Telegram\Traits\HttpClients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

trait GraphQlHttpClient
{
    /**
     * @throws GuzzleException
     */
    public static function getGraphQlData($query, $token): mixed
    {
        $client = new Client([
            'base_uri' => config('telebot.bots.QuartSoftLunchBot.request_base_uri'),
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ]
        ]);

        $response = $client->post('graphql', [
            'json' => [
                'query' => $query['query'],
                'variables' => $query['variables'] ?? null
            ]
        ]);

        $json = $response->getBody()->getContents();
        $body = json_decode($json);

        return $body->data;
    }
}