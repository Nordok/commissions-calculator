<?php

declare(strict_types=1);

namespace App;

use App\CommissionCalculator\CommissionCalculator;
use App\Transaction\Service\TransactionsReader;

readonly class App
{
    public function __construct(
        private TransactionsReader $transactionsReader,
        private CommissionCalculator $commissionsCalculator
    ) {
    }

    public function run(): void
    {
        foreach ($this->transactionsReader->read() as $transaction) {
            echo $this->commissionsCalculator->calculate($transaction) . "\n";
        }
    }
}