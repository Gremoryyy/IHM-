<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../lib/store.php';

ensure_session_started($CONFIG);
portal_require_auth($CONFIG);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Methode non autorisee.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '', true);
if (!is_array($payload)) {
    $payload = [];
}

csrf_verify_request_or_fail($payload);

$action = (string)($payload['action'] ?? '');
$robotId = (int)($payload['robot_id'] ?? 0);
$assignment = (string)($payload['assignment'] ?? '');
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

try {
    $state = robot_load_state($CONFIG);
} catch (Throwable $e) {
    robot_json_response([
        'ok' => false,
        'error' => 'Impossible de charger la BDD.',
        'details' => $e->getMessage(),
    ], 500);
}

if ($action === 'launch_all') {
    foreach ($state['robots'] as $robot) {
        if (!($robot['active'] ?? false)) {
            continue;
        }

        robot_create_motion_command(
            $CONFIG,
            (int)$robot['id'],
            (int)$robot['box_number'],
            $userId,
            'Lancement collaboratif depuis l IHM'
        );
    }

    robot_json_response([
        'ok' => true,
        'state' => robot_load_state($CONFIG),
    ]);
}

if ($robotId <= 0) {
    robot_json_response(['ok' => false, 'error' => 'Robot invalide.'], 422);
}

foreach ($state['robots'] as $robot) {
    if ((int)($robot['id'] ?? 0) !== $robotId) {
        continue;
    }

    switch ($action) {
        case 'toggle_active':
            robot_update_active_state($CONFIG, $robotId, !($robot['active'] ?? false), $userId);
            break;

        case 'toggle_running':
            if (!($robot['active'] ?? false)) {
                robot_json_response(['ok' => false, 'error' => 'Le robot est inactif.'], 422);
            }

            if ($robot['running'] ?? false) {
                robot_create_stop_command($CONFIG, $robotId, $userId);
            } else {
                robot_create_motion_command($CONFIG, $robotId, (int)$robot['box_number'], $userId, 'Commande unitaire depuis l IHM');
            }
            break;

        case 'set_assignment':
            $options = robot_assignment_options();
            if (!isset($options[$assignment])) {
                robot_json_response(['ok' => false, 'error' => 'Position invalide.'], 422);
            }

            robot_save_assignment($CONFIG, $robotId, robot_box_number_from_assignment($assignment), $userId);
            break;

        default:
            robot_json_response(['ok' => false, 'error' => 'Action inconnue.'], 422);
    }

    robot_json_response([
        'ok' => true,
        'state' => robot_load_state($CONFIG),
    ]);
}

robot_json_response(['ok' => false, 'error' => 'Robot introuvable.'], 404);
