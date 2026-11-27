# Clean Architecture template — everyday commands. Run `make` to list them.
# Based on the Makefile proposed by dunglas/symfony-docker (docs/makefile.md).

# Load .env.local if it exists (local overrides, git-ignored — e.g. HTTP_PORT on a busy machine)
-include .env.local
export

# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console

# PostgreSQL (credentials read from the environment, defaults aligned with compose.yaml)
POSTGRES_USER ?= app
POSTGRES_DB   ?= app
DB_RUN  = $(DOCKER_COMP) exec -e PGPASSWORD=$${POSTGRES_PASSWORD:-!ChangeMe!}
PSQL    = $(DB_RUN) -T database psql -U $(POSTGRES_USER) -d $(POSTGRES_DB)
PSQL_IT = $(DB_RUN) database psql -U $(POSTGRES_USER) -d $(POSTGRES_DB)

# Upstream template (dunglas/symfony-docker) and this template, for updates (docs/template/updating.md)
UPSTREAM_URL ?= https://github.com/dunglas/symfony-docker.git
TEMPLATE_URL ?= https://github.com/lionelkouame/symfony-docker

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build refresh up start down logs sh bash health test test-db phpunit deptrac phpstan quality composer vendor sf cc migration-diff migrate db db-query setup-hooks upstream-merge template-sync

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images (uses the cache, daily use)
	@$(DOCKER_COMP) build

refresh: ## Rebuilds the Docker images from scratch, pulling the latest base images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs), waiting for healthy services
	@$(DOCKER_COMP) up --detach --wait

start: build up ## Build and start the containers

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the FrankenPHP container
	@$(PHP_CONT) sh

bash: ## Connect to the FrankenPHP container via bash so up and down arrows go to previous commands
	@$(PHP_CONT) bash

health: ## Call GET /api/v1/health: the reference use case crossing every layer
	@curl --silent --show-error --fail --insecure https://localhost:$${HTTPS_PORT:-443}/api/v1/health && echo

## —— Quality 🧪 ————————————————————————————————————————————————————————————————
test: ## Full gate: Deptrac + PHPStan + PHPUnit. Pass "c=" to add PHPUnit options, e.g. make test c="--filter HealthCheck"
test: deptrac phpstan test-db phpunit

phpunit: ## Run PHPUnit only, pass the parameter "c=" to add options
	@$(eval c ?=)
	@$(DOCKER_COMP) exec -e APP_ENV=test php bin/phpunit $(c)

# Rebuilds the test database from the migrations, like the CI does before PHPUnit:
# a schema left behind by another branch must not make unrelated tests fail, and a
# mapping change without its migration must fail here rather than in production.
test-db: ## Recreate the test database from the migrations and validate the mapping
	@$(SYMFONY) -e test doctrine:database:drop --force --if-exists --quiet
	@$(SYMFONY) -e test doctrine:database:create --quiet
	@$(SYMFONY) -e test doctrine:migrations:migrate --no-interaction --allow-no-migration --quiet
	@$(SYMFONY) -e test doctrine:schema:validate --quiet
	@$(SYMFONY) -e test cache:clear --quiet

deptrac: ## Check the Clean Architecture dependency rule (deptrac.yaml)
	@$(PHP_CONT) vendor/bin/deptrac analyse --config-file=deptrac.yaml --no-interaction

phpstan: ## Static analysis, level max, checked business exceptions (phpstan.neon)
	@$(PHP_CONT) vendor/bin/phpstan analyse --memory-limit=512M

quality: deptrac phpstan ## Static checks only (no database needed)

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

## —— Doctrine 🗄️ ——————————————————————————————————————————————————————————————
migration-diff: ## Generate a migration from the differences between the XML mapping and the database
	@$(SYMFONY) doctrine:migrations:diff

migrate: ## Apply the pending migrations
	@$(SYMFONY) doctrine:migrations:migrate --no-interaction

db: ## Open an interactive psql shell
	@$(PSQL_IT)

db-query: ## Run a SQL query, example: make db-query q="SELECT 1;"
	@test -n "$(q)" || (echo 'Usage: make db-query q="SELECT 1;"' && exit 1)
	@$(PSQL) -c "$(q)"

## —— Git & template 🔁 ————————————————————————————————————————————————————————
setup-hooks: ## Enable the shared git hooks (.githooks): make test before each commit
	@git config core.hooksPath .githooks && echo "✅ Git hooks enabled (.githooks)"

upstream-merge: ## [template repository] Merge the latest dunglas/symfony-docker into the current branch
	@git remote get-url upstream >/dev/null 2>&1 || git remote add upstream $(UPSTREAM_URL)
	@git fetch upstream
	@git merge --no-ff upstream/main

template-sync: ## [project created from the template] Import the latest version of this template
	@curl -sSL https://raw.githubusercontent.com/coopTilleuls/template-sync/main/template-sync.sh | sh -s -- $(TEMPLATE_URL)
