# Guide d'architecture (Clean Architecture & DDD)

Ce guide décrit **comment le code est organisé et pourquoi**. Chaque règle renvoie à l'ADR qui la
justifie ; les outils qui la vérifient sont listés en fin de document.

---

## 🏗️ Les couches

Le code est rangé **par couche**, puis **par fonctionnalité** (`<Feature>` = une capacité métier :
`Order`, `Customer`...). Le template livre la fonctionnalité `System` (health check) comme exemple
de référence.

```text
src/
├── Domain/                         # MÉTIER PUR — aucune dépendance (ni Symfony, ni Doctrine)
│   ├── Entity/                     # Entités & agrégats, n'exposent que des Value Objects
│   ├── ValueObject/<Feature>/      # Valeurs immuables, invariants validés à la construction
│   │   └── IdentifierInterface + IdentifierTrait   # base de tous les identifiants
│   ├── ReadModel/<Feature>/        # Projections en lecture seule renvoyées par les use cases
│   ├── Event/<Feature>/            # Événements métier au passé (DomainEvent)
│   ├── Exception/                  # Catégories d'exceptions + Exception/<Feature>/
│   └── Port/<Concern>/             # Interfaces implémentées par l'Infrastructure
│       ├── Event/                  # DomainEventPublisherInterface
│       ├── Identity/               # IdGeneratorInterface, IdValidatorInterface
│       ├── Persistence/            # TransactionInterface
│       ├── Repository/             # <Aggregate>RepositoryInterface
│       └── Workflow/               # WorkflowEngineInterface
│
├── Application/                    # ORCHESTRATION
│   ├── UseCase/<Feature>/<Action>/ # <Action>Request, <Action>Response, <Action>UseCase
│   └── Service/<Feature>/          # Logique partagée par plusieurs use cases (ADR 0012)
│
└── Infrastructure/                 # TECHNIQUE — <Responsabilité>/<Technologie>/ (ADR 0010)
    ├── Event/Symfony/              # SymfonyDomainEventPublisher + Listener/<Feature>/
    ├── Identity/SymfonyUid/        # UUID v7
    ├── Persistence/Doctrine/       # DoctrineTransaction, Repository/, Mapping/ (XML), Type/, Fixtures/
    ├── Presentation/ApiPlatform/V1/
    │   ├── Resource/<Feature>/     # <Feature>Resource + <Action>Input / <Action>Output
    │   └── State/{Processor,Provider}/<Feature>/
    └── Workflow/Symfony/           # SymfonyWorkflowEngine
```

Les dossiers vides contiennent un `.gitkeep` : ils matérialisent l'emplacement attendu.

## 🎯 La règle de dépendance

```text
 Infrastructure ──► Application ──► Domain
```

- `Domain` ne dépend de **rien** d'autre que PHP.
- `Application` dépend du `Domain`. Dans `Application`, seuls les `UseCase` appellent les `Service`.
- `Infrastructure` dépend du `Domain`, des `UseCase` et des librairies.
- Le Domain parle au monde extérieur par des **ports** ; l'Infrastructure fournit les **adapters** ;
  `config/services.yaml` les relie (une seule « composition root »).

Vérifiée par **Deptrac** (`make deptrac`) — [ADR 0002](adr/0002-clean-architecture-layers-and-dependency-rule.md).

---

## 🔁 Le cycle d'une requête

```text
 HTTP (JSON)
    │
    ▼
 <Action>Input            DTO HTTP, contraintes #[Assert] déclaratives       (ADR 0007)
    │
    ▼
 Processor / Provider     adaptateur : convertit, ne décide rien, aucun try/catch  (ADR 0004)
    │
    ▼
 <Action>Request          primitives uniquement
    │
    ▼
 <Action>UseCase          construit les Value Objects, applique les règles, appelle les ports
    │
    ▼
 <Action>Response         enveloppe un ReadModel (jamais une entité)          (ADR 0003)
    │
    ▼
 <Action>Output           DTO HTTP, primitives → JSON / JSON-LD
```

