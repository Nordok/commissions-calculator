<?php

declare(strict_types=1);

namespace App\Rate\Provider;

use App\Rate\Exception\MissingCurrenciesRateException;
use App\Rate\Exception\RatesProviderResponseException;
use App\Rate\RateConverterInterface;
use App\Transaction\Model\Transaction;
use GuzzleHttp\Client;
use Psr\Http\Client\ClientExceptionInterface;

class ExchangeRatesAPIProvider implements RateConverterInterface
{
    private array $rates;

    public function __construct(
        private readonly string $baseCurrency,
        private readonly string $url,
        private readonly string $token,
        private readonly Client $httpClient
    ) {
        $this->rates = [];
    }

    /**
     * @throws MissingCurrenciesRateException
     * @throws RatesProviderResponseException
     */
    public function getBaseValue(Transaction $transaction): float
    {
        $rate = $this->getRate($transaction->getCurrency());

        return round($transaction->getAmount() / $rate, 2);
    }

    /**
     * @throws MissingCurrenciesRateException
     * @throws RatesProviderResponseException
     */
    private function getRate(string $targetCurrency): float
    {
        if ($this->baseCurrency === $targetCurrency) {
            return 1;
        }

        if (!count($this->rates)) {
            $this->rates = $this->fetchRates();
        }

        return $this->getRateForCurrency($targetCurrency);
    }

    /**
     * @throws MissingCurrenciesRateException
     */
    private function getRateForCurrency(string $currency): float
    {
        if (!isset($this->rates[$currency])) {
            throw new MissingCurrenciesRateException($currency, $this->baseCurrency);
        }

        return $this->rates[$currency];
    }

    /**
     * @throws RatesProviderResponseException
     */
    private function fetchRates(): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->url, [
                'query' => [
                    'access_key' => $this->token,
                    'base' => $this->baseCurrency
                ]
            ]);
        } catch (ClientExceptionInterface $clientException) {
            throw new RatesProviderResponseException($clientException->getMessage());
        }

        $responseBody = json_decode((string)$response->getBody());

        return json_decode(json_encode($responseBody->rates), true);
    }
}