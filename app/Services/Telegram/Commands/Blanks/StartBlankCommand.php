<?php

namespace App\Services\Telegram\Commands\Blanks;

use WeStacks\TeleBot\Handlers\CommandHandler;

class StartBlankCommand extends CommandHandler
{
    protected static $aliases = ['/start'];
    protected static $description = 'It\'s time to eat';

    public function handle(): void
    {}
}