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

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * System "/start" command
 */
class StartCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Show the PHP Telegram Support Bot start';

    /**
     * @var string
     */
    protected $version = '0.2.0';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * @inheritdoc
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $text = $this->getMessage()->getText(true);

        // Fall back to /help command.
        if (!in_array($text, ['activate', 'rules'])) {
            $text = 'help';
        }

        return $this->getTelegram()->executeCommand($text);
    }
}
