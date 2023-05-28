<?php

declare(strict_types=1);

namespace App\Transaction\Service;

use App\Transaction\Exception\BadTransactionDataFormatException;
use App\Transaction\Exception\FileNotFoundException;
use App\Transaction\Model\Transaction;
use Generator;

class TransactionsReader
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @throws FileNotFoundException
     */
    public function read(): Generator
    {
        $file = @fopen($this->filePath, 'r');

        if (!$file) {
            throw new FileNotFoundException($this->filePath);
        }

        while ($transactionData = fgets($file)) {
            yield $this->buildTransaction($transactionData);
        }

        fclose($file);
    }

    private function buildTransaction(string $transactionData): Transaction
    {
        $decodedData = json_decode($transactionData);

        $allFieldsArePresent = isset($decodedData->bin) && isset($decodedData->amount) && isset($decodedData->currency);
        if (!$decodedData || !$allFieldsArePresent) {
            throw new BadTransactionDataFormatException($transactionData);
        }

        return new Transaction(
            $decodedData->bin,
            (float)$decodedData->amount,
            $decodedData->currency,
        );
    }
}