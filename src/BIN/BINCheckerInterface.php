<?php

declare(strict_types=1);

namespace App\BIN;

interface BINCheckerInterface
{
    public function isEuropean(string $bin): bool;
}