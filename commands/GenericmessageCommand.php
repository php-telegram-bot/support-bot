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

namespace PhpTelegramBot\Core\Commands\SystemCommands;

use PhpTelegramBot\Core\Commands\SystemCommand;
use PhpTelegramBot\Core\Entities\ServerResponse;
use PhpTelegramBot\Core\Exception\TelegramException;

/**
 * Generic message command
 */
class GenericmessageCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage';

    /**
     * @var string
     */
    protected $description = 'Handle generic message';

    /**
     * @var string
     */
    protected $version = '0.2.0';

    /**
     * Execute command
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        // Handle new chat members.
        if ($this->getMessage()->getNewChatMembers()) {
            return $this->getTelegram()->executeCommand('newchatmembers');
        }

        // Handle posts forwarded from channels.
        if ($this->getMessage()->getForwardFrom()) {
            return $this->getTelegram()->executeCommand('id');
        }

        return parent::execute();
    }
}
