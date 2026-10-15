<?php

namespace App\Finance;

/**
 * A refused finance action. $reason is a stable key (insufficient_funds,
 * account_missing, already_reversed, ...) shown to the admin through the
 * translation files; the message is the English fallback.
 */
class FinanceException extends \RuntimeException
{
    public $reason;
    public $context;

    public function __construct($reason, $message = '', array $context = [])
    {
        $this->reason = $reason;
        $this->context = $context;
        parent::__construct($message !== '' ? $message : $reason);
    }
}
