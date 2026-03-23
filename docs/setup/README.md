# Setup local (minimum)

## IHM web

```bash
php -S localhost:8080 -t ihm_web
```

Accès :
- `http://localhost:8080/`
- `http://localhost:8080/dashboard.php`

## Configuration locale non versionnée

```bash
cp ihm_web/config.local.php.example ihm_web/config.local.php
```

Puis modifier `ACCESS_CODE` dans `ihm_web/config.local.php`.
