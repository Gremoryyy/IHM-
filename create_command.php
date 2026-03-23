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
$userId = isset($data['user_id']) && $data['user_id'] !== '' ? (int)$data['user_id'] : null;
$commandType = trim((string)($data['command_type'] ?? ''));
$targetRobotId = isset($data['target_robot_id']) && $data['target_robot_id'] !== '' ? (int)$data['target_robot_id'] : null;
$boxNumber = isset($data['box_number']) && $data['box_number'] !== '' ? (int)$data['box_number'] : null;
$payload = isset($data['payload']) ? json_encode($data['payload'], JSON_UNESCAPED_UNICODE) : null;
$details = trim((string)($data['details'] ?? ''));

if ($robotId <= 0 || $commandType === '') {
    jsonResponse([
        'success' => false,
        'message' => 'robot_id et command_type sont obligatoires.'
    ], 400);
}

$pdo = getPDO();

$stmt = $pdo->prepare('
    INSERT INTO robot_commands
    (robot_id, user_id, command_type, target_robot_id, box_number, payload, status, details)
    VALUES (?, ?, ?, ?, ?, ?, "pending", ?)
');
$stmt->execute([
    $robotId,
    $userId,
    $commandType,
    $targetRobotId,
    $boxNumber,
    $payload,
    $details !== '' ? $details : null
]);

$commandId = (int)$pdo->lastInsertId();

$logStmt = $pdo->prepare('
    INSERT INTO robot_action_logs
    (robot_id, user_id, action_type, commande, box_number, status, details)
    VALUES (?, ?, ?, ?, ?, ?, ?)
');
$logStmt->execute([
    $robotId,
    $userId,
    'command_created',
    $commandType,
    $boxNumber,
    'pending',
    'Commande créée. ID=' . $commandId
]);

jsonResponse([
    'success' => true,
    'message' => 'Commande créée.',
    'command_id' => $commandId
], 201);
