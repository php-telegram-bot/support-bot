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
use PhpTelegramBot\Core\Entities\ChatMember;
use PhpTelegramBot\Core\Entities\ServerResponse;
use PhpTelegramBot\Core\Entities\User;
use PhpTelegramBot\Core\Exception\TelegramException;
use PhpTelegramBot\Core\Request;
use TelegramBot\SupportBot\Helpers;

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
    protected $version = '0.4.0';

    /**
     * @var int
     */
    private $chat_id;

    /**
     * @var int
     */
    private $user_id;

    /**
     * @var string
     */
    private $group_name = 'PHP Telegram Support Bot';

    /**
     * @inheritdoc
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $message       = $this->getMessage();
        $this->chat_id = $message->getChat()->getId();
        $this->user_id = $message->getFrom()->getId();

        $this->group_name = $message->getChat()->getTitle();

        ['users' => $new_users, 'bots' => $new_bots] = $this->getNewUsersAndBots();

        // Kick bots if they weren't added by an admin.
        $this->kickDisallowedBots($new_bots);

        return $this->refreshWelcomeMessage($new_users);
    }

    /**
     * Remove existing and send new welcome message.
     *
     * @param array $new_users
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    private function refreshWelcomeMessage(array $new_users): ServerResponse
    {
        if (empty($new_users)) {
            return Request::emptyResponse();
        }

        $new_users_text = implode(', ', array_map(static function (User $new_user) {
            return '<a href="tg://user?id=' . $new_user->getId() . '">' . filter_var($new_user->getFirstName(), FILTER_SANITIZE_SPECIAL_CHARS) . '</a>';
        }, $new_users));

        $text = "Welcome {$new_users_text} to the <b>{$this->group_name}</b> group\n";
        $text .= 'Please remember that this is <b>NOT</b> the Telegram Support Chat.' . PHP_EOL;
        $text .= 'Read the <a href="https://t.me/PHP_Telegram_Bot_Support/5526">Rules</a> that apply here.';

        $welcome_message_sent = $this->replyToChat($text, ['parse_mode' => 'HTML', 'disable_web_page_preview' => true]);
        if (!$welcome_message_sent->isOk()) {
            return Request::emptyResponse();
        }

        $welcome_message = $welcome_message_sent->getResult();

        $new_message_id = $welcome_message->getMessageId();
        $chat_id        = $welcome_message->getChat()->getId();

        if ($new_message_id && $chat_id) {
            Helpers::saveLatestWelcomeMessage($new_message_id);
            Helpers::deleteOldWelcomeMessages();
        }

        return $welcome_message_sent;
    }

    /**
     * Check if the bot has been added by an admin.
     *
     * @return bool
     */
    private function isUserAllowedToAddBot(): bool
    {
        $chat_member = Request::getChatMember([
            'chat_id' => $this->chat_id,
            'user_id' => $this->user_id,
        ])->getResult();

        if ($chat_member instanceof ChatMember) {
            return in_array($chat_member->getStatus(), ['creator', 'administrator'], true);
        }

        return false;
    }

    /**
     * Get an array of all newly added users and bots.
     *
     * @return array
     */
    private function getNewUsersAndBots(): array
    {
        $users = [];
        $bots  = [];

        foreach ($this->getMessage()->getNewChatMembers() as $member) {
            if ($member->getIsBot()) {
                $bots[] = $member;
                continue;
            }

            $users[] = $member;
        }

        return compact('users', 'bots');
    }

    /**
     * Kick bots that weren't added by an admin.
     *
     * @todo: Maybe notify the admins / user that tried to add the bot(s)?
     *
     * @param array $bots
     */
    private function kickDisallowedBots(array $bots): void
    {
        if (empty($bots) || $this->isUserAllowedToAddBot()) {
            return;
        }

        foreach ($bots as $bot) {
            Request::kickChatMember([
                'chat_id' => $this->chat_id,
                'user_id' => $bot->getId(),
            ]);
        }
    }
}
