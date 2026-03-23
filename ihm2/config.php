<?php
declare(strict_types=1);

/**
 * Configuration par défaut.
 * Pour surcharger sans committer : créer `config.local.php` qui retourne un tableau de clés identiques.
 */

$default = [
    // Si vide : le portail demandera seulement d'accepter les CGU.
    // Recommandé : définir un code (ex: "robot2026").
    'ACCESS_CODE' => 'robot2026',

    // Durée de session (en minutes) avant de demander de se reconnecter.
    'SESSION_TTL_MINUTES' => 240,

    // Nom du cookie de session (évite les collisions avec d'autres projets).
    'SESSION_NAME' => 'robot_ihm2_session',

    // Base de donnees MariaDB / MySQL.
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => 3306,
    'DB_NAME' => 'robot6ddl',
    'DB_USER' => 'root',
    'DB_PASS' => '',

    // Google reCAPTCHA v2
    'RECAPTCHA_SITE_KEY' => '',
    'RECAPTCHA_SECRET_KEY' => '',
];

$localPath = __DIR__ . '/config.local.php';
if (is_file($localPath)) {
    /** @var array<string, mixed> $local */
    $local = require $localPath;
    if (!is_array($local)) {
        throw new RuntimeException('config.local.php doit retourner un tableau PHP.');
    }
    $default = array_replace($default, $local);
}

return $default;
