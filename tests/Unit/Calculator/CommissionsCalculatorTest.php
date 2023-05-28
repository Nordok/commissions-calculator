<?php

declare(strict_types=1);

namespace Unit\Calculator;

use App\BIN\BINCheckerInterface;
use App\CommissionCalculator\CommissionCalculator;
use App\Rate\RateConverterInterface;
use App\Transaction\Model\Transaction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CommissionsCalculatorTest extends TestCase
{
    public static function nonEuropeanCardTransactionProvider(): array
    {
        return [
            'Non European card' => [
                'bin' => '5375',
                'amount' => 1000,
                'currency' => 'USD',
                'isEuropeanCard' => false,
                'baseValue' => 934.58,
                'targetCommission' => 18.69
            ],
            'European card' => [
                'bin' => '5375',
                'amount' => 1000,
                'currency' => 'BGN',
                'isEuropeanCard' => true,
                'baseValue' => 511.40,
                'targetCommission' => 5.11
            ]
        ];
    }

    #[DataProvider('nonEuropeanCardTransactionProvider')]
    public function testCalculatesCommissionCorrectly(
        string $bin,
        float  $amount,
        string $currency,
        bool $isEuropeanCard,
        float  $baseValue,
        float  $targetCommission
    ): void {
        $transaction = new Transaction($bin, $amount, $currency);

        $binChecker = $this->createConfiguredMock(
            BINCheckerInterface::class,
            [
                'isEuropean' => $isEuropeanCard
            ]
        );

        $rateConverter = $this->createConfiguredMock(
            RateConverterInterface::class,
            [
                'getBaseValue' => $baseValue
            ]
        );

        $commissionCalculator = new CommissionCalculator($binChecker, $rateConverter, 0.01, 0.02);
        $this->assertEquals($targetCommission, $commissionCalculator->calculate($transaction));
    }
}