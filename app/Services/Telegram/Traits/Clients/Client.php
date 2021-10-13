<?php

namespace App\Services\Telegram\Traits\Clients;

use GuzzleHttp\Client as Http;
use GuzzleHttp\Exception\GuzzleException;

trait Client
{
    /**
     * @throws GuzzleException
     */
    public static function clientAuth($uri, $query)
    {
        $client = new Http([
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

    /**
     * @throws GuzzleException
     */
    public static function clientGraphQl($query, $token): mixed
    {
        $client = new Http([
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