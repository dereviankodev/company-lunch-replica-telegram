<?php

namespace App\Services\Telegram\Traits\GraphQl\Queries;

trait Auth
{
    public static function me(): array
    {
        $query = <<<GQL
            query {
                me {
                    id
                    name
                }
            }
            GQL;

        return [
            'query' => $query,
        ];
    }
}