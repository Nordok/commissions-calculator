<?php

namespace Unit\Transaction\Service;

use App\Transaction\Exception\BadTransactionDataFormatException;
use App\Transaction\Model\Transaction;
use App\Transaction\Service\TransactionsReader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class TransactionsReaderTest extends TestCase
{
    private vfsStreamDirectory $directory;

    protected function setUp(): void
    {
        $this->directory = vfsStream::setup('temp');
    }

    public function testReadsTransactionsWithValidStructure(): void
    {
        vfsStream::newFile('valid_transactions.txt')
            ->withContent('{"bin":"45717360","amount":"100.00","currency":"EUR"}')
            ->at($this->directory);

        $transactionsReader = new TransactionsReader(vfsStream::url('temp/valid_transactions.txt'));

        $this->assertEquals(
            new Transaction('45717360', 100.00, 'EUR'),
            $transactionsReader->read()->current()
        );
    }

    public function testThrowExceptionWithInvalidTransactionStructure(): void
    {
        vfsStream::newFile('empty_transaction.txt')
            ->withContent('{}')
            ->at($this->directory);

        $this->expectException(BadTransactionDataFormatException::class);

        $transactionsReader = new TransactionsReader(vfsStream::url('temp/empty_transaction.txt'));
        $transactionsReader->read()->current();

        vfsStream::newFile('broken_file.txt')
            ->withContent('not_json_content')
            ->at($this->directory);

        $this->expectException(BadTransactionDataFormatException::class);

        $transactionsReader = new TransactionsReader(vfsStream::url('temp/broken_file.txt'));
        $transactionsReader->read()->current();
    }
}