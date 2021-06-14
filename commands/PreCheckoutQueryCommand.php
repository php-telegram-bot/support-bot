<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;

/**
 * No pre-checkout checks required at this point, just approve.
 */
class PreCheckoutQueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'precheckoutquery';

    /**
     * @var string
     */
    protected $description = 'PCQ command';

    /**
     * @inheritdoc
     */
    public function execute(): ServerResponse
    {
        // Simply approve, no need for any checks at this point.
        return $this->getPreCheckoutQuery()->answer(true);
    }
}
