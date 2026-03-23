# Robot6DDL

Projet scolaire **Robot 6DDL** dédié à la synchronisation de 3 robots, avec une IHM web, une API PHP/MySQL, une base SQL et du code embarqué ESP32.

## Organisation du dépôt

- `api/` : backend PHP (endpoints API).
- `sql/` : script SQL de création de la base `robot6ddl`.
- `ihm2/` : interface web (portail / dashboard).
- `esp32/` : code embarqué des robots.
- `docs/` : documentation projet et workflow.
- `tests/` : exemples de tests manuels (curl).

## Politique de branches

La branche `main` est la branche de référence **propre, structurée et stable**.

Le développement se fait sur des branches séparées (par élève ou fonctionnalité), puis est fusionné dans `main` uniquement lorsque le travail est propre et validé.
