<?php

namespace App\Services\Telegram\Traits\GraphQl\Queries;

trait AuthQuery
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