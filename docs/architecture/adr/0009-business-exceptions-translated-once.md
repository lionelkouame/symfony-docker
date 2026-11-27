# ADR 0009: Business Exceptions Categorised in the Domain, Translated to HTTP Once

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store ADR 0013

---

## Context

When each processor catches the use case exceptions and rethrows an `HttpException`, the mapping is
duplicated everywhere, a forgotten `catch` becomes a 500, and adapters must know every exception of
every use case. When nobody translates, business errors surface as 500. A generic
`\InvalidArgumentException` cannot tell a client error from a bug.

## Decision

### 1. An exception states a business fact, never an HTTP status

No `HttpException` in `src/Domain` or `src/Application`.

### 2. Every business exception extends a category

```text
 \DomainException
   └── App\Domain\Exception\DomainException   (abstract, never thrown)
         ├── NotFoundException       (abstract)  → 404   the aggregate does not exist
         ├── AlreadyExistException   (abstract)  → 409   a uniqueness invariant would be violated
         └── InvalidDataException    (abstract)  → 422   invalid data or forbidden transition
               ├── InvalidValueException          ◄── thrown directly by Value Objects
               └── Workflow\TransitionNotAllowedException
```

Feature exceptions live in `Domain/Exception/<Feature>/`, use **named constructors** carrying the
context (`OrderNotFoundException::withId($id)`), and extend the right category: no HTTP change is
needed. A new category is added to `api_platform.yaml` **and** to `BusinessExceptionsAreCategorisedTest`
in the same change.

### 3. Translation happens once, by configuration

```yaml
# config/packages/api_platform.yaml
api_platform:
  exception_to_status:
    # API Platform defaults: setting the key replaces them, so they are restated.
    Symfony\Component\Serializer\Exception\ExceptionInterface: 400
    ApiPlatform\Metadata\Exception\InvalidArgumentException: 400
    Doctrine\ORM\OptimisticLockException: 409
    Doctrine\DBAL\Exception\UniqueConstraintViolationException: 422
    # Business categories.
    App\Domain\Exception\NotFoundException: 404
    App\Domain\Exception\AlreadyExistException: 409
    App\Domain\Exception\InvalidDataException: 422
```

API Platform matches with `is_a()`, so an abstract category covers all its subclasses, and answers an
RFC 7807 `application/problem+json` body.

### 4. Use cases throw, adapters never catch

- Processors and providers contain **no `try/catch`**.
- A use case declares its business exceptions with `@throws`; PHPStan enforces it
  (`checkedExceptionClasses: [App\Domain\Exception\DomainException]`). A Value Object built from a value
  that cannot be invalid (generated ID) is silenced inline with
  `@phpstan-ignore missingType.checkedException (reason)`.
- When a use case always needs the aggregate, the repository port carries the "not found"
  (`OrderRepositoryInterface::get(OrderId): Order` throws `OrderNotFoundException`).
- A malformed identifier is reported as **not found**, not as invalid data.

### 5. Messages never contain personal data

The message becomes the `detail` sent to the client and may be logged. Technical, non-personal
values (an ID, a code) are fine.

### Status code reference

| Situation                                    | Raised by                        | Status |
| -------------------------------------------- | -------------------------------- | ------ |
| Body not parseable                           | Serializer                       | 400    |
| Missing / blank / malformed field            | Input DTO constraints (ADR 0007) | 422    |
| Value Object invariant broken                | `InvalidValueException`          | 422    |
| Forbidden transition, business rule violated | `InvalidDataException` subclass  | 422    |
| Uniqueness violated                          | `AlreadyExistException` subclass | 409    |
| Aggregate not found / ID that cannot match   | `NotFoundException` subclass     | 404    |
| Anything else (bug, infrastructure failure)  | —                                | 500    |

### Guards

- `tests/Unit/Architecture/BusinessExceptionsAreCategorisedTest.php`: every concrete exception of
  `src/Domain/Exception` extends a mapped category.
- `tests/Unit/Architecture/NoInvalidArgumentExceptionInCoreTest.php`: the generic SPL exception is
  forbidden in `src/Domain` and `src/Application` (it stays legitimate in Infrastructure for technical
  failures, which must remain a 500).
- API tests assert the 404 / 409 / 422 of every feature.

## Consequences

### Positive

- Adding a business exception requires no HTTP change; the mapping lives in one file.
- Bugs stay visible: `\Throwable` and SPL exceptions are never mapped, so they remain logged 500s.

### Negative

- Error bodies carry the exception message as `detail` and a generic `title`: a client needing a
  stable machine-readable error code requires a dedicated `kernel.exception` listener (deferred).

## Alternatives Considered

| Option                                | Verdict                                                            |
| ------------------------------------- | ------------------------------------------------------------------ |
| Global `exception_to_status` (chosen) | Zero code, native `problem+json` and OpenAPI support               |
| Per-operation `exceptionToStatus`     | Allowed as a local override                                        |
| `kernel.exception` listener           | Deferred until a stable error code is needed                       |
| `Result` / `Either` return values     | Rejected: must be propagated by hand in every use case and adapter |
| `try/catch` in each adapter           | Rejected: duplicated, easy to forget                               |

## Changes from the original

- The `PolicyViolationException` category, reserved but never mapped (it would have surfaced as a
  500), is not part of the template: a new category is introduced with its mapping, when needed.
- The API Platform 5 default `UniqueConstraintViolationException: 422` is restated; repositories still
  translate unique violations into `AlreadyExistException` (409, ADR 0015).
