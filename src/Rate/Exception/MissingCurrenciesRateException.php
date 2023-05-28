<?php

declare(strict_types=1);

namespace App\Rate\Exception;

use Exception;
use Throwable;

class
MissingCurrenciesRateException extends Exception
{
    protected $message = 'Missing currencies rate for %s - %s';

    public function __construct(string $targetCurrency, string $baseCurrency, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(sprintf($this->message, $targetCurrency, $baseCurrency), $code, $previous);
    }
}