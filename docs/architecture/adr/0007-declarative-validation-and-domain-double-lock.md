# ADR 0007: Declarative Input Validation and the Domain Double Lock

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store ADR 0005

---

## Context

Validation can live on the HTTP DTO (Symfony Validator, native 422 with the offending property) or in
the Domain (business exceptions). Only the first gives good HTTP errors; only the second protects
non-HTTP channels (CLI, message handlers, tests).

## Decision

### 1. The Input DTO carries the whole declarative contract

Format (`#[Assert\NotBlank]`, `#[Assert\Email]`, `#[Assert\Length]`...) **and** business constraints
(`#[UniqueOrderReference]`) are declared on the Input DTO: the full contract is readable in one
place, and API Platform answers `422` with a `ConstraintViolationList`.

### 2. Business constraints delegate to Domain ports

A custom constraint lives in `Infrastructure/Validation/Symfony/` (not under `Presentation/`: it is
reusable by any channel, ADR 0010). Its validator depends only on a Domain port
(`OrderRepositoryInterface::existsByReference()`), so no Symfony Validator class enters the Domain.

```text
Infrastructure/Validation/Symfony/
├── Constraint/UniqueOrderReference.php
└── ConstraintValidator/UniqueOrderReferenceValidator.php   ──► Domain/Port/Repository/OrderRepositoryInterface
```

### 3. The Domain keeps its own lock

The use case **still checks** the rule (`if ($orders->existsByReference($ref)) throw
OrderReferenceAlreadyExistsException::...`) and the Value Objects still validate their invariants.
The DTO rejects bad requests early; the Domain guarantees integrity whatever the entry channel.
Uniqueness is guaranteed a third time by a database constraint (ADR 0015).

## Consequences

### Positive

- Precise 422 responses for HTTP clients, full protection for every other channel.
- The Domain stays framework-free.

### Negative

- A rule can be expressed twice (constraint + use case guard): both must be kept in sync, and both
  are tested.

## Changes from the original

- Custom constraints live in `Infrastructure/Validation/Symfony/` (concern, then technology).
