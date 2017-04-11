<?php

namespace TelegramBot\SupportBot;

use Dotenv\Dotenv;
use Longman\TelegramBot\Exception\TelegramLogException;
use Longman\TelegramBot\TelegramLog;
use NPM\TelegramBotManager\BotManager;

// Composer autoloader.
require_once __DIR__ . '/../vendor/autoload.php';
(new Dotenv(__DIR__ . '/..'))->load();

try {
    $bot = new BotManager([
        // Vitals!
        'api_key'          => getenv('SB_API_KEY'),
        'bot_username'     => getenv('SB_BOT_USERNAME'),
        'secret'           => getenv('SB_SECRET'),
        'webhook'          => getenv('SB_WEBHOOK'),
        'max_connections'  => 5,
        'validate_request' => true,
//        'allowed_updates' => ['message', 'edited_message', 'inline_query', 'chosen_inline_result', 'callback_query'],

        // Optional extras.
        'limiter'          => true,
        'admins'           => getenv('SB_ADMINS') ? array_map('intval', explode(',', getenv('SB_ADMINS') ?: '')) : [],
        'mysql'            => [
            'host'     => getenv('SB_DB_HOST'),
            'user'     => getenv('SB_DB_USER'),
            'password' => getenv('SB_DB_PASS'),
            'database' => getenv('SB_DB_NAME'),
        ],
        'download_path'    => getenv('SB_PATH_DOWNLOAD'),
        'upload_path'      => getenv('SB_PATH_UPLOAD'),
        'commands_paths'   => [__DIR__ . '/commands'],
        'logging'          => [
            // Focus mainly on errors, no need to log everything.
            'error' => __DIR__ . '/../logs/' . getenv('SB_BOT_USERNAME') . '_error.log',
//            'debug'  => __DIR__ . '/../logs/' . getenv('SB_BOT_USERNAME') . '_debug.log',
//            'update' => __DIR__ . '/../logs/' . getenv('SB_BOT_USERNAME') . '_update.log',
        ],
//        'command_configs'  => [],
    ]);

    $bot->run();
} catch (TelegramLogException $e) {
    // Silence... beautiful silence =)
} catch (\Throwable $e) {
    TelegramLog::error($e->getMessage());
}
