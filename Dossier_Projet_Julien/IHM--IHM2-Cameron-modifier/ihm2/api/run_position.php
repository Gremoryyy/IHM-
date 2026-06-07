<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    robot_json_response([
        'ok' => false,
        'error' => 'Méthode non autorisée. Utilise POST.'
    ], 405);
}

$data = robot_request_data();

$robotId = (int)($data['robot_id'] ?? 0);
$actionName = trim((string)($data['action_name'] ?? ''));

if ($robotId <= 0 || $actionName === '') {
    robot_json_response([
        'ok' => false,
        'error' => 'robot_id et action_name sont obligatoires.'
    ], 400);
}

function call_esp32_json(string $url, int $timeout = 120): array
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => $timeout,
        ]
    ]);

    $raw = @file_get_contents($url, false, $context);

    if ($raw === false) {
        return [
            'ok' => false,
            'error' => "Impossible d'appeler l'ESP32 : {$url}",
        ];
    }

    $json = json_decode($raw, true);

    if (!is_array($json)) {
        return [
            'ok' => false,
            'error' => "Réponse invalide de l'ESP32 : {$url}",
            'raw' => $raw,
        ];
    }

    return $json;
}

try {
    $pdo = robot_get_pdo($CONFIG);

    // 1. Récupérer le robot et son IP
    $robotStmt = $pdo->prepare('
    SELECT id, robot_name, ip_address
    FROM robots
    WHERE id = ?
    LIMIT 1
    ');
    $robotStmt->execute([$robotId]);
    $robot = $robotStmt->fetch();

    if (!is_array($robot)) {
        robot_json_response([
            'ok' => false,
            'error' => 'Robot introuvable en BDD.'
        ], 404);
    }

    $ip = (string)($robot['ip_address'] ?? '');

    if ($ip === '') {
        robot_json_response([
            'ok' => false,
            'error' => 'Aucune IP configurée pour ce robot.'
        ], 422);
    }

    // 2. Récupérer les positions depuis la BDD
    $positionsStmt = $pdo->prepare('
    SELECT
    id,
    step_order,
    servo_base_angle,
    servo_shoulder_angle,
    servo_elbow_angle,
    servo_wrist_angle,
    servo_rotate_angle,
    servo_grip_angle,
    speed_ms_per_degree,
    pause_after_ms,
    commentaire
    FROM robot_positions
    WHERE robot_id = ?
    AND action_name = ?
    ORDER BY step_order ASC
    ');
    $positionsStmt->execute([$robotId, $actionName]);
    $positions = $positionsStmt->fetchAll();

    if (count($positions) === 0) {
        robot_json_response([
            'ok' => false,
            'error' => 'Aucune position trouvée pour cette action.',
            'robot_id' => $robotId,
            'action_name' => $actionName
        ], 404);
    }

    // 3. Créer une commande en BDD
    $payload = json_encode([
        'robot_id' => $robotId,
        'action_name' => $actionName,
        'positions' => $positions,
    ], JSON_UNESCAPED_UNICODE);

    $commandStmt = $pdo->prepare('
    INSERT INTO robot_commands
    (robot_id, created_by_user_id, target_robot_id, action_name, payload, status, details, started_at)
    VALUES (?, NULL, NULL, ?, ?, "in_progress", ?, NOW())
    ');
    $commandStmt->execute([
        $robotId,
        $actionName,
        $payload,
        'Exécution depuis run_position.php'
    ]);

    $commandId = (int)$pdo->lastInsertId();

    // 4. Activer le robot côté ESP32
    call_esp32_json("http://{$ip}/activate", 5);

    $executedSteps = [];

    // 5. Envoyer chaque étape à l’ESP32
    foreach ($positions as $position) {
        $query = http_build_query([
            'base' => (int)$position['servo_base_angle'],
                                  'shoulder' => (int)$position['servo_shoulder_angle'],
                                  'elbow' => (int)$position['servo_elbow_angle'],
                                  'wrist' => (int)$position['servo_wrist_angle'],
                                  'rotate' => (int)$position['servo_rotate_angle'],
                                  'grip' => (int)$position['servo_grip_angle'],
                                  'speed' => (int)$position['speed_ms_per_degree'],
                                  'pause' => (int)$position['pause_after_ms'],
        ]);

        $url = "http://{$ip}/set_angles?{$query}";
        $response = call_esp32_json($url, 120);

        $executedSteps[] = [
            'step_order' => (int)$position['step_order'],
            'url' => $url,
            'response' => $response,
        ];

        if (($response['ok'] ?? false) !== true) {
            $failStmt = $pdo->prepare('
            UPDATE robot_commands
            SET status = "failed", executed_at = NOW(), details = ?
            WHERE id = ?
            ');
            $failStmt->execute([
                'Erreur ESP32 à l’étape ' . (int)$position['step_order'],
                               $commandId
            ]);

            $logStmt = $pdo->prepare('
            INSERT INTO robot_action_logs
            (robot_id, user_id, command_id, action_name, status, details)
            VALUES (?, NULL, ?, ?, "failed", ?)
            ');
            $logStmt->execute([
                $robotId,
                $commandId,
                $actionName,
                'Erreur à l’étape ' . (int)$position['step_order']
            ]);

            robot_json_response([
                'ok' => false,
                'error' => 'Erreur pendant l’exécution sur ESP32.',
                'failed_step' => (int)$position['step_order'],
                                'esp32_response' => $response,
                                'executed_steps' => $executedSteps,
            ], 500);
        }
    }

    // 6. Marquer la commande comme terminée
    $doneStmt = $pdo->prepare('
    UPDATE robot_commands
    SET status = "completed", executed_at = NOW(), details = ?
    WHERE id = ?
    ');
    $doneStmt->execute([
        'Action exécutée avec succès : ' . $actionName,
        $commandId
    ]);

    $logStmt = $pdo->prepare('
    INSERT INTO robot_action_logs
    (robot_id, user_id, command_id, action_name, status, details)
    VALUES (?, NULL, ?, ?, "completed", ?)
    ');
    $logStmt->execute([
        $robotId,
        $commandId,
        $actionName,
        'Action exécutée depuis la BDD vers ESP32.'
    ]);

    robot_json_response([
        'ok' => true,
        'message' => 'Action exécutée avec succès.',
        'robot_id' => $robotId,
        'robot_name' => $robot['robot_name'],
        'action_name' => $actionName,
        'steps_count' => count($positions),
                        'executed_steps' => $executedSteps,
    ]);

} catch (Throwable $e) {
    robot_json_response([
        'ok' => false,
        'error' => 'Erreur serveur PHP.',
        'details' => $e->getMessage(),
    ], 500);
}
