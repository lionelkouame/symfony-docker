<?php

declare(strict_types=1);

namespace App\Domain\Port\Persistence;

/**
 * Runs several database writes as one unit: all of them are committed, or none (ADR 0014).
 *
 * Only for use cases that write more than once: a single write is already
 * atomic. Never put a network call (object storage, external API) inside the
 * operation: the transaction would stay open during the call, and those
 * systems do not take part in it anyway.
 */
interface TransactionInterface
{
    /**
     * Runs the operation in a transaction, committed if it returns normally and
     * rolled back if it throws (the exception is rethrown as is).
     *
     * @template T
     *
     * @param callable(): T $operation
     *
     * @return T
     */
    public function run(callable $operation): mixed;
}
