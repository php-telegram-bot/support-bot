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

use JsonException;
use LitEmoji\LitEmoji;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Payments\LabeledPrice;
use Longman\TelegramBot\Entities\Payments\SuccessfulPayment;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

use function TelegramBot\SupportBot\cache;

/**
 * Donate using Telegram Payments.
 */
class DonateCommand extends UserCommand
{
    public const DEFAULT_CURRENCY = 'EUR';

    /**
     * @var string
     */
    protected $name = 'donate';

    /**
     * @var string
     */
    protected $description = 'Donate to the PHP Telegram Bot project';

    /**
     * @var string
     */
    protected $usage = '/donate <amount> <currency>';

    /**
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Handle the callback queries regarding the /donate command.
     *
     * @param CallbackQuery $callback_query
     * @param array         $callback_data
     *
     * @return ServerResponse
     */
    public static function handleCallbackQuery(CallbackQuery $callback_query, array $callback_data): ServerResponse
    {
        self::createPaymentInvoice(
            $callback_query->getFrom()->getId(),
            (int) $callback_data['amount'],
            $callback_data['currency']
        );

        return $callback_query->answer([
            'text' => 'Awesome, an invoice has been sent to you.',
        ]);
    }

    /**
     * @return ServerResponse
     * @throws TelegramException
     */
    public function preExecute(): ServerResponse
    {
        $this->isPrivateOnly() && $this->removeNonPrivateMessage();

        // Make sure we only reply to messages.
        if (!$this->getMessage()) {
            return Request::emptyResponse();
        }

        return $this->execute();
    }

