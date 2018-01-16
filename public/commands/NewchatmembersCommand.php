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
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;

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
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
		$groupname = $message->getChat()->getTitle();
        $members = $message->getNewChatMembers();

        $text = 'Hi there!';

        if (!$message->botAddedInChat()) {
            $member_names = [];
            foreach ($members as $member) {
                $member_names[] = $member->tryMention();
            }
            //$text = 'Hi ' . implode(', ', $member_names) . '!';
			$text = 'Hello ' . implode(', ', $member_names) . ' in the ' . $groupname . ' Group' . PHP_EOL;
      $text .= 'Please Read the /Rules of this Chat.':
			
			

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        return Request::sendMessage($data);
		
    }
}
