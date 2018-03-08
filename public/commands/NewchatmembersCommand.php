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
use Longman\TelegramBot\Entities\ChatMember;
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
    protected $version = '0.2.0';

    /**
     * @var int
     */
    private $chat_id;

    /**
     * @var int
     */
    private $user_id;

    /**
     * @inheritdoc
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute(): ServerResponse
    {
        $message       = $this->getMessage();
        $this->chat_id = $message->getChat()->getId();
        $this->user_id = $message->getFrom()->getId();

        $group_name = $message->getChat()->getTitle();

        ['users' => $new_users, 'bots' => $new_bots] = $this->getNewUsersAndBots();

        // Kick bots if they weren't added by an admin.
        $this->kickDisallowedBots($new_bots);

        $new_users_text = implode(', ', array_map(function (User $new_user) {
            return '<a href="tg://user?id=' . $new_user->getId() . '">' . filter_var($new_user->getFirstName(), FILTER_SANITIZE_SPECIAL_CHARS) . '</a>';
        }, $new_users));

        if ($new_users_text === '') {
            return Request::emptyResponse();
        }

        $text = "Welcome {$new_users_text} to the <b>{$group_name}</b> group\n";
        $text .= 'Please remember that this is <b>NOT</b> the Telegram Support Chat.' . PHP_EOL;
        $text .= 'Read the <a href="https://telegram.me/PHP_Telegram_Support_Bot?start=rules">Rules</a> that apply here.';

        return $this->replyToChat($text, ['parse_mode' => 'HTML', 'disable_web_page_preview' => true]);
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
            return \in_array($chat_member->getStatus(), ['creator', 'administrator'], true);
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
