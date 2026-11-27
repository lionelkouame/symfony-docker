# Changelog

All notable changes to this **template** are documented in this file, so that projects created from
it know what `make template-sync` imports. Changes merged from `dunglas/symfony-docker` are not
listed: see the upstream history.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added

- Installed Symfony 8.1 application: API Platform 5, Doctrine ORM 3 with PostgreSQL, Workflow, Uid,
  Validator; PHPUnit, PHPStan (level max), Deptrac, Doctrine fixtures.
- Clean Architecture layers (`src/Domain`, `src/Application`, `src/Infrastructure`) with the shared
  building blocks: business exception categories, identifier trait, domain events, ports for event
  publishing, identity, transactions and workflows, and their Symfony / Doctrine adapters.
- Reference use case `CheckSystemStatus`, exposed as `GET /api/v1/health`, tested at unit, API and CI
  level.
- Architecture tests: business exceptions are categorised, no generic SPL argument exception in the core.
- `Makefile`, shared pre-commit hook, CI steps (health check, Deptrac, PHPStan, migrations, PHPUnit,
  schema validation), issue and pull request templates, labels as code.
- Documentation: template approach, update guide, architecture guide and 17 agnostic ADRs distilled
  from the Compliance Store project, with the inconsistencies of the original documentation fixed.
