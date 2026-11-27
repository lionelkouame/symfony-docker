# ADR 0008: URI Variables and Context Contract of Providers Declared with PHPDoc Shapes

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store ADR 0007

---

## Context

`ProviderInterface::provide()` receives `array $uriVariables` and `array $context`, typed
`array<string, mixed>`. Passing `$uriVariables['id']` to a Request typed `string` fails PHPStan, and
defensive `is_string()` checks hide the route contract.

## Decision

1. **Every provider documents the shape it consumes**, with optional keys to stay compatible with the
   `= []` default:

```php
/**
 * @param array{id?: string} $uriVariables
 */
public function provide(Operation $operation, array $uriVariables = [], array $context = []): OrderOutput
{
    $response = $this->useCase->execute(new GetOrderRequest(id: $uriVariables['id'] ?? ''));

    return OrderOutput::fromReadModel($response->readModel);
}
```

2. The same applies to `$context` (`@param array{request?: Request} $context`) for collection
   providers reading query parameters; the mapping helper is named `buildRequestFromQuery()` to avoid
   confusing Symfony's `Request` with the use case Request.
3. **The provider does not decide "not found"**: an empty or malformed identifier is passed to the use
   case, which throws a `NotFoundException` (404) — the provider never returns `null` itself
   (ADR 0003, ADR 0009).

## Consequences

### Positive

- The route contract is visible on the method; PHPStan level max passes without casts.

### Negative

- The shape must be maintained when a route variable is renamed.

## Changes from the original

- The original example returned `null` from the use case and mapped it in the provider; this
  contradicted the "a use case never returns null" rule and is replaced by the exception flow.
- The query mapping helper is renamed `buildRequestFromQuery()` (it was `buildQueryFromRequest()`):
  use case inputs are Requests, no longer CQRS Queries.
