<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../lib/store.php';

ensure_session_started($CONFIG);
portal_require_auth($CONFIG);

try {
    // 🔄 Chargement état global
    $state = robot_load_state($CONFIG);

    // 📦 On renvoie directement les robots (format attendu par le dashboard)
    robot_json_response([
        'ok' => true,

        // 🔐 sécurité / UI
        'csrf' => csrf_token(),
        'assignment_options' => robot_assignment_options(),

        // 🤖 IMPORTANT : format simplifié pour le frontend
        'robots' => $state['robots'],
    ]);

} catch (Throwable $e) {

    robot_json_response([
        'ok' => false,
        'error' => 'Impossible de charger la BDD.',
        'details' => $e->getMessage(),
    ], 500);
}