<?php

namespace App\Services\Telegram\Traits\GraphQl\Queries;

trait CartQuery
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
                            category {
                                id
                            }
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