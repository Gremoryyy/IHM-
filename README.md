# Robot pince

Ce dépôt contient :

- `robot_pince.ino` : code Arduino (pince/robot).
- `ihm2/` : IHM web (PHP/HTML) + page d'accès (portail "application").

## Démarrer l'IHM (portail + dashboard)

1) Configure le code d'accès (optionnel mais recommandé) :

- Copier `ihm2/config.local.php.example` → créer `ihm2/config.local.php`
- Modifier `ACCESS_CODE` dans `ihm2/config.local.php`

2) Lancer un serveur PHP depuis la racine du repo :

```bash
php -S localhost:8080 -t ihm2
```

3) Ouvrir :

- Portail : http://localhost:8080/
- IHM : http://localhost:8080/dashboard.php

> Note : si la commande `php` n'existe pas sur ton Mac, installe PHP via Homebrew (`brew install php`) ou utilise MAMP/XAMPP.
