<?php

namespace App\Services\Telegram\Traits\GraphQl\Queries;

trait Catalog
{
    public static function categories(): array
    {
        $query = <<<GQL
            query {
                categories {
                    id
                    name
                    actualMenu {
                        id
                    }
                }
            }
            GQL;

        return [
            'query' => $query,
        ];
    }

    public static function category($categoryId): array
    {
        $query = <<<GQL
            query (\$id: ID!) {
                category(id: \$id) {
                    id
                    name
                    actualMenu {
                        id
                        price
                        dish {
                            id
                            name
                            ingredients
                            weight
                        }
                    }
                }
            }
            GQL;

        $variables = [
            'id' => $categoryId
        ];

        return [
            'query' => $query,
            'variables' => $variables
        ];
    }
}