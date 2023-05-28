<?php

declare(strict_types=1);

namespace App\BIN\Provider;

use App\BIN\BINCheckerInterface;
use App\BIN\EUCountryCodesEnum;
use App\BIN\Exception\BINDataProviderResponseException;
use GuzzleHttp\Client;
use Psr\Http\Client\ClientExceptionInterface;

class BinListProvider implements BINCheckerInterface
{
    private array $bins = [];

    public function __construct(private readonly string $url, private readonly Client $httpClient)
    {
    }

    /**
     * @throws BINDataProviderResponseException
     */
    public function isEuropean(string $bin): bool
    {
        if (!isset($this->bins[$bin])) {
            $this->bins[$bin] = EUCountryCodesEnum::isEUCode($this->fetchBINCountry($bin));
        }

        return $this->bins[$bin];
    }

    /**
     * @throws BINDataProviderResponseException
     */
    private function fetchBINCountry(string $bin): string
    {
        try {
            $response = $this->httpClient->request('GET', sprintf('%s/%s', $this->url, $bin));
        } catch (ClientExceptionInterface $clientException) {
            throw new BINDataProviderResponseException($clientException->getMessage());
        }

        $responseBody = json_decode((string)$response->getBody());

        return $responseBody->country->alpha2;
    }
}