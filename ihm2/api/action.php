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

$state = robot_load_state();

if ($action === 'launch_all') {
    foreach ($state['robots'] as $index => $robot) {
        if (!($robot['active'] ?? false)) {
            continue;
        }

        $assignmentKey = (string)($robot['assignment'] ?? 'position_1');
        $state['robots'][$index]['running'] = true;
        $state['robots'][$index]['status'] = 'En cours';
        $state['robots'][$index]['last_log'] = 'Lancement collaboratif';
        $state['robots'][$index]['current_angles'] = robot_angles_for_assignment($assignmentKey, true);
    }

    robot_save_state($state);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'state' => $state], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($robotId <= 0) {
    http_response_code(422);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Robot invalide.'], JSON_UNESCAPED_UNICODE);
    exit;
}

foreach ($state['robots'] as $index => $robot) {
    if ((int)($robot['id'] ?? 0) !== $robotId) {
        continue;
    }

    switch ($action) {
        case 'toggle_active':
            $isActive = !($robot['active'] ?? false);
            $state['robots'][$index]['active'] = $isActive;
            $state['robots'][$index]['running'] = false;
            $state['robots'][$index]['status'] = $isActive ? 'Pret' : 'Inactif';
            $state['robots'][$index]['last_log'] = $isActive ? 'Robot reactive' : 'Robot desactive';
            $state['robots'][$index]['current_angles'] = robot_angles_for_assignment((string)$robot['assignment'], false);
            break;

        case 'toggle_running':
            if (!($robot['active'] ?? false)) {
                http_response_code(422);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'error' => 'Le robot est inactif.'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $isRunning = !($robot['running'] ?? false);
            $assignmentKey = (string)($robot['assignment'] ?? 'position_1');
            $state['robots'][$index]['running'] = $isRunning;
            $state['robots'][$index]['status'] = $isRunning ? 'En cours' : 'Pret';
            $state['robots'][$index]['last_log'] = $isRunning ? 'Sequence lancee' : 'Sequence arretee';
            $state['robots'][$index]['current_angles'] = robot_angles_for_assignment($assignmentKey, $isRunning);
            break;

        case 'set_assignment':
            $options = robot_assignment_options();
            if (!isset($options[$assignment])) {
                http_response_code(422);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'error' => 'Position invalide.'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $state['robots'][$index]['assignment'] = $assignment;
            $state['robots'][$index]['status'] = ($robot['active'] ?? false) ? (($robot['running'] ?? false) ? 'En cours' : 'Pret') : 'Inactif';
            $state['robots'][$index]['last_log'] = 'Affectation mise a jour';
            $state['robots'][$index]['current_angles'] = robot_angles_for_assignment($assignment, (bool)($robot['running'] ?? false));
            break;

        default:
            http_response_code(422);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'Action inconnue.'], JSON_UNESCAPED_UNICODE);
            exit;
    }

    robot_save_state($state);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'state' => $state], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => false, 'error' => 'Robot introuvable.'], JSON_UNESCAPED_UNICODE);
