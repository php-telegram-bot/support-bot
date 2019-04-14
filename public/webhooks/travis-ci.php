<?php declare(strict_types=1);

/**
 * This file is part of the PHP Telegram Support Bot.
 *
 * (c) PHP Telegram Bot Team (https://github.com/php-telegram-bot)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use NPM\ServiceWebhookHandler\Handlers\TravisCIHandler;
use TelegramBot\SupportBot\Webhooks\Utils;

// Composer autoloader.
require_once __DIR__ . '/../../vendor/autoload.php';
Dotenv\Dotenv::create(__DIR__ . '/../..')->load();

$webhook = new TravisCIHandler();
if (!$webhook->validate()) {
    http_response_code(404);
    die;
}

// Save all incoming data to a log file for future reference.
Utils::logWebhookData(getenv('TG_LOGS_DIR') . '/' . getenv('TG_BOT_USERNAME') . '_webhook_travis-ci.log');
