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
$commande = trim((string)($_GET['commande'] ?? ''));
$boxNumber = isset($_GET['box_number']) && $_GET['box_number'] !== '' ? (int)$_GET['box_number'] : null;

if ($robotId <= 0 || $commande === '') {
    jsonResponse([
        'success' => false,
        'message' => 'robot_id et commande sont obligatoires.'
    ], 400);
}

$pdo = getPDO();

$sql = '
    SELECT
        id,
        robot_id,
        commande,
        box_number,
        step_order,
        servo_6_angle,
        servo_7_angle,
        servo_8_angle,
        servo_9_angle,
        servo_10_angle,
        servo_11_angle,
        speed_ms_per_degree,
        commentaire
    FROM robot_positions
    WHERE robot_id = ?
      AND commande = ?
';

$params = [$robotId, $commande];

if ($boxNumber !== null) {
    $sql .= ' AND box_number = ?';
    $params[] = $boxNumber;
} else {
    $sql .= ' AND box_number IS NULL';
}

$sql .= ' ORDER BY step_order ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$positions = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'count' => count($positions),
    'positions' => $positions
]);
