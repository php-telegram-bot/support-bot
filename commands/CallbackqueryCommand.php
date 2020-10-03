<?php

/**
 * This file is part of the PHP Telegram Support Bot.
 *
 * (c) PHP Telegram Bot Team (https://github.com/php-telegram-bot)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommands\DonateCommand;
use Longman\TelegramBot\Commands\UserCommands\RulesCommand;
use Longman\TelegramBot\Entities\ServerResponse;

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Reply to callback query';

    /**
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * Command execute method
     *
     * @return ServerResponse
     */
    public function execute(): ServerResponse
    {
        $callback_query = $this->getCallbackQuery();
        parse_str($callback_query->getData(), $callback_data);

        if ('donate' === $callback_data['command']) {
            return DonateCommand::handleCallbackQuery($callback_query, $callback_data);
        }

        if ('rules' === $callback_data['command']) {
            return RulesCommand::handleCallbackQuery($callback_query, $callback_data);
        }

        return $callback_query->answer();
    }
}
