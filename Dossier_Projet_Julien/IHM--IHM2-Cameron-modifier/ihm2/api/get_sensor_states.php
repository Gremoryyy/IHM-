<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/database.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    robot_json_response([
        'success' => false,
        'message' => 'Methode non autorisee. Utilise GET.',
    ], 405);
}

$robotId = isset($_GET['robot_id']) ? (int)$_GET['robot_id'] : 0;
if ($robotId <= 0) {
    robot_json_response([
        'success' => false,
        'message' => 'robot_id obligatoire.',
    ], 400);
}

try {
    $pdo = robot_get_pdo($CONFIG);
    $stmt = $pdo->prepare('
        SELECT
            s.id,
            s.robot_id,
            r.robot_name,
            s.sensor_pin,
            s.box_number,
            s.box_present,
            s.note,
            s.updated_at
        FROM sensor_states s
        INNER JOIN robots r ON r.id = s.robot_id
        WHERE s.robot_id = ?
        ORDER BY s.box_number ASC
    ');
    $stmt->execute([$robotId]);

    $states = $stmt->fetchAll();
    robot_json_response([
        'success' => true,
        'count' => count($states),
        'sensor_states' => $states,
    ]);
} catch (Throwable $e) {
    robot_json_response([
        'success' => false,
        'message' => 'Erreur SQL.',
        'error' => $e->getMessage(),
    ], 500);
}
