<?php

declare(strict_types=1);

namespace App\CommissionCalculator;

use App\BIN\BINCheckerInterface;
use App\Rate\RateConverterInterface;
use App\Transaction\Model\Transaction;

readonly class CommissionCalculator
{
    public function __construct(
        private BINCheckerInterface    $binChecker,
        private RateConverterInterface $rateConverter,
        private float                  $euCommission,
        private float                  $nonEuCommission
    ) {
    }

    public function calculate(Transaction $transaction): float
    {
        $baseValue = $this->rateConverter->getBaseValue($transaction);

        return round(
            $this->binChecker->isEuropean($transaction->getBin())
                ? $baseValue * $this->euCommission
                : $baseValue * $this->nonEuCommission,
            2
        );
    }
}