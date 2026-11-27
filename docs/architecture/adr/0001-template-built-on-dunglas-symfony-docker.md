# ADR 0001: The Template Is Built on dunglas/symfony-docker and Customised Additively

* **Status**: Accepted
* **Date**: 2026-11-27
* **Origin**: Clean Architecture template, distilled from the Compliance Store project

---

## Context

New projects start from this repository. It must provide, out of the box:

* the Docker runtime of [dunglas/symfony-docker](https://github.com/dunglas/symfony-docker)
  (FrankenPHP, Caddy, HTTPS, Mercure, dev container, production image);
* a Symfony application already structured with the Clean Architecture conventions decided
  in previous projects, with a working end-to-end example.

Both sources keep moving: Dunglas releases infrastructure fixes every few weeks (PHP, FrankenPHP,
Caddy, CI), and the conventions evolve with each project. A template that drifts away from
its upstream becomes impossible to update, and projects created from it freeze on the day
they were generated.

## Decision

### 1. Two layers, two owners

| Layer                  | Owner                  | Content                                                                 |
| ---------------------- | ---------------------- | ----------------------------------------------------------------------- |
| Docker runtime         | dunglas/symfony-docker | `Dockerfile`, `compose*.yaml`, `frankenphp/`, `.devcontainer/`, `docs/*.md` |
| Application & conventions | this template       | `src/`, `tests/`, `config/`, `docs/architecture/`, `docs/template/`, tooling |

### 2. Customise by adding, not by editing

* New concerns go into **new files** (`Makefile`, `deptrac.yaml`, `phpstan.neon`, `.githooks/`,
  `.github/ISSUE_TEMPLATE/`, `docs/architecture/`...): git never conflicts on a file the upstream
  does not have.
* Dunglas files are left **byte-identical** whenever possible. In particular, the self-removing
  install block of `frankenphp/docker-entrypoint.sh` and `compose.yaml` is kept: it is inert as
  soon as `composer.json` is not `{}`, and keeping it removes the most likely merge conflict.
* When an upstream file must change, the change stays inside the extension points the upstream
  designed for it:
  * Flex recipe blocks (`###> doctrine/doctrine-bundle ###` in `compose.yaml`, `Dockerfile`);
  * the steps the upstream CI ships commented out ("Uncomment if Doctrine ORM is installed");
  * a marked block (`<!-- ###> clean-architecture ### -->`) in `README.md`.
* The upstream files touched are listed in [docs/template/updating.md](../../template/updating.md);
  that list is updated in the same pull request as any new touch.

### 3. The application is committed, installed

Unlike the upstream (whose `composer.json` is `{}` and installs the skeleton on first start), the
template commits an installed Symfony application (`composer.json`, `composer.lock`,
`symfony.lock`, recipes' configuration). A project created from it starts with the conventions,
the quality gate and a green test suite, without a bootstrap script to maintain.

### 4. Two update flows

```text
 dunglas/symfony-docker ──(git merge upstream/main)──► this template ──(template-sync)──► project A
                                                                     └─(template-sync)──► project B
```

* **Upstream → template**: `make upstream-merge` (a real merge, so the history keeps every upstream
  commit and the next merge only replays the new ones).
* **Template → project**: `make template-sync`, which runs
  [coopTilleuls/template-sync](https://github.com/coopTilleuls/template-sync) against this
  repository — the tool Dunglas recommends for its own template.

### 5. A reference use case proves the whole chain

`GET /api/v1/health` (`CheckSystemStatus` use case) crosses every layer and is tested at every
level (unit, API, CI `curl`). After any update, `make test && make health` tells whether the
template still works.

## Consequences

### Positive

* Upstream updates merge cleanly in the common case; conflicts, when they happen, are confined to
  a short, documented list of files.
* A new project is productive immediately: structure, tooling, CI, docs and a green example.
* The template's own changes reach existing projects through `template-sync`.

### Negative

* Dependencies are pinned by `composer.lock`: the template must be updated (`composer update`)
  regularly, which Dependabot proposes weekly.
* Recipes that touch upstream files (`compose.yaml`, `Dockerfile`) can still conflict; the marked
  blocks make those conflicts easy to resolve but do not remove them.

## Alternatives Considered

| Option | Verdict |
| ------ | ------- |
| Keep `composer.json` at `{}` and apply the conventions with a bootstrap script after the skeleton install | Rejected: the script installs whatever versions are current, so the template can break without any commit; the conventions would live in a non-runnable overlay folder |
| Restructure the upstream files (move Dunglas docs to a sub-folder, rewrite the README) | Rejected: every upstream change to those files would become a conflict |
| Fork once and stop following the upstream | Rejected: Docker/FrankenPHP/PHP updates are the reason to use the upstream |
