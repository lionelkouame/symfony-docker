# ADR 0003: Use Case Contract — Request, Use Case, Response Wrapping a Read Model

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store (architecture overview, "Use case conventions"; ADR 0013 on `null`)

---

## Context

Use cases are the entry points of the application, called by HTTP adapters, CLI commands, message
handlers and tests. Without a shared shape, each one invents its signature (scalars, arrays,
entities returned to the controller, `null` for "not found"), and adapters end up depending on
Domain entities.

## Decision

### 1. One folder per action, three classes

```text
src/Application/UseCase/<Feature>/<Action>/
├── <Action>Request.php    # readonly, primitives only (string, int, bool, ?string, list<string>...)
├── <Action>Response.php   # readonly, wraps a Domain read model
└── <Action>UseCase.php    # final readonly, the orchestration
```

### 2. Generic contract checked by PHPStan

```php
/**
 * @implements UseCaseInterface<PlaceOrderRequest, PlaceOrderResponse>
 */
final readonly class PlaceOrderUseCase implements UseCaseInterface
{
    /**
     * @throws CustomerNotFoundException
     * @throws InvalidValueException
     */
    public function execute(UseCaseRequestInterface $request): PlaceOrderResponse
    {
        // Request (primitives) → Value Objects → Entity → Ports
        return new PlaceOrderResponse(new PlacedOrder($order->id()->value()));
    }
}
```

`UseCaseInterface<TRequest, TResponse>`, `UseCaseRequestInterface` and `UseCaseResponseInterface`
live in `Application/UseCase/`. PHPStan (level max) checks the generics.

### 3. Rules

- The use case builds the Value Objects from the Request's primitives (ADR 0005).
- The Response carries a **read model** (`Domain/ReadModel/<Feature>/`), never an entity: the entity
  does not leave the Application layer, so adapters cannot call its behaviour or depend on its shape.
- A use case **never returns `null`**: a missing result is a `NotFoundException` (ADR 0009).
- A use case without parameters still receives an empty Request (`CheckSystemStatusRequest`), so
  criteria can be added without changing its signature.
- Each `execute()` declares its business exceptions with `@throws`: this is its error contract.
- A use case never calls another use case; shared logic goes into an application service (ADR 0012).

Reference implementation: `src/Application/UseCase/System/CheckSystemStatus/`.

## Consequences

### Positive

- Every entry point looks the same: easy to read, review and generate.
- Adapters depend on primitives and read models only.
- The error contract of each use case is explicit and checked.

### Negative

- Three classes plus a read model for each action, even trivial ones.

## Changes from the original

- The parameter of `UseCaseInterface::execute()` is named `$request` (it was named after one specific
  use case).
