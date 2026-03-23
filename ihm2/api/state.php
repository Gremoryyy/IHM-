<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../lib/store.php';

ensure_session_started($CONFIG);
portal_require_auth($CONFIG);

header('Content-Type: application/json; charset=utf-8');

$state = robot_load_state();

echo json_encode([
    'ok' => true,
    'csrf' => csrf_token(),
    'assignment_options' => robot_assignment_options(),
    'state' => $state,
], JSON_UNESCAPED_UNICODE);
