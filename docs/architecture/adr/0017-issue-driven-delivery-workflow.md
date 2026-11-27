# ADR 0017: Issue-Driven Delivery — Issues, Branches, Conventional Commits, Pull Requests

- **Status**: Accepted
- **Date**: 2026-11-27
- **Origin**: Compliance Store (issue templates, labels as code, branch and commit history)

---

## Context

A change without a written reason is hard to review and impossible to trace once the author has
forgotten it. The history of the original project shows a workflow that works: one issue, one branch,
one pull request.

## Decision

1. **Every change starts with an issue**, from the templates of `.github/ISSUE_TEMPLATE/`: Epic, User
   Story, Tech task, Bug. Labels are managed as code (`.github/labels.yml`, synced by a workflow).
2. **One branch per issue**: `feature/<issue>-<slug>` (also `fix/`, `docs/`, `chore/`), created from
   `main`.
3. **Conventional commits**: `feat:`, `fix:`, `refacto:`, `docs:`, `test:`, `chore:`, with the issue
   number, e.g. `feat: place an order (#42)`. One logical step per commit.
4. **A pull request per branch**, using `.github/PULL_REQUEST_TEMPLATE.md` (`Closes #<issue>`), merged
   once the CI is green. A change of convention comes with its ADR in the same pull request.
5. Notable changes of the template itself are recorded in `CHANGELOG.md` (Keep a Changelog), so that
   projects syncing the template know what they import.

## Consequences

### Positive

- Every line of code can be traced back to a reason.
- Projects created from the template inherit the same workflow.

### Negative

- A small overhead for trivial changes.
