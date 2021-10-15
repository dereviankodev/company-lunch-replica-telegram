<?php

namespace App\Services\Telegram\Handlers\Categories;

use App\Models\TelegramUser;
use App\Services\Telegram\Handlers\BaseHandler;
use App\Services\Telegram\Traits\GraphQl\Queries\CategoryQuery;
use App\Services\Telegram\Traits\HttpClients\GraphQlHttpClient;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class ListHandler extends UpdateHandler
{
    use GraphQlHttpClient, CategoryQuery;

    private static Collection $callbackData;

    private const QUERY_ACCESSES = [
        'categories',
    ];

    public static function trigger(Update $update, TeleBot $bot): bool
    {
        if (!isset($update?->callback_query)) {
            return false;
        }

        static::$callbackData = Str::of($update->callback_query->data)->trim()->explode('=');

        return collect(static::QUERY_ACCESSES)->contains(static::$callbackData->first());
    }

    /**
     * @throws GuzzleException
     */
    public function handle()
    {
        $this->answerCallbackQuery([
            'callback_query_id' => $this->update->callback_query->id
        ]);

        $user = $this->update->user();
        $hashId = BaseHandler::hashBySecret($user->id);
        $telegramUser = TelegramUser::find($hashId);

        $request = static::categories();
        $data = static::getGraphQlData($request, $telegramUser->token);
        $categories = collect($data->categories)->filter(function ($value) {
            return !empty($value->actualMenu);
        })->all();

        $inlineKeyboard = [];

        foreach ($categories as $category) {
            $item = [
                [
                    'text' => $category->name.' ('.count($category->actualMenu).')'
                        /*.(count($category->actualMenu['cart']) === 0 ?: ', 🛒  ('.count($category->actualMenu['cart']).')')*/,
                    'callback_data' => 'category='.$category->id
                ]
            ];

            $inlineKeyboard[] = $item;
        }

        try {
            $this->deleteMessage();
        } catch (Exception $e) {
            Log::error($e);
        }

        $this->sendMessage([
            'text' => '<strong>Меню на '.date('Y-m-d').'</strong>',
            'parse_mode' => 'HTML',
            'reply_markup' => [
                'inline_keyboard' => $inlineKeyboard
            ]
        ]);
    }
}