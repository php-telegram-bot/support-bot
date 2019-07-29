<?php declare(strict_types=1);

/**
 * This file is part of the PHP Telegram Support Bot.
 *
 * (c) PHP Telegram Bot Team (https://github.com/php-telegram-bot)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Github\Api\Issue;
use Github\Api\PullRequest;
use Github\Client;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use NPM\ServiceWebhookHandler\Handlers\GitHubHandler;
use TelegramBot\SupportBot\Webhooks\Utils;

// Composer autoloader.
require_once __DIR__ . '/../../vendor/autoload.php';
Dotenv\Dotenv::create(__DIR__ . '/../..')->load();

$webhook = new GitHubHandler(getenv('TG_WEBHOOK_SECRET_GITHUB'));
if (!$webhook->validate()) {
    http_response_code(404);
    die;
}

// Save all incoming data to a log file for future reference.
Utils::logWebhookData(getenv('TG_LOGS_DIR') . '/' . getenv('TG_BOT_USERNAME') . '_webhook_github.log');

// Limit repos and events to serve.
$allowed_repos_events = [
    'php-telegram-bot/core'                 => ['release'],
    'php-telegram-bot/support-bot'          => ['release'],
    'php-telegram-bot/telegram-bot-manager' => ['release'],
];

// Get the incoming webhook data.
$data = $webhook->getData();

// Only react to allowed repos and events.
$repo = $data['repository'];
if (!in_array($webhook->getEvent(), $allowed_repos_events[$repo['full_name']] ?? [], true)) {
    die;
}

// Handle event.
if ($webhook->getEvent() === 'release') {
    handleRelease($data);

    if ($repo === 'php-telegram-bot/support-bot' && getenv('TG_AUTOUPDATE') === '1') {
        pullLatestAndUpdate();
    }
}

/**
 * Handle the "release" event.
 *
 * @param array $data
 */
function handleRelease(array $data): void
{
    $repo    = $data['repository'];
    $release = $data['release'];
    $action  = $data['action'];

    if ($action === 'published' && !$release['draft'] && !$release['prerelease']) {
        $author     = $release['author']['login'];
        $author_url = $release['author']['html_url'];
        $tag        = $release['tag_name'];
        $url        = $release['html_url'];
        $body       = parseReleaseBody($release['body'], $repo['owner']['login'], $repo['name']);

        $message = LitEmoji\LitEmoji::encodeUnicode("
:star: *New Release!* :star:
(_version_ [{$tag}]({$url}) _of_ [{$repo['full_name']}]({$repo['html_url']}) _has just been released by_ [{$author}]({$author_url}))

{$body}
");

        // Post the release message!
        sendTelegramMessage(getenv('TG_SUPPORT_GROUP_ID'), $message);
    }
}

/**
 * Make the release message Telegram-friendly and resolve links to GitHub.
 *
 * @param string $body
 * @param string $user
 * @param string $repo
 *
 * @return string
 */
function parseReleaseBody($body, $user, $repo): string
{
    // Replace headers with bold text.
    $body = preg_replace_callback('~### (?<header>.*)~', static function ($matches) {
        return "*{$matches['header']}*";
    }, $body);

    $github_client = new Client();
    // Replace any ID links with the corresponding issue or pull request link.
    $body = preg_replace_callback('~(?:(?<user>[0-9a-z\-]*)/(?<repo>[0-9a-z\-]*))?#(?<id>\d*)~i', static function ($matches) use ($github_client, $user, $repo) {
        $text = $matches[0];
        $id   = $matches['id'];
        $user = $matches['user'] ?: $user;
        $repo = $matches['repo'] ?: $repo;

        // Check if this ID is an issue.
        try {
            /** @var Issue $issue */
            $issue = $github_client->api('issue')->show($user, $repo, $id);
            return "[{$text}]({$issue['html_url']})";
        } catch (Throwable $e) {
            // Silently ignore.
        }

        // Check if this ID is a pull request.
        try {
            /** @var PullRequest $pr */
            $pr = $github_client->api('pull_request')->show($user, $repo, $id);
            return "[{$text}]({$pr['html_url']})";
        } catch (Throwable $e) {
            // Silently ignore.
        }

        return $text;
    }, $body);

    return $body;
}

/**
 * Send a text to the passed chat.
 *
 * @param string $chat_id
 * @param string $text
 *
 * @return ServerResponse|null
 */
function sendTelegramMessage($chat_id, $text): ?ServerResponse
{
    try {
        new Telegram(getenv('TG_API_KEY'));

        TelegramLog::initialize(new Logger('telegram_bot_releases', [
            (new StreamHandler(getenv('TG_LOGS_DIR') . '/releases.debug.log', Logger::DEBUG))->setFormatter(new LineFormatter(null, null, true)),
            (new StreamHandler(getenv('TG_LOGS_DIR') . '/releases.error.log', Logger::ERROR))->setFormatter(new LineFormatter(null, null, true)),
        ]));

        $parse_mode = 'markdown';

        return Request::sendMessage(compact('chat_id', 'text', 'parse_mode'));
    } catch (TelegramException $e) {
        TelegramLog::error($e->getMessage());
    } catch (Throwable $e) {
        // Silently ignore.
    }

    return null;
}

/**
 * Pull the latest code from the repository and install with composer.
 */
function pullLatestAndUpdate(): void
{
    exec('sudo -u www-data /usr/bin/git stash');
    exec('sudo -u www-data /usr/bin/git fetch');
    exec('sudo -u www-data /usr/bin/git reset --hard');
    exec('sudo -u www-data /usr/bin/git rebase');
    exec('sudo -u www-data /usr/bin/git pull');
    exec('sudo -u www-data /usr/local/bin/composer install --no-dev');
}
