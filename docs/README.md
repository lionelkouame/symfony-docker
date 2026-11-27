# Documentation

## 🧭 Template Clean Architecture

- [La démarche du template](template/README.md) — pourquoi et comment il étend dunglas/symfony-docker
- [Démarrer un nouveau projet](template/new-project.md)
- [Mettre à jour le template et les projets](template/updating.md)

## 🏛️ Architecture

- [Guide d'architecture](architecture/overview.md) — couches, cycle d'une requête, conventions,
  gestion des erreurs, ajout d'une fonctionnalité pas à pas
- [Architecture Decision Records](architecture/adr/README.md) — les 17 décisions et leur justification

## 🐳 Infrastructure Docker (documentation de dunglas/symfony-docker)

Ces pages viennent du template de Dunglas et sont laissées telles quelles pour rester synchronisées
avec l'upstream.

1. [Options available](options.md)
2. [Using Symfony Docker with an existing project](existing-project.md)
3. [Support for extra services](extra-services.md)
4. [Deploying in production](production.md)
5. [Debugging with Xdebug](xdebug.md)
6. [TLS Certificates](tls.md)
7. [Using MySQL instead of PostgreSQL](mysql.md)
8. [Using Alpine Linux instead of Debian](alpine.md)
9. [Using a Makefile](makefile.md) — le template fournit déjà un `Makefile`
10. [Updating the template](updating.md) — voir aussi [template/updating.md](template/updating.md)
11. [Using coding agents](agents.md)
12. [Troubleshooting](troubleshooting.md)

## 🧪 Tester l'API à la main

Les requêtes du client HTTP (JetBrains / Visual Studio Code REST Client) sont dans
[`tests/http/v1/`](../tests/http/v1/), regroupées par fonctionnalité ; l'hôte se règle par les
variables `@baseUrl` / `@apiUrl` en tête de chaque fichier.
