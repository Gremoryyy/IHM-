# Architecture du dépôt Robot6DDL

Le dépôt est organisé par fonction afin de centraliser les composants du projet Robot6DDL :

- **IHM web** : `ihm2/`
- **Backend PHP / MySQL** : `api/`
- **SQL de création de base** : `sql/`
- **Code embarqué ESP32 / robots** : `esp32/`
- **Documentation** : `docs/`
- **Tests manuels (curl)** : `tests/`

Cette structure permet de garder `main` lisible et maintenable, sans mélange de fichiers par élève.
