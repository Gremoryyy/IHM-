# Backend PHP / MySQL - Robot6DDL

## Rôle de cette partie

Cette partie correspond au backend PHP/MySQL du projet **Robot6DDL**.

Elle sert à faire le lien entre :
- l’IHM web,
- la base de données MySQL,
- et les robots / ESP32.

Le backend permet notamment :
- la connexion utilisateur,
- la récupération des positions des robots depuis la base,
- la gestion des commandes envoyées aux robots,
- la lecture de l’état des robots,
- la lecture et la mise à jour de l’état des capteurs,
- le suivi d’exécution des commandes.

## Fichiers inclus

- `database.php` : connexion à la base MySQL
- `login.php` : authentification utilisateur
- `get_positions.php` : récupération des positions d’un robot
- `create_command.php` : création d’une commande robot
- `get_pending_command.php` : récupération de la prochaine commande en attente
- `update_command_status.php` : mise à jour du statut d’une commande
- `get_sensor_states.php` : lecture de l’état des capteurs
- `update_sensor_state.php` : mise à jour de l’état des capteurs
- `get_robots.php` : lecture de l’état des robots
- `robot6ddl.sql` : export SQL de la base de données

## Base de données

La base utilisée s’appelle :

`robot6ddl`

Important :
la base MySQL n’est pas stockée “vivante” dans GitHub.  
Le dépôt contient seulement le fichier SQL permettant de la recréer.

## Installation locale

### 1. Pré-requis

- XAMPP ou LAMPP
- Apache
- MySQL / MariaDB
- PHP
- phpMyAdmin recommandé

### 2. Importer la base

Dans phpMyAdmin :
1. créer une base nommée `robot6ddl`
2. importer le fichier `robot6ddl.sql`

### 3. Placer les fichiers PHP

Les fichiers PHP doivent être placés dans un dossier web accessible par Apache, par exemple :

`htdocs/robot6ddl/`

Exemple Linux LAMPP :
`/opt/lampp/htdocs/robot6ddl/`

## Configuration actuelle

Dans `database.php`, la configuration de développement local utilisée est :

- host : `localhost`
- database : `robot6ddl`
- user : `root`
- password : vide

Cette configuration correspond à un usage local de développement.

## Endpoints disponibles

### `login.php`
Authentifie un utilisateur et écrit un log de connexion.

### `get_positions.php`
Retourne les positions enregistrées pour un robot, une commande et éventuellement une boîte.

### `create_command.php`
Crée une nouvelle commande robot dans la base.

### `get_pending_command.php`
Récupère la prochaine commande en attente pour un robot et la passe en `in_progress`.

### `update_command_status.php`
Met à jour le statut d’une commande (`completed`, `error`, etc.).

### `get_sensor_states.php`
Retourne l’état des capteurs d’un robot.

### `update_sensor_state.php`
Met à jour l’état d’un capteur.

### `get_robots.php`
Retourne la liste des robots et leur état actuel.

## Cycle normal d’exécution d’une commande

Le fonctionnement validé est le suivant :

1. création d’une commande avec `create_command.php`
2. récupération par le robot / ESP32 via `get_pending_command.php`
3. passage automatique de la commande en `in_progress`
4. exécution côté robot
5. mise à jour finale via `update_command_status.php`

Important :
`create_command.php` ne change pas directement le statut du robot.

Le changement d’état intervient quand la commande est réellement terminée.

Exemples :
- `start` + `completed` => robot `active`
- `stop` + `completed` => robot `inactive`

## Exemples de tests curl

### Connexion utilisateur

```bash
curl -X POST http://localhost/robot6ddl/login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
