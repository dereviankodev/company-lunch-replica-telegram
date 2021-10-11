<?php

namespace App\Services\Telegram\Traits\GraphQl\Queries;

trait Catalog
{
    public static function categories(): string
    {
        return <<<GQL
            query {
                categories {
                    id
                    name
                    img_path
                }
            }
            GQL;
    }
}