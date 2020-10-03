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

namespace TelegramBot\SupportBot;

use Longman\TelegramBot\DB;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;

class Helpers
{
    /**
     * Get a simple option value.
     *
     * @todo: Move into core!
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function getSimpleOption($name, $default = false)
    {
        return json_decode(DB::getPdo()->query("
            SELECT `value`
            FROM `simple_options`
            WHERE `name` = '{$name}'
        ")->fetchColumn() ?: '', true) ?? $default;
    }

    /**
     * Set a simple option value.
     *
     * @todo: Move into core!
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return bool
     */
    public static function setSimpleOption($name, $value): bool
    {
        return DB::getPdo()->prepare("
            INSERT INTO `simple_options`
            (`name`, `value`) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE
            `name` = VALUES(`name`),
            `value` = VALUES(`value`)
        ")->execute([$name, json_encode($value)]);
    }

    /**
     * Delete any old welcome messages from the group.
     */
    public static function deleteOldWelcomeMessages(): void
    {
        $chat_id = getenv('TG_SUPPORT_GROUP_ID');

        $welcome_message_ids = self::getSimpleOption('welcome_message_ids', []);
        foreach ($welcome_message_ids as $key => $message_id) {
            // Be sure to keep the latest one.
            if ($key === 'latest') {
                continue;
            }

            $deletion = Request::deleteMessage(compact('chat_id', 'message_id'));
            if (!$deletion->isOk()) {
                // Let's just save the error for now if it fails, to see if we can fix this better.
                TelegramLog::error(sprintf(
                    'Chat ID: %s, Message ID: %s, Error Code: %s, Error Message: %s',
                    $chat_id,
                    $message_id,
                    $deletion->getErrorCode(),
                    $deletion->getDescription()
                ));
            }

            unset($welcome_message_ids[$key]);
        }

        self::setSimpleOption('welcome_message_ids', $welcome_message_ids);
    }

    /**
     * Save the latest welcome message to the option.
     *
     * @param int $welcome_message_id
     */
    public static function saveLatestWelcomeMessage($welcome_message_id): void
    {
        $welcome_message_ids     = self::getSimpleOption('welcome_message_ids', []);
        $new_welcome_message_ids = array_values($welcome_message_ids) + ['latest' => $welcome_message_id];
        self::setSimpleOption('welcome_message_ids', $new_welcome_message_ids);
    }

    /**
     * Handle expired activations and kick those users.
     */
    public static function handleExpiredActivations(): void
    {
        $expiry_time      = strtotime(getenv('TG_SUPPORT_GROUP_ACTIVATION_EXPIRE_TIME') ?: '15 min');
        $expiry_time_in_s = $expiry_time - time();

        // If the user is already activated, keep the initial activation date.
        $users_to_kick = DB::getPdo()->query("
            SELECT `id`
            FROM " . TB_USER . "
            WHERE `joined_at` < (NOW() - INTERVAL {$expiry_time_in_s} SECOND)
              AND `activated_at` IS NULL
        ");
        foreach ($users_to_kick as $user_to_kick) {
            self::kickUser((int) $user_to_kick['id']);
        }
    }

    /**
     * Kick the passed user.
     *
     * @param int $user_id
     *
     * @return bool
     */
    protected static function kickUser(int $user_id): bool
    {
        try {
            $ban_time  = strtotime(getenv('TG_SUPPORT_GROUP_BAN_TIME') ?: '1 day');
            $kick_user = Request::kickChatMember([
                'chat_id'    => getenv('TG_SUPPORT_GROUP_ID'),
                'user_id'    => $user_id,
                'until_date' => $ban_time,
            ]);
            if ($kick_user->isOk()) {
                return DB::getPdo()->prepare("
                    UPDATE " . TB_USER . "
                    SET `activated_at` = NULL,
                        `joined_at` = NULL,
                        `kicked_at` = NOW()
                    WHERE `id` = ?
                ")->execute([$user_id]);
            }
        } catch (\Throwable $e) {
            // Fail silently.
        }

        return false;
    }
}
