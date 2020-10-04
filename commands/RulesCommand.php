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

use LitEmoji\LitEmoji;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ChatPermissions;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use PDO;

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

    public static function handleCallbackQuery(CallbackQuery $callback_query, array $callback_data): ?ServerResponse
    {
        if ('agree' === $callback_data['action'] ?? null) {
            $message         = $callback_query->getMessage();
            $chat_id         = $message->getChat()->getId();
            $clicked_user_id = $callback_query->getFrom()->getId();

            // If the user is already activated, keep the initial activation date.
            $activated = DB::getPdo()->prepare("
                UPDATE " . TB_USER . "
                SET `activated_at` = ?
                WHERE `id` = ?
                  AND `activated_at` IS NULL
            ")->execute([date('Y-m-d H:i:s'), $clicked_user_id]);

            if (!$activated) {
                return $callback_query->answer([
                    'text'       => 'Something went wrong, please try again later.',
                    'show_alert' => true,
                ]);
            }

            Request::restrictChatMember([
                'chat_id'     => getenv('TG_SUPPORT_GROUP_ID'),
                'user_id'     => $clicked_user_id,
                'permissions' => new ChatPermissions([
                    'can_send_messages'         => true,
                    'can_send_media_messages'   => true,
                    'can_add_web_page_previews' => true,
                    'can_invite_users'          => true,
                ]),
            ]);

            Request::editMessageReplyMarkup([
                'chat_id'      => $chat_id,
                'message_id'   => $message->getMessageId(),
                'reply_markup' => new InlineKeyboard([
                    ['text' => LitEmoji::encodeUnicode(':white_check_mark: Ok! Go to Bot Support group...'), 'url' => 'https://t.me/' . getenv('TG_SUPPORT_GROUP_USERNAME')],
                ]),
            ]);

            return $callback_query->answer([
                'text'       => 'Thanks for agreeing to the rules. You may now post in the support group.',
                'show_alert' => true,
            ]);
        }

        return $callback_query->answer();
    }

    /**
     * @inheritdoc
     */
    public function execute(): ServerResponse
    {
        $text = "
Rules:  `English only | No Spamming or Nonsense Posting | No Bots`

**:uk: English only**
Please keep your conversations in English inside this chatroom, otherwise your message will be deleted.

**:do_not_litter: No Spamming or Nonsense Posting**
Don't spam or send Messages with useless Content. When repeated you may be kicked or banned.

**:robot: No Bots**
Please do not add a Bot inside this Chat without asking the Admins first. Feel free to mention the Bot in a Message

Also keep in mind that the [PHP Telegram Bot Support Chat](https://t.me/PHP_Telegram_Bot_Support) applies only for the [PHP Telegram Bot library](https://github.com/php-telegram-bot/core).
";

        $data = [
            'parse_mode'               => 'markdown',
            'disable_web_page_preview' => true,
        ];

        if (!self::hasUserAgreedToRules($this->getMessage()->getFrom()->getId())) {
            $text                 .= PHP_EOL . 'You **must agree** to these rules to post in the support group. Simply click the button below.';
            $data['reply_markup'] = new InlineKeyboard([
                ['text' => LitEmoji::encodeUnicode(':+1: I Agree to the Rules'), 'callback_data' => 'command=rules&action=agree'],
            ]);
        }

        return $this->replyToChat(LitEmoji::encodeUnicode($text), $data);
    }

    /**
     * Check if the passed user has agreed to the rules.
     *
     * @param int $user_id
     *
     * @return bool
     */
    protected static function hasUserAgreedToRules(int $user_id): bool
    {
        $statement = DB::getPdo()->prepare('
            SELECT `activated_at`
            FROM `' . TB_USER . '`
            WHERE `id` = ?
              AND `activated_at` IS NOT NULL
        ');
        $statement->execute([$user_id]);
        $agreed = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

        return !empty($agreed);
    }
}
