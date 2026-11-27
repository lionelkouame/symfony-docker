# ADR 0004: API Platform Is Confined to the Presentation Layer

* **Status**: Accepted
* **Date**: 2026-11-27
* **Origin**: Compliance Store ADR 0003 (state processors/providers) and ADR 0008 (presentation layout)

---

## Context

API Platform encourages putting `#[ApiResource]` on Doctrine entities. That couples the Domain to
HTTP and persistence, lets API Platform write to the database without going through the use cases,
and freezes the API contract to the internal model.

## Decision

### 1. Layout, version first

```text
src/Infrastructure/Presentation/ApiPlatform/
└── V1/
    ├── Resource/<Feature>/
    │   ├── <Feature>Resource.php     # #[ApiResource]: declares operations only
    │   ├── <Action>Input.php         # write DTO, #[Assert\...] constraints (ADR 0007)
    │   └── <Action>Output.php        # read DTO, primitives only, fromReadModel()
    └── State/
        ├── Processor/<Feature>/<Action>Processor.php   # writes (POST, PUT, PATCH, DELETE)
        └── Provider/<Feature>/<Action>Provider.php     # reads (GET)
```

A `V2` is added next to `V1` without touching it; `config/packages/api_platform.yaml` lists the
resource directories.

### 2. Resources declare, adapters convert

* A **Resource** holds no data and no logic: `shortName`, `uriTemplate`, `input`, `output`,
  `provider` / `processor`, OpenAPI metadata.
* One **Input** per write operation. GET operations have no Input: identifiers come from
  `$uriVariables` (ADR 0008).
* One **Output** per operation, or a shared one when several operations expose exactly the same
  representation.
* **Providers and Processors are thin adapters**: Input → Request → use case → Response → Output.
  They take no business decision and contain no `try/catch` (ADR 0009).

```php
public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): OrderOutput
{
    Assert::isInstanceOf($data, PlaceOrderInput::class);

    $response = $this->useCase->execute(new PlaceOrderRequest(
        customerId: $data->customerId,
        lines: $data->lines,
    ));

    return OrderOutput::fromReadModel($response->readModel);
}
```

Reference implementation: `V1/Resource/System/HealthCheckResource.php` and
`V1/State/Provider/System/HealthCheckProvider.php`.

## Consequences

### Positive

* Nothing reaches the database without going through a use case.
* The HTTP contract evolves independently of the Domain (versioned DTOs).
* Domain entities stay free of API Platform and Doctrine.

### Negative

* No automatic CRUD: every operation needs DTOs and a provider/processor.

## Changes from the original

* Paths follow the final layout (`Infrastructure/Presentation/ApiPlatform/V1/...`); the original
  ADR still referenced `Infrastructure/ApiPlatform/Resource`.
* Validators moved out of the presentation folder (ADR 0007, ADR 0010).
