<?php

declare(strict_types=1);

namespace App\Transaction\Exception;

use Exception;
use Throwable;

class FileNotFoundException extends Exception
{
    protected $message = 'Input file doesn\'t exists at path: ';

    public function __construct(string $filePath, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('%s%s', $this->message, $filePath), $code, $previous);
    }
}