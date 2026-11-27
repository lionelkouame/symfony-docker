<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Port\Persistence\TransactionInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Adapter of the {@see TransactionInterface} port onto Doctrine ORM.
 *
 * Repositories flush on their own: inside wrapInTransaction(), those flushes
 * join the enclosing transaction, so nothing is committed before the end of
 * the operation.
 *
 * On failure Doctrine also closes the EntityManager. Harmless in an HTTP
 * request, which ends there, but a long-running process (worker, command)
 * must take it into account.
 */
final readonly class DoctrineTransaction implements TransactionInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function run(callable $operation): mixed
    {
        return $this->entityManager->wrapInTransaction(static fn (): mixed => $operation());
    }
}
