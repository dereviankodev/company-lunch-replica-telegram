<?php

namespace App\Services\Telegram\Traits\GraphQl\Queries;

trait Auth
{
    public static function me(): string
    {
        return <<<GQL
            query {
                me {
                    id
                    name
                }
            }
            GQL;
    }
}