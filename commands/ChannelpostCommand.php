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

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

/**
 * Handle post sent from channel.
 */
class ChannelpostCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'channelpost';

    /**
     * @var string
     */
    protected $description = 'Handle Channel Post';

    /**
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * @inheritdoc
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        if ($this->getChannelPost()->getCommand() === 'id') {
            return $this->getTelegram()->executeCommand('id');
        }

        return Request::emptyResponse();
    }
}
