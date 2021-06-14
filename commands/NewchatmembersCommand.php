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

use LitEmoji\LitEmoji;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\ChatMember;
use Longman\TelegramBot\Entities\ChatPermissions;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use TelegramBot\SupportBot\Helpers;

/**
 * Send a welcome message to new chat members.
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
    protected $version = '0.5.0';

    /**
     * @var Message
     */
    private Message $message;

    /**
     * @var int
     */
    private int $chat_id;

    /**
     * @var int
     */
    private int $user_id;

    /**
     * @var string
     */
    private string $group_name = 'PHP Telegram Support Bot';

    /**
     * @inheritdoc
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $this->message = $this->getMessage();
        $this->chat_id = $this->message->getChat()->getId();
        $this->user_id = $this->message->getFrom()->getId();

        $this->group_name = $this->message->getChat()->getTitle();

        ['users' => $new_users, 'bots' => $new_bots] = $this->getNewUsersAndBots();

        // Kick bots if they weren't added by an admin.
        $this->kickDisallowedBots($new_bots);

        // Restrict all permissions for new users.
        $this->restrictNewUsers($new_users);

        // Set the joined date for all new group members.
        $this->updateUsersJoinedDate($new_users);

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

        $text = ":wave: Welcome {$new_users_text} to the <b>{$this->group_name}</b> group\n\n";
        $text .= 'Please read and agree to the rules before posting here, thank you!';

        $welcome_message_sent = $this->replyToChat(
            LitEmoji::encodeUnicode($text),
            [
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => true,
                'disable_notification'     => true,
                'reply_markup'             => new InlineKeyboard([
                    ['text' => LitEmoji::encodeUnicode(':orange_book: Read the Rules'), 'url' => 'https://t.me/' . getenv('TG_BOT_USERNAME') . '?start=rules'],
                ]),
            ]
        );
        if (!$welcome_message_sent->isOk()) {
            return Request::emptyResponse();
        }

        /** @var Message $welcome_message */
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

        foreach ($this->message->getNewChatMembers() as $member) {
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

    /**
     * Write users join date to DB.
     *
     * @param User[] $new_users
     *
     * @return bool
     */
    private function updateUsersJoinedDate(array $new_users): bool
    {
        $new_users_ids = array_map(static function (User $user) {
            return $user->getId();
        }, $new_users);

        // Update "Joined Date" for new users.
        return DB::getPdo()->prepare("
            UPDATE " . TB_USER . "
            SET `joined_at` = ?
            WHERE `id` IN (?)
        ")->execute([date('Y-m-d H:i:s'), implode(',', $new_users_ids)]);
    }

    /**
     * Restrict permissions in support group for passed users.
     *
     * @param User[] $new_users
     *
     * @return array
     */
    private function restrictNewUsers(array $new_users): array
    {
        $responses = [];

        foreach ($new_users as $new_user) {
            $user_id             = $new_user->getId();
            $responses[$user_id] = Request::restrictChatMember([
                'chat_id'     => getenv('TG_SUPPORT_GROUP_ID'),
                'user_id'     => $user_id,
                'permissions' => new ChatPermissions([
                    'can_send_messages'         => false,
                    'can_send_media_messages'   => false,
                    'can_send_polls'            => false,
                    'can_send_other_messages'   => false,
                    'can_add_web_page_previews' => false,
                    'can_change_info'           => false,
                    'can_invite_users'          => false,
                    'can_pin_messages'          => false,
                ]),
            ]);
        }

        return $responses;
    }
}
