# Démarrer un nouveau projet depuis le template

## 1. Créer le dépôt

Sur GitHub, **Use this template** (ou `git clone` puis changement du remote `origin`).

> Ne pas forker : un fork garde le template comme parent et les pull requests partiraient vers lui.

## 2. Démarrer

```console
make build
make up          # attend que les services soient « healthy »
make health      # {"status":"OK", ...}
make setup-hooks # active le hook pre-commit (make test)
make test        # Deptrac + PHPStan + PHPUnit
```

Ouvrir `https://localhost/api` (documentation OpenAPI) et accepter le certificat local
([docs/tls.md](../tls.md)). Si les ports 80/443 sont occupés, créer un `.env.local` :

```dotenv
HTTP_PORT=8080
HTTPS_PORT=8443
HTTP3_PORT=8443
```

## 3. Personnaliser

| Quoi                                | Où                                                                |
| ----------------------------------- | ----------------------------------------------------------------- |
| Nom et description du paquet        | `composer.json` (`name`, `description`)                           |
| Titre et description de l'API       | `config/packages/api_platform.yaml`                               |
| Version exposée par le health check | variable `APP_VERSION` (défaut dans `config/services.yaml`)       |
| Labels métier (`domain: <feature>`) | `.github/labels.yml`                                              |
| Présentation du projet              | `README.md` : remplacer le contenu de Dunglas par celui du projet |
| Historique                          | `CHANGELOG.md` : repartir d'une section `[Unreleased]` vide       |

Dans un projet, le `README.md` n'a plus besoin de rester proche de celui de Dunglas : il présente le
projet et pointe vers `docs/`.

## 4. Garder le health check

`GET /api/v1/health` est le test de fumée de toute la chaîne (CI comprise) : on le garde. Les
fonctionnalités métier s'ajoutent à côté, en suivant le
[pas-à-pas du guide d'architecture](../architecture/overview.md#-ajouter-une-fonctionnalité-pas-à-pas).

## 5. Rester à jour

`make template-sync` importe les nouveautés du template — voir [updating.md](updating.md).
