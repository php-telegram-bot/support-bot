<?php

namespace TelegramBot\SupportBot;

use Dotenv\Dotenv;
use Longman\TelegramBot\Exception\TelegramLogException;
use Longman\TelegramBot\TelegramLog;
use TelegramBot\TelegramBotManager\BotManager;

// Composer autoloader.
require_once __DIR__ . '/../vendor/autoload.php';
(new Dotenv(__DIR__ . '/..'))->load();

try {
    // Vitals!
    $params = [
        'api_key' => getenv('TG_API_KEY'),
    ];
    foreach (['bot_username', 'secret'] as $extra) {
        if ($param = getenv('TG_' . strtoupper($extra))) {
            $params[$extra] = $param;
        }
    }

    // Database connection.
    if (getenv('TG_DB_HOST')) {
        $params['mysql'] = [
            'host'     => getenv('TG_DB_HOST'),
            'user'     => getenv('TG_DB_USER'),
            'password' => getenv('TG_DB_PASSWORD'),
            'database' => getenv('TG_DB_DATABASE'),
        ];
    }

    // Optional extras.
    $extras = ['admins', 'botan', 'commands', 'cron', 'limiter', 'logging', 'paths', 'valid_ips', 'webhook'];
    foreach ($extras as $extra) {
        if ($param = getenv('TG_' . strtoupper($extra))) {
            $params[$extra] = json_decode($param, true);
        }
    }

    $bot = new BotManager($params);
    $bot->run();
} catch (TelegramLogException $e) {
    // Silence... beautiful silence =)
} catch (\Throwable $e) {
    TelegramLog::error($e->getMessage());
}
