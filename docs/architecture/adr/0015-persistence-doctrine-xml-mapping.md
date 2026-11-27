# ADR 0015: Persistence — Doctrine XML Mapping, Custom Types, Uniqueness Guaranteed by the Database

* **Status**: Accepted
* **Date**: 2026-11-27
* **Origin**: Compliance Store ADR 0004 (custom types), ADR 0013 §4bis (unique constraints), issue on fixtures

---

## Context

Doctrine attributes on entities would couple the Domain to the ORM. Value Objects need to be stored as
columns. A uniqueness check in a use case does not resist two concurrent requests.

## Decision

1. **Entities are mapped in XML**, from Infrastructure:
   `src/Infrastructure/Persistence/Doctrine/Mapping/<Entity>.orm.xml`, mapping prefix
   `App\Domain\Entity` (`config/packages/doctrine.yaml`).
2. **Each Value Object stored in a column has a custom DBAL type** in
   `Persistence/Doctrine/Type/<ValueObject>Type.php`, declared under `doctrine.dbal.types`. A type
   rebuilding a VO from an invalid row is data corruption (500): PHPStan's checked-exception rule is
   relaxed for this folder only.
3. **Repositories** implement the Domain ports in `Persistence/Doctrine/Repository/`; `get()` throws
   the feature's `NotFoundException`, `find...()` may return `null` for the use case to decide.
4. **Every uniqueness invariant is also a unique constraint** in the database, and the repository
   translates `UniqueConstraintViolationException` into the matching `AlreadyExistException`: the loser
   of a race gets the same 409 as a sequential duplicate.

```php
public function add(Order $order): void
{
    $this->entityManager->persist($order);

    try {
        $this->entityManager->flush();
    } catch (UniqueConstraintViolationException) {
        throw OrderReferenceAlreadyExistsException::withReference($order->reference());
    }
}
```

5. **Schema changes go through migrations** (`make migration-diff`, then review). The test database is
   rebuilt from the migrations and `doctrine:schema:validate` runs in `make test` and in the CI, so a
   mapping change without its migration fails the build.
6. **Fixtures** live in `Persistence/Doctrine/Fixtures/` (they depend on Doctrine) and build entities
   through their named constructors.

## Consequences

### Positive

* Entities stay pure PHP; the database protects the invariants the application checks.

### Negative

* XML mapping is more verbose than attributes and is not refactored automatically by the IDE.
* One custom type per stored Value Object.
