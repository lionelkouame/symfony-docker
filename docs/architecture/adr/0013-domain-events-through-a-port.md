# ADR 0013: Domain Events Published Through a Domain Port

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store (`DomainEvent`, `DomainEventPublisher`, Symfony listeners); no ADR existed

---

## Context

Reactions to a business fact (send a notification, update a projection, start a process) should not
be hardcoded in the use case that produced the fact, and the use case must not depend on Symfony's
event dispatcher.

## Decision

1. A **domain event** implements `App\Domain\Event\DomainEvent` (`occurredAt(): \DateTimeImmutable`,
   UTC), is `final readonly`, named in the **past tense** (`OrderPlaced`), lives in
   `Domain/Event/<Feature>/` and carries Value Objects or primitives — never the aggregate.
2. Use cases publish through `Domain/Port/Event/DomainEventPublisherInterface::publish()`, **after**
   the state change is persisted.
3. `Infrastructure/Event/Symfony/SymfonyDomainEventPublisher` dispatches the event as is on Symfony's
   PSR-14 dispatcher; listeners live in `Infrastructure/Event/Symfony/Listener/<Feature>/On<Event>.php`
   and subscribe with `#[AsEventListener]`.
4. Listeners hold side effects only, no rule deciding the outcome of the use case. Dispatch is
   synchronous; moving a listener to Messenger is an Infrastructure change that leaves the use case
   untouched.

## Consequences

### Positive

- Producers and reactions are decoupled; reactions are added without touching the use case.
- Use cases are unit-tested with an in-memory publisher collecting the events.

### Negative

- Synchronous listeners run in the request: a slow or failing listener affects the caller until it
  is moved to an asynchronous transport.

## Changes from the original

- The port is named `DomainEventPublisherInterface` and its adapter `SymfonyDomainEventPublisher`
  (ADR 0010).
