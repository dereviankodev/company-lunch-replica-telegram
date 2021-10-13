<?php

namespace App\Services\Telegram\Handlers\Menus;

use App\Services\Telegram\Traits\Clients\Client;
use App\Services\Telegram\Traits\GraphQl\Queries\Catalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class IndexMenuHandler extends UpdateHandler
{
    use Catalog;
    use Client;

    private static Collection $callbackData;

    private const QUERY_ACCESSES = [
        'menu',
    ];

    /**
     * @inheritDoc
     */
    public static function trigger(Update $update, TeleBot $bot)
    {
        if (!isset($update?->callback_query)) {
            return false;
        }

        static::$callbackData = Str::of($update->callback_query->data)->trim()->explode('=');

        return collect(static::QUERY_ACCESSES)->contains(static::$callbackData->first());
    }

    /**
     * @inheritDoc
     */
    public function handle()
    {
        // TODO: Implement handle() method.
    }
}