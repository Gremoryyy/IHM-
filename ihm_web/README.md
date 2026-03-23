# IHM Web (PHP/HTML) + portail d'accès

Cette partie contient l’interface web de supervision avec un portail d’accès simple.

## Fichiers principaux

- `index.php` : portail (CGU + code d'accès) et création de session.
- `dashboard.php` : IHM protégée par session.
- `logout.php` : déconnexion.
- `config.php` : configuration par défaut + chargement optionnel de `config.local.php` (non versionné).
- `lib/` : bootstrap + gestion auth/session/CSRF.
- `assets/` : CSS/JS.

## Configuration locale

Copier `config.local.php.example` vers `config.local.php` (non committé), puis définir un code d’accès :

```php
<?php
declare(strict_types=1);

return [
  'ACCESS_CODE' => 'change-moi',
];
```

## Lancement local

```bash
php -S localhost:8080 -t ihm_web
```
