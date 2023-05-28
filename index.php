<?php

declare(strict_types=1);

use DI\Container;
use App\App;

/** @var Container $container */
$container = require __DIR__ . '/bootstrap/container.php';

/** @var App $app */
$app = $container->get(App::class);
$app->run();