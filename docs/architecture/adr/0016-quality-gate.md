# ADR 0016: Quality Gate — PHPStan Max, Deptrac, PHPUnit and Architecture Tests

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store (`phpstan.neon`, `deptrac.yaml`, `Makefile`, `.githooks/pre-commit`, CI)

---

## Context

Conventions that are only written down erode. Each of the decisions above must be checked by a tool,
locally before the commit and in the CI before the merge.

## Decision

### 1. Tools

| Tool     | Configuration      | Checks                                                                      |
| -------- | ------------------ | --------------------------------------------------------------------------- |
| PHPStan  | `phpstan.neon`     | Level **max** on `src/` and `tests/`, generics, checked business exceptions |
| Deptrac  | `deptrac.yaml`     | Dependency rule between layers (ADR 0002, ADR 0012)                         |
| PHPUnit  | `phpunit.dist.xml` | Fails on deprecations, notices and warnings                                 |
| Doctrine | —                  | `doctrine:schema:validate` after the migrations (ADR 0015)                  |

### 2. Test pyramid, mirroring `src/`

```text
tests/
├── Unit/
│   ├── Architecture/    # conventions as tests (exception categories, no SPL exceptions in the core)
│   ├── Domain/          # entities and value objects, no framework
│   ├── Application/     # use cases with fakes of the ports
│   └── Infrastructure/  # adapters testable without I/O (workflow engine, id generator...)
├── Integration/         # adapters against real services (database, storage)
├── Api/                 # HTTP contract: status codes, payloads (ApiTestCase)
├── Fixtures/            # test-only classes
└── http/                # manual requests (JetBrains / VS Code REST client)
```

Every feature has unit tests for its rules and API tests for each status code it can return.

### 3. One command, three places

- `make test` = Deptrac + PHPStan + rebuild of the test database + PHPUnit.
- `.githooks/pre-commit` runs `make test` (enable once with `make setup-hooks`).
- The CI runs the same steps in the Docker image, plus `curl /api/v1/health` and the upstream lint job.

## Consequences

### Positive

- A convention broken anywhere fails fast with a precise message.
- The CI and the developer machine run the same checks in the same image.

### Negative

- The pre-commit hook takes a few seconds; `git commit --no-verify` exists for emergencies and the CI
  still catches what it skipped.