L'exemple complet est le health check :

| Étape                        | Fichier                                                                                                          |
| ---------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| Resource                     | `Infrastructure/Presentation/ApiPlatform/V1/Resource/System/HealthCheckResource.php`                             |
| Provider                     | `Infrastructure/Presentation/ApiPlatform/V1/State/Provider/System/HealthCheckProvider.php`                       |
| Request / UseCase / Response | `Application/UseCase/System/CheckSystemStatus/`                                                                  |
| Read model                   | `Domain/ReadModel/System/SystemStatus.php`                                                                       |
| Output                       | `Infrastructure/Presentation/ApiPlatform/V1/Resource/System/HealthCheckOutput.php`                               |
| Tests                        | `tests/Unit/Application/UseCase/System/`, `tests/Api/HealthCheckApiTest.php`, `tests/http/v1/system/health.http` |

---

## 📐 Conventions par couche

### Domain

- Classes `final` (entités) ou `final readonly` (Value Objects, read models, événements).
- Les entités se construisent par des **constructeurs nommés** (`Order::place(...)`) qui garantissent
  leurs invariants ; pas de setter public sans règle métier.
- Chaque propriété scalaire métier est un **Value Object** ; une liste est une collection
  (`IteratorAggregate` + `Countable`) — [ADR 0005](adr/0005-value-objects-instead-of-primitives.md).
- Un Value Object n'est jamais nul ; l'optionalité est portée par l'entité (`?PhoneNumber`) —
  [ADR 0006](adr/0006-strict-non-nullable-value-objects.md).
- Les identifiants utilisent `IdentifierTrait` : `OrderId::make($value, $validator)`.
- Les Value Objects lèvent `InvalidValueException` ; jamais l'exception SPL d'argument invalide.

### Application

- Un use case par action, trois classes, contrat générique `UseCaseInterface<Request, Response>` —
  [ADR 0003](adr/0003-use-case-contract.md).
- Un use case ne renvoie jamais `null` et déclare ses exceptions métier avec `@throws`.
- Plusieurs écritures → `TransactionInterface::run()`, sans appel réseau à l'intérieur —
  [ADR 0014](adr/0014-transactions-through-a-domain-port.md).
- Publication des événements après la persistance, via `DomainEventPublisherInterface` —
  [ADR 0013](adr/0013-domain-events-through-a-port.md).

### Infrastructure

- `<Responsabilité>/<Technologie>/`, jamais de dossier `Service`, `Gateway`, `Utils`, `Helper` —
  [ADR 0010](adr/0010-infrastructure-by-concern-then-technology.md).
- Un adapter s'appelle `<Technologie><Port>` (`DoctrineTransaction`, `SymfonyUuidGenerator`).
- Les entités sont mappées en **XML** (`Persistence/Doctrine/Mapping/<Entity>.orm.xml`), un type DBAL
  par Value Object stocké, l'unicité garantie par une contrainte en base et traduite en
  `AlreadyExistException` — [ADR 0015](adr/0015-persistence-doctrine-xml-mapping.md).
- API Platform : une Resource par fonctionnalité qui ne fait que déclarer, un Input par écriture,
  un Output par représentation, `$uriVariables` décrits en PHPDoc shape —
  [ADR 0004](adr/0004-api-platform-confined-to-presentation.md),
  [ADR 0008](adr/0008-uri-variables-contract-in-providers.md).

### Nommage

