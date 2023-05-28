<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use function DI\env;

return [
    'input.file.path' => $GLOBALS['argv'][1] ?? '',

    'base.currency' => env('BASE_CURRENCY'),

    'commissions.eu.raw' => env('EU_COMMISSION'),
    'commissions.eu' => function (ContainerInterface $container) {
        return (float)$container->get('commissions.eu.raw');
    },

    'commissions.non.eu.raw' => env('NON_EU_COMMISSION'),
    'commissions.non.eu' => function (ContainerInterface $container) {
        return (float)$container->get('commissions.non.eu.raw');
    },

    // Rate converter
    'rates.provider' => env('RATES_PROVIDER'),

    'exchangerates.url' => env('EXCHANGE_RATES_URL'),
    'exchangerates.token' => env('EXCHANGE_RATES_TOKEN'),

    // BIN data provider
    'bin.data.provider' => env('BIN_DATA_PROVIDER'),

    'binlist.url' => env('BINLIST_URL'),
];