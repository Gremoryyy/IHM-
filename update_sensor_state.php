<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilise POST.'
    ], 405);
}

$data = getRequestData();

$robotId = (int)($data['robot_id'] ?? 0);
$sensorPin = isset($data['sensor_pin']) && $data['sensor_pin'] !== '' ? (int)$data['sensor_pin'] : null;
$boxNumber = isset($data['box_number']) && $data['box_number'] !== '' ? (int)$data['box_number'] : null;
$boxPresent = isset($data['box_present']) ? (int)$data['box_present'] : null;

if ($robotId <= 0 || ($sensorPin === null && $boxNumber === null) || !in_array($boxPresent, [0, 1], true)) {
    jsonResponse([
        'success' => false,
        'message' => 'Paramètres invalides.'
    ], 400);
}

$pdo = getPDO();

if ($sensorPin !== null) {
    $stmt = $pdo->prepare('
        UPDATE sensor_states
        SET box_present = ?, updated_at = NOW()
        WHERE robot_id = ? AND sensor_pin = ?
    ');
    $stmt->execute([$boxPresent, $robotId, $sensorPin]);
} else {
    $stmt = $pdo->prepare('
        UPDATE sensor_states
        SET box_present = ?, updated_at = NOW()
        WHERE robot_id = ? AND box_number = ?
    ');
    $stmt->execute([$boxPresent, $robotId, $boxNumber]);
}

jsonResponse([
    'success' => true,
    'message' => 'État capteur mis à jour.'
]);
