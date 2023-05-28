<?php

declare(strict_types=1);

namespace Unit\Rate\Provider;

use App\BIN\EUCountryCodesEnum;
use App\BIN\Provider\BinListProvider;
use App\Rate\Provider\ExchangeRatesAPIProvider;
use App\Transaction\Model\Transaction;
use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ExchangeRatesAPIProviderTest extends TestCase
{
    public static function transactionsDataProvider(): array
    {
        return [
            [
                'bin' => '5375',
                'amount' => 1000,
                'currency' => 'USD',
                'rate' => 1.07,
                'expectedValue' => 934.58
            ]
        ];
    }

    #[DataProvider('transactionsDataProvider')]
    public function testCalculatesBaseValueCorrectly(string $bin, float $amount, string $currency, float $rate, float $expectedValue): void
    {
        $transaction = new Transaction($bin, $amount, $currency);

        $responseStreamMock = $this->createConfiguredMock(
            StreamInterface::class,
            [
                '__toString' => sprintf('{"rates":{"%s":%s}}', $currency, $rate)
            ]
        );

        $responseMock = $this->createConfiguredMock(
            ResponseInterface::class,
            [
                'getBody' => $responseStreamMock
            ]
        );

        $httpClientMock = $this->createConfiguredMock(
            Client::class,
            ['request' => $responseMock]
        );

        $exchangeRatesAPIProvider = new ExchangeRatesAPIProvider('EUR', '', '', $httpClientMock);
        $this->assertEquals($expectedValue, $exchangeRatesAPIProvider->getBaseValue($transaction));
    }

    #[DataProvider('transactionsDataProvider')]
    public function testDoesntFetchRatesTwice(string $bin, float $amount, string $currency, float $rate): void
    {
        $transaction = new Transaction($bin, $amount, $currency);

        $responseStreamMock = $this->createConfiguredMock(
            StreamInterface::class,
            [
                '__toString' => sprintf('{"rates":{"%s":%s}}', $currency, $rate)
            ]
        );

        $responseMock = $this->createConfiguredMock(
            ResponseInterface::class,
            [
                'getBody' => $responseStreamMock
            ]
        );

        $httpClientMock = $this->createConfiguredMock(
            Client::class,
            ['request' => $responseMock]
        );

        $httpClientMock->expects($this->once())
            ->method('request');

        $exchangeRatesAPIProvider = new ExchangeRatesAPIProvider('EUR', '', '', $httpClientMock);

        $exchangeRatesAPIProvider->getBaseValue($transaction);
        $exchangeRatesAPIProvider->getBaseValue($transaction);
    }
}