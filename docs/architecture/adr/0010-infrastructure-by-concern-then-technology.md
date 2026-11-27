# ADR 0010: Ports and Adapters Organised by Concern, Then by Technology

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store ADR 0008 and ADR 0015

---

## Context

Infrastructure folders named after a pattern (`Gateway/`, `Adapter/`) or a catch-all (`Service/`,
`Utils/`) mix unrelated adapters and hide which port each one implements. A technology-first layout
(`Infrastructure/Symfony/`) becomes a catch-all as well.

## Decision

### 1. One rule for every adapter

```text
Infrastructure/<Concern>/<Technology>/
```

- **Concern first**, matching a Domain port: `Event`, `Identity`, `Persistence`, `Storage`,
  `Encryption`, `Notification`, `Workflow`, `Validation`, and `Presentation` for entry points.
- **Technology second**: `Event/Symfony`, `Identity/SymfonyUid`, `Persistence/Doctrine`,
  `Presentation/ApiPlatform`, `Storage/Flysystem`... A replacement is a sibling folder
  (`Storage/Local`), the existing one is untouched.
- Types tied to one technology (a Flysystem object key, a Doctrine type) live next to their adapter,
  never in the Domain.
- No pattern-named (`Gateway`, `Adapter`) or catch-all (`Service`, `Utils`, `Helper`, `Manager`) folder.

### 2. Ports follow the same concerns

```text
Domain/Port/
├── Event/DomainEventPublisherInterface.php
├── Identity/IdGeneratorInterface.php, IdValidatorInterface.php
├── Persistence/TransactionInterface.php
├── Repository/<Aggregate>RepositoryInterface.php
└── Workflow/WorkflowEngineInterface.php
```

Every port name ends with `Interface`; every adapter is named `<Technology><Port>`
(`SymfonyDomainEventPublisher`, `DoctrineTransaction`, `SymfonyUuidGenerator`).

### 3. One composition root

Ports are bound to adapters in `config/services.yaml`, in one commented section. Swapping a
technology is a new sibling folder plus one line there.

### Resulting layout

```text
Infrastructure/
├── Event/Symfony/               # SymfonyDomainEventPublisher + Listener/<Feature>/
├── Identity/SymfonyUid/         # UUID v7 generator and validator
├── Persistence/Doctrine/        # DoctrineTransaction, Repository/, Mapping/, Type/, Fixtures/
├── Presentation/ApiPlatform/V1/ # Resource/, State/ (ADR 0004)
├── Validation/Symfony/          # custom constraints (ADR 0007), when needed
└── Workflow/Symfony/            # SymfonyWorkflowEngine + Listener/<Feature>/
```

## Consequences

### Positive

- The tree reads as the list of ports and their implementations.
- Swapping a technology is local to one folder and one line of configuration.

### Negative

- One more folder level for concerns with a single implementation.

## Changes from the original

- `Domain/Port/Service/` (a catch-all, the very thing the original ADR forbade in Infrastructure) is
  replaced by `Domain/Port/Identity/`; `GeneratorInterface` becomes `IdGeneratorInterface`.
- The event port is suffixed `Interface` like the others (`DomainEventPublisherInterface`) and its
  adapter is named after it (`SymfonyDomainEventPublisher`).
- Listeners live under `<Concern>/<Technology>/Listener/<Feature>/` for both events and workflows (the
  original documents disagreed on the workflow listener path).
