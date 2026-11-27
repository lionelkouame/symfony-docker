# Architecture Decision Records

Les ADR consignent les décisions d'architecture du template. Elles sont **agnostiques** : elles
s'appliquent à tout projet créé depuis ce template, quel que soit son métier. Les exemples
utilisent un domaine fictif (`Order`, `Customer`).

| ADR                                                           | Décision                                                                    | Vérifiée par                                                |
| ------------------------------------------------------------- | --------------------------------------------------------------------------- | ----------------------------------------------------------- |
| [0001](0001-template-built-on-dunglas-symfony-docker.md)      | Le template repose sur dunglas/symfony-docker et se personnalise par ajout  | `make upstream-merge`, liste des fichiers upstream modifiés |
| [0002](0002-clean-architecture-layers-and-dependency-rule.md) | Couches Domain / Application / Infrastructure, dépendances vers l'intérieur | Deptrac                                                     |
| [0003](0003-use-case-contract.md)                             | Contrat des use cases : Request, UseCase, Response + read model             | PHPStan (génériques)                                        |
| [0004](0004-api-platform-confined-to-presentation.md)         | API Platform confiné à la couche Présentation, versionné                    | Deptrac, revue                                              |
| [0005](0005-value-objects-instead-of-primitives.md)           | Value Objects plutôt que primitifs et tableaux                              | Revue, tests unitaires                                      |
| [0006](0006-strict-non-nullable-value-objects.md)             | Value Objects jamais nuls, l'optionalité est portée par l'entité            | Revue                                                       |
| [0007](0007-declarative-validation-and-domain-double-lock.md) | Validation déclarative des Input + double verrou du Domain                  | Tests API                                                   |
| [0008](0008-uri-variables-contract-in-providers.md)           | Contrat des `$uriVariables` en PHPDoc shapes                                | PHPStan                                                     |
| [0009](0009-business-exceptions-translated-once.md)           | Exceptions métier catégorisées, traduites en HTTP une seule fois            | Tests d'architecture, PHPStan                               |
| [0010](0010-infrastructure-by-concern-then-technology.md)     | Ports et adapters rangés par responsabilité puis par technologie            | Revue                                                       |
| [0011](0011-workflows-behind-a-domain-port.md)                | Workflows déclarés en configuration, pilotés par un port                    | Tests unitaires                                             |
| [0012](0012-application-services-shared-by-use-cases.md)      | Services applicatifs pour la logique partagée entre use cases               | Deptrac                                                     |
| [0013](0013-domain-events-through-a-port.md)                  | Événements du Domain publiés via un port                                    | Revue                                                       |
| [0014](0014-transactions-through-a-domain-port.md)            | Transactions via un port du Domain                                          | Test d'intégration                                          |
| [0015](0015-persistence-doctrine-xml-mapping.md)              | Mapping Doctrine XML, types personnalisés, unicité garantie par la base     | `doctrine:schema:validate`                                  |
| [0016](0016-quality-gate.md)                                  | Gate qualité : PHPStan max, Deptrac, PHPUnit, tests d'architecture          | CI, pre-commit                                              |
| [0017](0017-issue-driven-delivery-workflow.md)                | Issue → branche → commits conventionnels → pull request                     | Templates GitHub                                            |

## Origine

Ces décisions sont issues du projet Compliance Store. Les ADR propres à son métier (chiffrement
d'enveloppe, moteur réglementaire, réponses binaires...) n'ont pas été reprises. Chaque ADR indique
sa source et, en fin de document, ce qui a été **corrigé** par rapport à l'original lorsque la
documentation n'était plus alignée avec le code.

## Rédiger un nouvel ADR

1. Copier [`template.md`](template.md) vers `NNNN-titre-en-kebab-case.md` (numéro suivant).
2. Remplir **Context**, **Decision**, **Consequences**, et si utile **Alternatives Considered**.
3. Statut `Proposed` tant que la pull request n'est pas fusionnée, puis `Accepted`.
4. Une décision qui en remplace une autre la marque `Superseded by ADR NNNN` : on ne réécrit pas
   l'histoire, on la complète.
5. Ajouter la ligne au tableau ci-dessus et mettre à jour le
   [guide d'architecture](../overview.md) si une convention change.
