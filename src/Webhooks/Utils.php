<?php declare(strict_types=1);

/**
 * This file is part of the PHP Telegram Support Bot.
 *
 * (c) PHP Telegram Bot Team (https://github.com/php-telegram-bot)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TelegramBot\SupportBot\Webhooks;

class Utils
{
    /**
     * Log the incoming webhook data.
     *
     * @param string $path
     */
    public static function logWebhookData($path): void
    {
        $f = fopen($path, 'ab+');
        fwrite($f, sprintf(
            "%s\ninput:  %s\nGET:    %s\nPOST:   %s\nSERVER: %s\n\n",
            date('Y-m-d H:i:s'),
            file_get_contents('php://input'),
            json_encode($_GET),
            json_encode($_POST),
            json_encode($_SERVER)
        ));
        fclose($f);
    }
}
