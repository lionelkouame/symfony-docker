# Mettre à jour le template et les projets

Deux flux, deux commandes :

```text
 dunglas/symfony-docker ──(make upstream-merge)──► ce template ──(make template-sync)──► projet A
                                                                └─(make template-sync)──► projet B
```

---

## 1. Dunglas → template (`make upstream-merge`)

Dans **ce dépôt** uniquement :

```console
git switch -c chore/upstream-sync-$(date +%Y%m%d)
make upstream-merge   # ajoute le remote upstream si besoin, fetch, merge --no-ff upstream/main
```

C'est un vrai `merge` : l'historique garde chaque commit de Dunglas, donc la fusion suivante ne
rejoue que les nouveaux.

### Fichiers de Dunglas modifiés par le template

Ce sont les **seuls** endroits où un conflit est possible. Toute nouvelle modification d'un fichier
upstream doit être ajoutée à cette liste dans la même pull request.

| Fichier | Modification | Résolution d'un conflit |
| ------- | ------------ | ----------------------- |
| `composer.json` | application installée (upstream : `{}`) | garder la version du template |
| `compose.yaml` | bloc `###> doctrine/doctrine-bundle ###` (service `database`, volume) | garder les deux : le bloc de recipe et le changement upstream |
| `compose.override.yaml` | bloc `###> doctrine/doctrine-bundle ###` (port de la base) | idem |
| `Dockerfile` | bloc `###> doctrine/doctrine-bundle ###` (`pdo_pgsql`) dans `###> recipes ###` | idem |
| `.github/workflows/ci.yaml` | étapes de test décommentées + health check, Deptrac, PHPStan | reprendre le changement upstream, puis réappliquer les étapes du template |
| `.editorconfig` | section `[{Makefile,.githooks/*}]` en fin de fichier | garder les deux |
| `README.md` | bloc `<!-- ###> clean-architecture ### -->` sous le titre | garder les deux |

`composer.lock` est marqué `merge=ours` dans `.gitattributes` (règle de Dunglas) : en cas de conflit,
le lock du template est conservé ; relancer `composer update --lock` si `composer.json` a bougé.

### Après la fusion

```console
make refresh   # reconstruit les images sans cache, avec les dernières images de base
make up
make test
make health
```

Puis pull request, CI verte, merge, et une entrée dans `CHANGELOG.md`.

---

## 2. Template → projet (`make template-sync`)

Dans **un projet créé depuis le template** :

```console
git switch -c chore/template-sync
make template-sync   # coopTilleuls/template-sync contre ce template
```

[template-sync](https://github.com/coopTilleuls/template-sync) (l'outil recommandé par Dunglas, voir
[docs/updating.md](../updating.md)) calcule les changements du template depuis la dernière
synchronisation et les applique par `cherry-pick`. En cas de conflit : résoudre, puis
`git cherry-pick --continue`. Lire `CHANGELOG.md` du template pour savoir ce qui arrive.

L'URL du template est la variable `TEMPLATE_URL` du `Makefile` (`make template-sync TEMPLATE_URL=...`
pour un autre dépôt).

---

## 3. Mettre à jour les dépendances PHP

Dependabot propose chaque semaine les mises à jour de Composer, de Docker et des actions GitHub
(`.github/dependabot.yaml`, hérité de Dunglas). Pour une montée de version manuelle :

```console
make composer c='update --with-all-dependencies'
make test
```

Une montée de version majeure de Symfony se fait en changeant `extra.symfony.require` et les
contraintes `8.x.*` de `composer.json`.