    /**
     * Execute command
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $currencies = $this->validateCurrencyFetching();
        if ($currencies instanceof ServerResponse) {
            return $currencies;
        }

        $message = $this->getMessage();
        $user_id = $message->getFrom()->getId();

        $text = trim($message->getText(true));
        if ('' === $text) {
            return $this->sendBaseDonationMessage();
        }

        // Fetch currency and amount being donated.
        // Hack: https://stackoverflow.com/a/1807896
        [$amount, $currency_code] = preg_split('/\s+/', "$text ");

        $currency = $this->validateCurrency($currency_code);
        if ($currency instanceof ServerResponse) {
            return $currency;
        }

        $amount = $this->validateAmount($amount, $currency);
        if ($amount instanceof ServerResponse) {
            return $amount;
        }

        return self::createPaymentInvoice($user_id, $amount, $currency['code']);
    }

    /**
     * Fetch the list of official currencies supported by Telegram Payments.
     *
     * @return array
     */
    protected function fetchCurrenciesFromTelegram(): array
    {
        try {
            $currencies = cache()->get('telegram_bot_currencies.json');
            if (empty($currencies)) {
                $currencies = file_get_contents('https://core.telegram.org/bots/payments/currencies.json');
                cache()->set('telegram_bot_currencies.json', $currencies, 86400);
            }

            return json_decode($currencies, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return [];
        }
    }

    /**
     * Create an invoice for the passed parameters and return the response.
     *
     * @param int    $chat_id
     * @param int    $amount
     * @param string $currency_code
     *
     * @return ServerResponse
     */
    public static function createPaymentInvoice(int $chat_id, int $amount, string $currency_code = self::DEFAULT_CURRENCY): ServerResponse
    {
        $price = new LabeledPrice(['label' => 'Donation', 'amount' => $amount]);

        return Request::sendInvoice([
            'chat_id'         => $chat_id,
            'title'           => 'Donation to the PHP Telegram Bot library',
            'description'     => LitEmoji::encodeUnicode(
                ':rainbow: Support the well-being of this great project and help it progress.' . PHP_EOL .
                PHP_EOL .
                ':heart: With much appreciation, your donation will flow back into making the PHP Telegram Bot library even better!'
            ),
            'payload'         => "donation_{$amount}_{$currency_code}",
            'provider_token'  => getenv('TG_PAYMENT_PROVIDER_TOKEN'),
            'start_parameter' => 'donation',
            'currency'        => strtoupper($currency_code),
            'prices'          => [$price],
            'reply_markup'    => new InlineKeyboard([
                ['text' => LitEmoji::encodeUnicode(':money_with_wings: Donate Now'), 'pay' => true],
                ['text' => LitEmoji::encodeUnicode(':gem: Become a Patron'), 'url' => getenv('TG_URL_PATREON')],
            ]),
        ]);
    }

    /**
     * Make sure the currencies can be retrieved and cached correctly.
     *
     * @return array|ServerResponse
     * @throws TelegramException
     */
    protected function validateCurrencyFetching()
    {
        if ($currencies = $this->fetchCurrenciesFromTelegram()) {
            return $currencies;
        }

        return $this->replyToUser(
            LitEmoji::encodeUnicode(
                'Donations via the Support Bot are not available at this time :confused:' . PHP_EOL .
                PHP_EOL .
                'Try again later or see [other ways to donate](' . getenv('TG_URL_DONATE') . ')'
            ),
            ['parse_mode' => 'markdown']
        );
    }

    /**
     * Ensure the currency is valid and return the currency data array.
     *
     * @param string $currency_code
     *
     * @return array|ServerResponse
     * @throws TelegramException
     */
    protected function validateCurrency(string $currency_code)
    {
        $currencies = $this->fetchCurrenciesFromTelegram();

        '' !== $currency_code || $currency_code = self::DEFAULT_CURRENCY;
        $currency_code = strtoupper($currency_code);

        if ($currency = $currencies[$currency_code] ?? null) {
            return $currency;
        }

        return $this->replyToUser(
            "Currency *{$currency_code}* not supported." . PHP_EOL .
            PHP_EOL .
            '[Check supported currencies](https://core.telegram.org/bots/payments#supported-currencies)',
            ['parse_mode' => 'markdown', 'disable_web_page_preview' => true]
        );
    }

    /**
     * Ensure the amount is valid and return the clean integer to use for the invoice.
     *
     * @param string $amount
     * @param array  $currency
     *
     * @return int|ServerResponse
     * @throws TelegramException
     */
    protected function validateAmount(string $amount, array $currency)
    {
        $int_amount = (int) ceil((float) $amount);

        // Check that the donation amount is valid.
        $multiplier = 10 ** (int) $currency['exp'];

        // Let's ignore the fractions and round to the next whole.
        $min_amount = (int) ceil($currency['min_amount'] / $multiplier);
        $max_amount = (int) floor($currency['max_amount'] / $multiplier);

        if ($int_amount >= $min_amount && $int_amount <= $max_amount) {
            return $int_amount * $multiplier;
        }

        return $this->replyToUser(
            sprintf(
                'Donations in %1$s must be between %2$s and %3$s.' . PHP_EOL .
                PHP_EOL .
                '[Check currency limits](https://core.telegram.org/bots/payments#supported-currencies)',
                $currency['title'],
                $min_amount,
                $max_amount
            ),
            ['parse_mode' => 'markdown', 'disable_web_page_preview' => true]
        );
    }

    /**
     * Send a message with an inline keyboard listing predefined amounts.
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    protected function sendBaseDonationMessage(): ServerResponse
    {
        return $this->replyToUser(
            LitEmoji::encodeUnicode(
                ":smiley: So great that you're considering a donation to the PHP Telegram Bot project." . PHP_EOL .
                PHP_EOL .
                ':+1: Simply select one of the predefined amounts listed below.' . PHP_EOL .
                PHP_EOL .
                'Alternatively, you can also define a custom amount using:' . PHP_EOL .
                '`' . $this->usage . '`' . PHP_EOL
            ) . PHP_EOL .
            '[Check supported currencies](https://core.telegram.org/bots/payments#supported-currencies) (Default is: *' . self::DEFAULT_CURRENCY . '*)',
            [
                'parse_mode'               => 'markdown',
                'disable_web_page_preview' => true,
                'reply_markup'             => new InlineKeyboard([
                    ['text' => '5€', 'callback_data' => 'command=donate&amount=500&currency=EUR'],
                    ['text' => '10€', 'callback_data' => 'command=donate&amount=1000&currency=EUR'],
                    ['text' => '20€', 'callback_data' => 'command=donate&amount=2000&currency=EUR'],
                    ['text' => '50€', 'callback_data' => 'command=donate&amount=5000&currency=EUR'],
                ], [
                    ['text' => '$5', 'callback_data' => 'command=donate&amount=500&currency=USD'],
                    ['text' => '$10', 'callback_data' => 'command=donate&amount=1000&currency=USD'],
                    ['text' => '$20', 'callback_data' => 'command=donate&amount=2000&currency=USD'],
                    ['text' => '$50', 'callback_data' => 'command=donate&amount=5000&currency=USD'],
                ], [
                    ['text' => LitEmoji::encodeUnicode(':gem: Patreon'), 'url' => getenv('TG_URL_PATREON')],
                    ['text' => LitEmoji::encodeUnicode(':cyclone: Tidelift'), 'url' => getenv('TG_URL_TIDELIFT')],
                    ['text' => 'More options...', 'url' => getenv('TG_URL_DONATE')],
                ]),
            ]
        );
    }

    /**
     * Send "Thank you" message to user who donated.
     *
     * @param SuccessfulPayment $payment
     * @param int               $user_id
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public static function handleSuccessfulPayment(SuccessfulPayment $payment, int $user_id): ServerResponse
    {
        return Request::sendMessage([
            'chat_id' => $user_id,
            'text'    => LitEmoji::encodeUnicode(
                ':pray: Thank you for joining our growing list of donors.' . PHP_EOL .
                ':star: Your support helps a lot to keep this project alive!'
            ),
        ]);
    }
}
