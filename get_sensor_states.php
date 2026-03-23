<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilise GET.'
    ], 405);
}

$robotId = isset($_GET['robot_id']) ? (int)$_GET['robot_id'] : 0;

if ($robotId <= 0) {
    jsonResponse([
        'success' => false,
        'message' => 'robot_id obligatoire.'
    ], 400);
}

$pdo = getPDO();

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

jsonResponse([
    'success' => true,
    'count' => count($states),
    'sensor_states' => $states
]);
