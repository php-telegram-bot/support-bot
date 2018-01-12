<?php
/** 
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;

/**
 * New chat member command
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
    protected $version = '1.2.0';

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

function glclear($glist) {
    
    $glist = str_replace('<header class="entry-header">',"",$glist);
    $glist = str_replace('<h1 class="entry-title">',"",$glist);
    $glist = str_replace('</h1>',"",$glist);
    $glist = str_replace('</header><!-- .entry-header -->',"",$glist);
    $glist = str_replace('<div class="entry-content">',"",$glist);
    $glist = str_replace('<p>',"",$glist);
    $glist = str_replace('<strong>',"",$glist);
    $glist = str_replace('<br />',"",$glist);
    $glist = str_replace('</strong>',"",$glist);
    $glist = str_replace('</p>',"",$glist);
    $glist = str_replace('&nbsp;',"",$glist);
    return $glist;
}


function umlaute($text) {
    $text = str_replace('&Ouml;',"Ö",$text);
    $text = str_replace('&ouml;',"ö",$text);
    $text = str_replace('&Auml;',"Ä",$text);
    $text = str_replace('&auml;',"ä",$text);
    $text = str_replace('&Uuml;',"Ü",$text);
    $text = str_replace('&uuml;',"ü",$text);
	$text = str_replace('&gt;',">",$text);
	$text = str_replace('&lt;',"<",$text);
	$text = str_replace('&szlig;',"ß",$text);
    return $text;
}
