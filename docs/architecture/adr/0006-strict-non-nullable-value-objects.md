# ADR 0006: Strict Non-Nullable Value Objects, Optionality on the Entity

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store ADR 0006

---

## Context

Some attributes are optional (a customer's phone number, a delivery note). Where is "absent"
represented: inside the Value Object (`?string $value`, `Phone::fromNullable()`) or around it?

## Decision

1. **A Value Object is never null and never "empty"**: its constructor does not accept `null` and its
   properties are not nullable. If an instance exists, it is valid.
2. **Optionality belongs to the entity**: `private ?PhoneNumber $phone`, with the meaning of `null`
   documented on the property when it is not obvious.
3. **The use case instantiates conditionally**:

```php
$customer = Customer::register(
    id: CustomerId::make($this->ids->generate()),
    name: CustomerName::make($request->name),
    phone: null !== $request->phone ? PhoneNumber::make($request->phone) : null,
);
```

## Consequences

### Positive

- Code receiving a Value Object never null-checks its content.
- The same Value Object is reused where it is optional and where it is mandatory.
- Optionality is visible in the entity's signature.

### Negative

- Explicit ternaries in use cases for every optional attribute.
