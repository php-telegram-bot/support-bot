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
use Exception;
use PhpTelegramBot\Core\Exception\TelegramLogException;
use PhpTelegramBot\Core\TelegramLog;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\NullLogger;
use TelegramBot\TelegramBotManager\BotManager;
use Throwable;

// Composer autoloader.
require_once __DIR__ . '/../vendor/autoload.php';
Dotenv::create(__DIR__ . '/..')->load();

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
    $extras = ['admins', 'commands', 'cron', 'limiter', 'paths', 'valid_ips', 'webhook'];
    foreach ($extras as $extra) {
        if ($param = getenv('TG_' . strtoupper($extra))) {
            $params[$extra] = json_decode($param, true);
        }
    }

    initLogging();

    $bot = new BotManager($params);
    $bot->run();
} catch (TelegramLogException $e) {
    // Silence... beautiful silence =)
} catch (Throwable $e) {
    TelegramLog::error($e->getMessage());
}

/**
 * Initialise the logging.
 *
 * @throws Exception
 */
function initLogging(): void
{
    // Logging.
    $logging_paths = json_decode(getenv('TG_LOGGING'), true) ?? [];

    $debug_log  = $logging_paths['debug'] ?? null;
    $error_log  = $logging_paths['error'] ?? null;
    $update_log = $logging_paths['update'] ?? null;

    // Main logger that handles all 'debug' and 'error' logs.
    $logger = ($debug_log || $error_log) ? new Logger('telegram_bot') : new NullLogger();
    $debug_log && $logger->pushHandler((new StreamHandler($debug_log, Logger::DEBUG))->setFormatter(new LineFormatter(null, null, true)));
    $error_log && $logger->pushHandler((new StreamHandler($error_log, Logger::ERROR))->setFormatter(new LineFormatter(null, null, true)));

    // Updates logger for raw updates.
    $update_logger = new NullLogger();
    if ($update_log) {
        $update_logger = new Logger('telegram_bot_updates');
        $update_logger->pushHandler((new StreamHandler($update_log, Logger::INFO))->setFormatter(new LineFormatter('%message%' . PHP_EOL)));
    }

    TelegramLog::initialize($logger, $update_logger);
}
