# ADR 0012: Application Services for Logic Shared by Use Cases

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store ADR 0016

---

## Context

Several use cases sometimes need the same orchestration of ports (e.g. "store a file": hash, encrypt,
write to object storage, persist, compensate on failure). Copying it duplicates the delicate part;
calling a use case from another couples entry points; a Domain service is wrong when the sequence
holds no business rule but only orchestrates ports.

## Decision

1. Logic shared by **at least two** use cases lives in an **application service**:
   `src/Application/Service/<Feature>/<WhatItDoes>.php` — grouped by feature, named after what it does
   (`DocumentVault`, `InvoiceNumberer`), never `XxxService`, `XxxHelper` or `XxxManager`.
2. It is a `final readonly` class **without an interface** (single implementation, depends on Domain
   ports only; tests pass fakes of those ports).
3. The whole sequence is extracted, compensation included.
4. **Only use cases call application services.** Deptrac enforces it: the Application layer is split
   into `UseCase` and `ApplicationService`, and `Infrastructure` may depend on `UseCase` only.
5. Logic with a single known consumer stays in its use case.

## Consequences

### Positive

- Shared orchestration and its compensation are written and tested once.
- `src/Application` has an explicit, feature-grouped place for it, with a checked boundary.

### Negative

- One more indirection; the "second consumer" rule requires judgement.

## Changes from the original

- The Deptrac rule suggested by the original ADR is implemented in `deptrac.yaml`.
