<?php

declare(strict_types=1);

use App\App;
use App\BIN\BINCheckerInterface;
use App\BIN\Provider\BinListProvider;
use App\CommissionCalculator\CommissionCalculator;
use App\Rate\Provider\ExchangeRatesAPIProvider;
use App\Rate\RateConverterInterface;
use App\Transaction\Service\TransactionsReader;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    App::class => autowire(),

    TransactionsReader::class => create()->constructor(get('input.file.path')),

    CommissionCalculator::class => autowire()
        ->constructorParameter('euCommission', get('commissions.eu'))
        ->constructorParameter('nonEuCommission', get('commissions.non.eu')),

    RateConverterInterface::class => function (ContainerInterface $container) {
        return match ($container->get('rates.provider')) {
            'exchangerates' => $container->get(ExchangeRatesAPIProvider::class),
            default => throw new RuntimeException('Undefined rates provider')
        };
    },

    ExchangeRatesAPIProvider::class => autowire()
        ->constructorParameter('baseCurrency', get('base.currency'))
        ->constructorParameter('url', get('exchangerates.url'))
        ->constructorParameter('token', get('exchangerates.token')),

    BINCheckerInterface::class => function (ContainerInterface $container) {
        return match ($container->get('bin.data.provider')) {
            'binlist' => $container->get(BinListProvider::class),
            default => throw new RuntimeException('Undefined BIN data provider')
        };
    },

    BinListProvider::class => autowire()->constructorParameter('url', get('binlist.url'))
];
