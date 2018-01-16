<?php declare(strict_types=1);
/**
 * This file is part of the PHP Telegram Support Bot.
 *
 * (c) PHP Telegram Bot Team (https://github.com/php-telegram-bot)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;

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
     * @inheritdoc
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
		$user = $message->getFrom()->getUsername();
        
        
		$text = <<<EOT
    
    Rules:  `English only | No Spamming or Nonsense Posting | No Bots`

¬ **English only**
Please keep your conversations in english inside this chatroom, otherwise your message will be deleted

¬ **No Spamming or Nonsense Posting** 
Don't spam Stickers or send Messages with no useful Content. When repeated you may be kicked or banned

¬ **No Bots**
Please do not add a Bot inside this Chat without asking the Admins first. Feel free to mention the Bot in a Message

Also keep in mind that this PHP Telegram Bot Support Chat applies only for this PHP Bot Library
https://github.com/php-telegram-bot/core
    

    EOT;
    
    
        $data = [
            'chat_id'      => $chat_id,
            'parse_mode' => 'MARKDOWN',
            'text'         => $text,
          
            
        ];
       
        return Request::sendMessage($data);
    }
}
