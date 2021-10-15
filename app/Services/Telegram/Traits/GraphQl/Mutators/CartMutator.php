<?php

namespace App\Services\Telegram\Traits\GraphQl\Mutators;

trait CartMutator
{
    public static function upsertIntoCart($menu_id, $count = 1): array
    {
        $query = <<<GQL
            mutation (\$menu_id: ID!, \$count: Int!) {
                upsertIntoCart(menu_id: \$menu_id, count: \$count) {
                    id
                    count
                    menu {
                        id
                    }
                }
            }
            GQL;

        $variables = [
            'menu_id' => $menu_id,
            'count' => $count
        ];

        return [
            'query' => $query,
            'variables' => $variables
        ];
    }

    public static function deleteDishFromCart($id): array
    {
        $query = <<<GQL
            mutation (\$id: ID!) {
                deleteDishFromCart(id: \$id) {
                    id
                    count
                    menu {
                        id
                    }
                }
            }
            GQL;

        $variables = [
            'id' => $id,
        ];

        return [
            'query' => $query,
            'variables' => $variables
        ];
    }

    public static function clearCart(): array
    {
        $query = <<<GQL
            mutation {
                clearCart {
                    id
                    count
                    menu {
                        id
                    }
                }
            }
            GQL;

        return [
            'query' => $query
        ];
    }
}