# IHM2 (PHP/HTML) + portail d'accès

Objectif : une **page d'accès** simple (portail côté application) qui protège l'accès à l'IHM.

## Fichiers

- `index.php` : portail (CGU + code d'accès) et création de session.
- `dashboard.php` : IHM (protégée par session).
- `logout.php` : déconnexion.
- `config.php` : configuration par défaut + chargement optionnel de `config.local.php` (non versionné).
- `lib/` : bootstrap + auth (sessions/CSRF).
- `assets/` : CSS/JS.

## Configuration

Copier `config.local.php.example` → `config.local.php` (non committé) pour définir un code d'accès différent :

```php
<?php
declare(strict_types=1);

return [
  'ACCESS_CODE' => 'change-moi',
];
```

## Lancement

```bash
php -S localhost:8080 -t ihm2
```
