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

namespace PhpTelegramBot\Core\Commands\UserCommands;

use PhpTelegramBot\Core\Commands\UserCommand;
use PhpTelegramBot\Core\Entities\ServerResponse;

/**
 * User "/rules" command
 */
class RulesCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'rules';

    /**
     * @var string
     */
    protected $description = 'Show the PHP Telegram Support Bot rules';

    /**
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * @var string
     */
    protected $usage = '/rules';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * @inheritdoc
     */
    public function execute(): ServerResponse
    {
        $text = <<<EOT
Rules:  `English only | No Spamming or Nonsense Posting | No Bots`

¬ **English only**
Please keep your conversations in english inside this chatroom, otherwise your message will be deleted

¬ **No Spamming or Nonsense Posting** 
Don't spam Stickers or send Messages with useless Content. When repeated you may be kicked or banned

¬ **No Bots**
Please do not add a Bot inside this Chat without asking the Admins first. Feel free to mention the Bot in a Message

Also keep in mind that this PHP Telegram Bot Support Chat applies only for the PHP Telegram Bot library
https://github.com/php-telegram-bot/core

EOT;

        return $this->replyToChat($text, ['parse_mode' => 'markdown']);
    }
}
