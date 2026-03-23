<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../lib/store.php';

ensure_session_started($CONFIG);
portal_require_auth($CONFIG);

try {
    $state = robot_load_state($CONFIG);

    robot_json_response([
        'ok' => true,
        'csrf' => csrf_token(),
        'assignment_options' => robot_assignment_options(),
        'state' => $state,
    ]);
} catch (Throwable $e) {
    robot_json_response([
        'ok' => false,
        'error' => 'Impossible de charger la BDD.',
        'details' => $e->getMessage(),
    ], 500);
}