| Élément                     | Convention                               | Exemple                            |
| --------------------------- | ---------------------------------------- | ---------------------------------- |
| Use case                    | `<Verbe><Nom>UseCase`                    | `PlaceOrderUseCase`                |
| Entrée / sortie de use case | `<Action>Request` / `<Action>Response`   | `PlaceOrderRequest`                |
| Read model                  | nom métier, pas de suffixe technique     | `PlacedOrder`, `OrderDetails`      |
| Port                        | `<Rôle>Interface`                        | `OrderRepositoryInterface`         |
| Adapter                     | `<Technologie><Rôle>`                    | `DoctrineOrderRepository`          |
| Événement                   | participe passé                          | `OrderPlaced`                      |
| Exception                   | fait métier + catégorie                  | `OrderNotFoundException::withId()` |
| DTO HTTP                    | `<Action>Input` / `<Action>Output`       | `PlaceOrderInput`                  |
| Provider / Processor        | `<Action>Provider` / `<Action>Processor` | `GetOrderProvider`                 |
| Service applicatif          | ce qu'il fait, jamais `XxxService`       | `InvoiceNumberer`                  |

---

## 🚨 Gestion des erreurs

Les erreurs métier sont **classées par catégorie** dans le Domain et **traduites en HTTP une seule
fois**, par configuration (`config/packages/api_platform.yaml`) —
[ADR 0009](adr/0009-business-exceptions-translated-once.md).

| Catégorie (`Domain/Exception/`) | Statut HTTP | Exemple                                                  |
| ------------------------------- | ----------- | -------------------------------------------------------- |
| `NotFoundException`             | 404         | `OrderNotFoundException`                                 |
| `AlreadyExistException`         | 409         | `OrderReferenceAlreadyExistsException`                   |
| `InvalidDataException`          | 422         | `InvalidValueException`, `TransitionNotAllowedException` |

- Une nouvelle exception métier hérite de la bonne catégorie : aucun changement côté HTTP.
- Les adapters ne contiennent **aucun `try/catch`** d'exception métier.
- Les messages partent au client : **jamais** de donnée personnelle dedans.

---

## ➕ Ajouter une fonctionnalité (pas à pas)

Exemple : « passer une commande » (`POST /api/v1/orders`).

1. **Issue** (template User Story) puis branche `feature/<n>-place-order` — [ADR 0017](adr/0017-issue-driven-delivery-workflow.md).
2. **Domain** : `ValueObject/Order/{OrderId,OrderReference,Quantity}`, `Entity/Order` (constructeur
   nommé), `Exception/Order/OrderReferenceAlreadyExistsException` (extends `AlreadyExistException`),
   `Port/Repository/OrderRepositoryInterface`, `ReadModel/Order/PlacedOrder`, `Event/Order/OrderPlaced`.
   Tests unitaires des Value Objects et de l'entité.
3. **Application** : `UseCase/Order/PlaceOrder/{PlaceOrderRequest,PlaceOrderResponse,PlaceOrderUseCase}`,
   `@throws` déclarés. Test unitaire avec des fakes des ports.
4. **Persistence** : `Persistence/Doctrine/Mapping/Order.orm.xml`, `Type/OrderIdType` (+ déclaration
   dans `doctrine.yaml`), `Repository/DoctrineOrderRepository` (traduction de la violation d'unicité),
   binding du port dans `config/services.yaml`, `make migration-diff`.
5. **Présentation** : `V1/Resource/Order/{OrderResource,PlaceOrderInput,OrderOutput}`,
   `V1/State/Processor/Order/PlaceOrderProcessor`. Tests API : 201, 409, 422. Requête manuelle dans
   `tests/http/v1/orders/`.
6. `make test`, pull request avec le template, ADR si une convention a changé.

---

## ✅ Qualité

| Outil    | Commande       | Vérifie                                                                    |
| -------- | -------------- | -------------------------------------------------------------------------- |
| Deptrac  | `make deptrac` | Règle de dépendance entre couches                                          |
| PHPStan  | `make phpstan` | Niveau max sur `src/` et `tests/`, génériques, exceptions métier déclarées |
| PHPUnit  | `make phpunit` | Tests unitaires, d'architecture, d'intégration et API                      |
| Doctrine | `make test-db` | Base de test reconstruite par les migrations + `doctrine:schema:validate`  |
| Tout     | `make test`    | Les quatre ci-dessus — lancé aussi par le hook pre-commit et la CI         |

Détails : [ADR 0016](adr/0016-quality-gate.md).
