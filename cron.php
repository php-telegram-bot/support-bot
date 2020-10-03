<?php

/**
 * This file is part of the PHP Telegram Support Bot.
 *
 * (c) PHP Telegram Bot Team (https://github.com/php-telegram-bot)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace TelegramBot\SupportBot;

use Dotenv\Dotenv;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;

// Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';
Dotenv::create(__DIR__)->load();

try {
    $telegram = new Telegram(getenv('TG_API_KEY'), getenv('TG_BOT_USERNAME'));
    $telegram->enableMySql([
        'host'     => getenv('TG_DB_HOST'),
        'port'     => getenv('TG_DB_PORT'),
        'user'     => getenv('TG_DB_USER'),
        'password' => getenv('TG_DB_PASSWORD'),
        'database' => getenv('TG_DB_DATABASE'),
    ]);

    // Handle expired activations.
    Helpers::handleExpiredActivations();
} catch (\Throwable $e) {
    TelegramLog::error($e->getMessage());
}
