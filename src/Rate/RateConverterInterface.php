<?php

declare(strict_types=1);

namespace App\Rate;

use App\Transaction\Model\Transaction;

interface RateConverterInterface
{
    public function getBaseValue(Transaction $transaction);
}