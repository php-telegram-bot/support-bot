<?php declare(strict_types=1);
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
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Request;

/**
 * New chat members command
 */
class NewchatmembersCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'newchatmembers';

    /**
     * @var string
     */
    protected $description = 'New Chat Members';

    /**
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * @inheritdoc
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute(): ServerResponse
    {
        $message    = $this->getMessage();
        $group_name = $message->getChat()->getTitle();

        // Only welcome actual users, not bots.
        $new_members = implode(', ', array_filter(array_map(function (User $member) {
            return $member->getIsBot() ? null : $member->tryMention();
        }, $message->getNewChatMembers())));

        if (empty($new_members)) {
            return Request::emptyResponse();
        }

        $text = "Welcome {$new_members} to the {$group_name} group\n";
        $text .= 'Please read the /rules that apply here.';

        return $this->replyToChat($text);
    }
}
