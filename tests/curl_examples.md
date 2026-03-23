# Exemples curl (API PHP)

> Remplacez `http://localhost/api` par l'URL réelle du backend.

## login.php

```bash
curl -X POST "http://localhost/api/login.php" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}'
```

## get_positions.php

```bash
curl "http://localhost/api/get_positions.php"
```

## get_robots.php

```bash
curl "http://localhost/api/get_robots.php"
```

## get_sensor_states.php

```bash
curl "http://localhost/api/get_sensor_states.php"
```

## create_command.php

```bash
curl -X POST "http://localhost/api/create_command.php" \
  -H "Content-Type: application/json" \
  -d '{"robot_id":1,"command":"start"}'
```

## get_pending_command.php

```bash
curl "http://localhost/api/get_pending_command.php?robot_id=1"
```

## update_command_status.php

```bash
curl -X POST "http://localhost/api/update_command_status.php" \
  -H "Content-Type: application/json" \
  -d '{"command_id":12,"status":"completed"}'
```

## Rappel de logique backend

- `create_command.php` crée une commande mais ne change pas immédiatement le statut du robot.
- `get_pending_command.php` récupère une commande en attente et la passe en `in_progress`.
- `update_command_status.php` termine la commande.
- `start` + `completed` => robot `active`.
- `stop` + `completed` => robot `inactive`.
