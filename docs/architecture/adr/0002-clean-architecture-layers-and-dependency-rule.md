# ADR 0002: Clean Architecture Layers and the Dependency Rule, Enforced by Deptrac

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store (architecture overview, `deptrac.yaml`)

---

## Context

Business rules outlive frameworks. When entities carry ORM or API attributes and controllers hold
business decisions, every framework upgrade touches the business code, and the rules cannot be
tested without booting the framework.

## Decision

### 1. Three layers under `src/`

```text
src/
├── Domain/          # Business rules, pure PHP: entities, value objects, read models,
│                    # events, exceptions and ports (interfaces)
├── Application/     # Orchestration: use cases (entry points) and application services
└── Infrastructure/  # Technical implementations of the ports + presentation (HTTP, CLI...)
```

The code is organised **by layer first, then by feature** (`<Feature>` = a business capability,
e.g. `Order`, `Customer`).

### 2. Dependencies only point inwards

```text
 Infrastructure ──► Application ──► Domain
```

- `Domain` depends on **nothing** but PHP: no Symfony, Doctrine, API Platform, PSR or utility
  library.
- `Application` depends on `Domain` only. Inside it, `UseCase` may call `ApplicationService`, never
  the reverse (ADR 0012).
- `Infrastructure` depends on `Domain`, on the `UseCase` part of `Application` and on vendors.
- The Domain talks to the outside world through **ports** (`Domain/Port/<Concern>/*Interface`) that
  Infrastructure implements (**adapters**). Ports are bound to adapters in `config/services.yaml`,
  the single composition root.

### 3. The rule is checked, not trusted

`deptrac.yaml` declares the layers and a `Vendor` layer (Symfony, Doctrine, ApiPlatform, Psr,
Webmozart...). A vendor that is not declared as a layer is not checked, which is why vendors are
listed explicitly. `make deptrac` runs in `make test`, in the pre-commit hook and in the CI.

## Consequences

### Positive

- The Domain and the use cases are unit-tested without any framework (fakes of the ports).
- A framework or library upgrade is confined to `Infrastructure/`.
- A violation fails the build with the exact offending dependency.

### Negative

- More classes than a "Symfony standard" application (ports, adapters, DTOs, mappers).
- A new vendor namespace must be added to the `Vendor` layer, otherwise Deptrac reports it as
  uncovered.
