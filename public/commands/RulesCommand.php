<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

/**
 * New chat member command
 */
class RulesCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'Rules';

    /**
     * @var string
     */
    protected $description = 'The Rules';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    
    /**
     * @var string
     */
    protected $usage = '/rules';
    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
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
