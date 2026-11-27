# La démarche du template

Ce dépôt est mon point de départ pour tout nouveau projet Symfony. Il combine deux choses :

1. **l'infrastructure Docker de [dunglas/symfony-docker](https://github.com/dunglas/symfony-docker)**
   (FrankenPHP, Caddy, HTTPS automatique, Mercure, Dev Container, image de production) ;
2. **mes conventions de Clean Architecture**, éprouvées sur le projet Compliance Store, sous forme de
   code fonctionnel, d'outillage qui les vérifie et d'[ADR](../architecture/adr/README.md).

---

## Pourquoi partir du template de Dunglas

Le template de Dunglas règle la partie « runtime » mieux que je ne le ferais seul : images optimisées,
mode worker de FrankenPHP, HTTPS en local, CI, Dependabot, mises à jour fréquentes de PHP et de
FrankenPHP. En revanche, il ne dit rien de l'organisation du code : `composer.json` y vaut `{}` et le
squelette Symfony est installé au premier démarrage.

Ce template ajoute la couche manquante : une application **déjà installée et déjà structurée**.

## Ce que le template ajoute

| Ajout | Où |
| ----- | -- |
| Application Symfony installée (API Platform, Doctrine ORM + PostgreSQL, Workflow, Uid, Validator) | `composer.json`, `composer.lock`, `config/` |
| Briques de base du Domain : catégories d'exceptions, identifiants, événements, ports | `src/Domain/` |
| Contrat des use cases + use case de référence `CheckSystemStatus` | `src/Application/UseCase/` |
| Adapters : événements Symfony, UUID v7, transaction Doctrine, workflow Symfony | `src/Infrastructure/` |
| Endpoint `GET /api/v1/health` qui traverse toutes les couches | `src/Infrastructure/Presentation/ApiPlatform/V1/` |
| Tests unitaires, d'architecture, d'intégration et API | `tests/` |
| Vérification de la règle de dépendance | `deptrac.yaml` |
| Analyse statique niveau max avec exceptions métier vérifiées | `phpstan.neon` |
| Commandes du quotidien et gate qualité | `Makefile`, `.githooks/pre-commit` |
| CI : health check, Deptrac, PHPStan, migrations, PHPUnit, validation du schéma | `.github/workflows/ci.yaml` |
| Workflow de livraison : templates d'issues et de PR, labels as code | `.github/` |
| Consignes pour les agents IA (Claude Code, OpenCode...) | `AGENTS.md`, `CLAUDE.md` |
| Documentation et ADR | `docs/architecture/`, `docs/template/` |

La documentation d'origine de Dunglas reste à sa place, dans `docs/*.md` (voir l'[index](../README.md)).

## Comment le template a été construit

1. Partir de `dunglas/symfony-docker` à jour (`main`).
2. `docker compose up --wait` : le point d'entrée de Dunglas installe le squelette Symfony.
3. `composer require` des paquets utilisés par les conventions ; les recipes Flex ajoutent PostgreSQL
   dans `compose.yaml` et `pdo_pgsql` dans le `Dockerfile`, **dans les blocs `###> ... ###` prévus
   pour ça**.
4. Restaurer le bloc d'installation auto-supprimé de `frankenphp/docker-entrypoint.sh` et de
   `compose.yaml` : il ne fait plus rien dès que `composer.json` n'est plus vide, et ces deux fichiers
   restent identiques à l'upstream.
5. Supprimer les dossiers par défaut (`src/Controller`, `src/Entity`, `src/Repository`,
   `src/ApiResource`, `src/DataFixtures`) au profit des trois couches.
6. Reprendre les conventions de Compliance Store, en version agnostique, et corriger au passage la
   documentation qui n'était plus alignée avec le code.

## Le principe qui garde les mises à jour possibles

> **Personnaliser par ajout, pas par modification.**

* Tout ce qui est propre au template vit dans des **fichiers que l'upstream n'a pas** : git ne peut
  pas y produire de conflit.
* Les fichiers de Dunglas restent intacts, sauf une courte liste de modifications confinées aux
  points d'extension prévus (blocs de recipes, étapes commentées de la CI, bloc balisé du README).
* Cette liste est tenue à jour dans [updating.md](updating.md), qui décrit aussi les deux flux de
  mise à jour : Dunglas → template, et template → projets.

La décision complète est dans l'[ADR 0001](../architecture/adr/0001-template-built-on-dunglas-symfony-docker.md).

## Aller plus loin

* [Démarrer un nouveau projet](new-project.md)
* [Mettre à jour le template et les projets](updating.md)
* [Guide d'architecture](../architecture/overview.md)
