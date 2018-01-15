<?php
/**
 * To start playing, just save all incoming data to a log file.
 */

use NPM\ServiceWebhookHandler\Handlers\GitHubHandler;

// Composer autoloader.
require_once __DIR__ . '/../../vendor/autoload.php';
(new Dotenv\Dotenv(__DIR__ . '/../..'))->load();

$webhook = new GitHubHandler(getenv('SB_WEBHOOK_SECRET_GITHUB'));
if (!$webhook->validate()) {
    http_response_code(404);
    die;
}

$f = fopen(__DIR__ . '/../../logs/' . getenv('SB_BOT_USERNAME') . '_webhook_github.log', 'ab+');
fwrite($f, sprintf(
    "%s\ninput:  %s\nGET:    %s\nPOST:   %s\nSERVER: %s\n\n",
    date('Y-m-d H:i:s'),
    file_get_contents('php://input'),
    json_encode($_GET),
    json_encode($_POST),
    json_encode($_SERVER)
));
fclose($f);
