<?php

declare(strict_types=1);

namespace Unit\BIN\Provider;

use App\BIN\EUCountryCodesEnum;
use App\BIN\Provider\BinListProvider;
use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class BinListProviderTest extends TestCase
{
    public static function nonEuropeanCountryProvider(): array
    {
        return [
            'US' => [
                'US'
            ]
        ];
    }

    public static function europeanCountriesProvider(): array
    {
        $data = [];

        foreach (EUCountryCodesEnum::cases() as $code) {
            $data[$code->value] = [$code->value];
        }

        return $data;
    }

    #[DataProvider('europeanCountriesProvider')]
    public function testCanRecognizeCardAsEuropean(string $countryCode): void
    {
        $responseStream = $this->createConfiguredMock(
            StreamInterface::class,
            [
                '__toString' => sprintf('{"country":{"alpha2":"%s"}}', $countryCode)
            ]
        );

        $response = $this->createConfiguredMock(
            ResponseInterface::class,
            [
                'getBody' => $responseStream
            ]
        );

        $httpClient = $this->createConfiguredMock(
            Client::class,
            ['request' => $response]
        );

        $binListProvider = new BinListProvider('url', $httpClient);
        $this->assertTrue($binListProvider->isEuropean('european_bin'));
    }

    #[DataProvider('nonEuropeanCountryProvider')]
    public function testCanRecognizeCardAsNonEuropean(string $countryCode): void
    {
        $responseStream = $this->createConfiguredMock(
            StreamInterface::class,
            [
                '__toString' => sprintf('{"country":{"alpha2":"%s"}}', $countryCode)
            ]
        );

        $response = $this->createConfiguredMock(
            ResponseInterface::class,
            [
                'getBody' => $responseStream
            ]
        );

        $httpClient = $this->createConfiguredMock(
            Client::class,
            ['request' => $response]
        );

        $binListProvider = new BinListProvider('url', $httpClient);
        $this->assertFalse($binListProvider->isEuropean('non_european_bin'));
    }

    public function testDoesNotMakeHttpRequestTwiceForTheSameBin(): void
    {
        $responseStream = $this->createConfiguredMock(
            StreamInterface::class,
            [
                '__toString' => sprintf('{"country":{"alpha2":"%s"}}', 'UA')
            ]
        );

        $response = $this->createConfiguredMock(
            ResponseInterface::class,
            [
                'getBody' => $responseStream
            ]
        );

        $httpClient = $this->createConfiguredMock(
            Client::class,
            ['request' => $response]
        );

        $binListProvider = new BinListProvider('url', $httpClient);

        $httpClient->expects($this->once())
            ->method('request');

        $binListProvider->isEuropean('5375');
        $binListProvider->isEuropean('5375');
    }
}