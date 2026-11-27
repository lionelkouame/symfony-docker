<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Domain\Port\Persistence\TransactionInterface;
use App\Infrastructure\Persistence\Doctrine\DoctrineTransaction;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The Doctrine adapter of the transaction port, against a real database (ADR 0014).
 */
final class DoctrineTransactionTest extends KernelTestCase
{
    private Connection $connection;
    private TransactionInterface $transaction;

    protected function setUp(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->connection = $entityManager->getConnection();
        $this->transaction = new DoctrineTransaction($entityManager);

        // A temporary table: it lives outside the tested transactions and disappears with the connection.
        $this->connection->executeStatement('CREATE TEMPORARY TABLE transaction_probe (label VARCHAR(20) NOT NULL)');
    }

    protected function tearDown(): void
    {
        $this->connection->executeStatement('DROP TABLE IF EXISTS transaction_probe');
        parent::tearDown();
    }

    public function testItCommitsEveryWriteAndReturnsTheOperationResult(): void
    {
        $countSeenInside = $this->transaction->run(function (): int {
            $this->connection->insert('transaction_probe', ['label' => 'first']);
            $this->connection->insert('transaction_probe', ['label' => 'second']);

            return $this->probeCount();
        });

        self::assertSame(2, $countSeenInside);
        self::assertSame(2, $this->probeCount());
    }

    public function testAFailureRollsBackTheWritesAndRethrows(): void
    {
        try {
            $this->transaction->run(function (): void {
                $this->connection->insert('transaction_probe', ['label' => 'first']);

                if (1 === $this->probeCount()) {
                    throw new \RuntimeException('Failure after the first write.');
                }
            });
            self::fail('The exception must be rethrown.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Failure after the first write.', $exception->getMessage());
        }

        self::assertSame(0, $this->probeCount());
    }

    private function probeCount(): int
    {
        $count = $this->connection->fetchOne('SELECT COUNT(*) FROM transaction_probe');
        \assert(is_numeric($count));

        return (int) $count;
    }
}
