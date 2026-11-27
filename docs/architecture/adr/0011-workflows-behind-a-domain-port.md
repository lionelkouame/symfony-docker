# ADR 0011: Workflows Declared in Configuration, Driven Through a Domain Port

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store ADR 0014

---

## Context

Lifecycles with a few states can be coded in the entity. Multi-step lifecycles need a declarative,
auditable state machine; `symfony/workflow` provides one, but neither the Domain nor the use cases may
depend on Symfony.

## Decision

1. **Workflows are declared in `config/packages/workflow.yaml`**, as `state_machine` unless several
   places must be active at once. The file documents the conventions and an example.
2. **The subject stays a plain Domain entity**: the `method` marking store reads and writes the place
   through `get<Property>()` / `set<Property>()` typed with a **backed enum** Value Object.
3. **Use cases depend on `Domain/Port/Workflow/WorkflowEngineInterface`** (`can()`, `apply()`,
   `enabledTransitions()`), implemented by `Infrastructure/Workflow/Symfony/SymfonyWorkflowEngine`,
   which resolves workflows by name through a service locator on the `workflow` tag (valid even when
   no workflow is declared).
4. **A refused transition is a business fact**: `NotEnabledTransitionException` becomes
   `TransitionNotAllowedException` (an `InvalidDataException`, 422). An unknown workflow is a
   programming error (`\LogicException`, 500).
5. **Side effects live in listeners** (`Infrastructure/Workflow/Symfony/Listener/<Feature>/`); the rules
   deciding whether a transition is allowed stay in the definition or in the Domain.
6. Workflow and transition names are exposed as constants next to the feature's use cases.

## Consequences

### Positive

- Lifecycles are declarative and can be dumped as diagrams (`bin/console workflow:dump`).
- The adapter is unit-tested with an in-memory state machine (`SymfonyWorkflowEngineTest`).

### Negative

- Workflow and transition names are strings shared between configuration and code.
- Simple lifecycles may stay hand-written in the entity; choose case by case.
