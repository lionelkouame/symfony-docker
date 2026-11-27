# ADR 0014: Transactions Through a Domain Port

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store (issue "transactions and concurrency", `TransactionInterface`); no ADR existed

---

## Context

A use case writing several aggregates must commit all of them or none. Repositories flush on their
own, and the use case cannot depend on Doctrine's `EntityManager`.

## Decision

1. `Domain/Port/Persistence/TransactionInterface::run(callable $operation): mixed` (generic `T`) runs
   the operation in a transaction: committed if it returns, rolled back if it throws (the exception is
   rethrown as is).
2. `Infrastructure/Persistence/Doctrine/DoctrineTransaction` implements it with
   `EntityManager::wrapInTransaction()`: the repositories' own `flush()` calls join the enclosing
   transaction.
3. Use it **only** when a use case writes more than once; a single write is already atomic.
4. **Never put a network call** (object storage, external API, email) inside the operation: it would
   keep the transaction open and does not take part in it. Use compensation (ADR 0012) or events
   (ADR 0013) instead.
5. Concurrent updates of the same aggregate are guarded by optimistic locking (a Doctrine `version`
   field), translated to 409 by API Platform's default `OptimisticLockException` mapping.

## Consequences

### Positive

- Atomicity is explicit in the use case and testable (`tests/Integration/Persistence/DoctrineTransactionTest`).

### Negative

- On failure Doctrine closes the `EntityManager`: harmless in an HTTP request, but long-running
  processes (workers, commands) must reset it.
