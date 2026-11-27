# ADR 0005: Value Objects Instead of Primitives and Native Arrays in the Domain

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store ADR 0004

---

## Context

Entity properties typed `string` or `array` let invalid values flow through the Domain (an empty
code, a malformed email), force validation to be repeated at every entry point, and document
neither the format nor the business meaning of the value (_primitive obsession_).

## Decision

1. **Every scalar business property of an entity is a Value Object**: `final readonly`, validated in
   its constructor (or a named constructor `make()` / `from...()`), throwing
   `InvalidValueException` when the invariant is broken (ADR 0009).
2. **Identifiers** implement `IdentifierInterface` with `IdentifierTrait`
   (`OrderId::make($value, $validator)`), the validator being the `IdValidatorInterface` port.
3. **Lists are collection Value Objects** implementing `IteratorAggregate` and `Countable`, consumed
   with `foreach`, `count()`, `contains()` — never an `array` in the Domain's public surface, never a
   `toArray()` used by the Domain.
4. **Conversion to raw types is an Infrastructure concern**: custom Doctrine types (VO ↔ column,
   ADR 0015) and Output DTOs (VO ↔ JSON).
5. **Boundary objects stay primitive**: use case Requests, Input and Output DTOs. The use case builds
   the Value Objects before calling the Domain.
6. Types that are already precise and immutable (`bool`, `int` counters, `\DateTimeImmutable`,
   backed enums) do not need a wrapper unless they carry an invariant.

```text
PlaceOrderRequest (primitives) ──► PlaceOrderUseCase builds OrderId, Quantity, OrderLines
                                          │
                                          ▼
                                   Order (exposes Value Objects only)
                                          │
              ┌───────────────────────────┴──────────────────────────┐
              ▼                                                      ▼
 Persistence/Doctrine/Type/*Type (VO ↔ SQL)          Presentation/.../OrderOutput (VO ↔ JSON)
```

## Consequences

### Positive

- Each rule is validated once, in one constructor; an invalid state is unrepresentable.
- Signatures document the business (`Quantity $quantity` says more than `int $quantity`).

### Negative

- More classes; one Doctrine type per Value Object stored in a column.

## Changes from the original

- Use case inputs are called **Requests** (the original still said `Command`), and the examples no
  longer reference a feature removed from the original project.
