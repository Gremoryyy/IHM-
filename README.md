# IHM- — Supervision de robots collaboratifs 6DDL

Ce dépôt regroupe les bases techniques d’un projet BTS CIEL autour de **3 robots collaboratifs 6DDL**.

L’objectif de cette phase est de fournir une base **propre, structurée et maintenable** pour un travail d’équipe (IHM web, ESP32, documentation et scripts SQL à venir).

## Objectif global

- Superviser 3 robots via une IHM web.
- Préparer l’intégration avec ESP32/Arduino et base de données MySQL.
- Travailler de façon collaborative avec une structure de dépôt claire.

## Structure du dépôt

```text
.
├── docs/
│   └── setup/
├── esp32/
│   ├── README.md
│   └── robot_pince/
├── ihm_web/
│   ├── assets/
│   ├── lib/
│   └── README.md
├── .gitignore
└── README.md
```

## Technologies utilisées (actuelles)

- **PHP / HTML / CSS / JS** : IHM web de supervision.
- **ESP32 (Arduino framework)** : prototype de code embarqué.
- **Git / GitHub** : collaboration et versionning.

## Grandes parties du projet

- `ihm_web/` : portail d’accès + dashboard de supervision (prototype).
- `esp32/` : code embarqué ESP32 et documentation de déploiement.
- `docs/` : documentation de mise en route et notes d’organisation.

## Lancement minimal (IHM web)

Depuis la racine du dépôt :

```bash
php -S localhost:8080 -t ihm_web
```

Puis ouvrir :

- Portail : `http://localhost:8080/`
- Dashboard : `http://localhost:8080/dashboard.php`

Configuration locale du code d’accès :

1. Copier `ihm_web/config.local.php.example` vers `ihm_web/config.local.php`
2. Modifier `ACCESS_CODE`

## Remarques importantes

- Le dashboard est actuellement un **prototype d’interface** (boutons simulés côté front).
- L’intégration réelle ESP32/Arduino/BDD reste à brancher progressivement.
- Les fichiers de configuration locale (`config.local.php`) ne doivent pas être versionnés.

## Recommandation Git (travail d’équipe)

- `main` : branche stable.
- Créer une branche de travail par tâche (`feature/...`, `fix/...`, `docs/...`).
- Faire relire puis fusionner (merge) uniquement les changements validés.
