<?php

namespace App\Services\Telegram\Traits\GraphQl\Queries;

trait Cart
{
    public static function getCart(): array
    {
        $query = <<<GQL
            query {
                getCart {
                    id
                    count
                    menu {
                        id
                        price
                        dish {
                            name
                            ingredients
                            weight
                        }
                    }
                }
            }
            GQL;

        return [
            'query' => $query,
        ];
    }
}