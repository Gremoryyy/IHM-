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

$commandId = (int)($data['command_id'] ?? 0);
$status = trim((string)($data['status'] ?? ''));
$details = trim((string)($data['details'] ?? ''));

$allowedStatuses = ['pending', 'in_progress', 'completed', 'error', 'cancelled'];

if ($commandId <= 0 || !in_array($status, $allowedStatuses, true)) {
    jsonResponse([
        'success' => false,
        'message' => 'command_id ou status invalide.'
    ], 400);
}

$pdo = getPDO();

$stmt = $pdo->prepare('SELECT * FROM robot_commands WHERE id = ? LIMIT 1');
$stmt->execute([$commandId]);
$command = $stmt->fetch();

if (!$command) {
    jsonResponse([
        'success' => false,
        'message' => 'Commande introuvable.'
    ], 404);
}

if (in_array($status, ['completed', 'error', 'cancelled'], true)) {
    $updateStmt = $pdo->prepare('
        UPDATE robot_commands
        SET status = ?, details = ?, executed_at = NOW()
        WHERE id = ?
    ');
    $updateStmt->execute([
        $status,
        $details !== '' ? $details : null,
        $commandId
    ]);
} else {
    $updateStmt = $pdo->prepare('
        UPDATE robot_commands
        SET status = ?, details = ?
        WHERE id = ?
    ');
    $updateStmt->execute([
        $status,
        $details !== '' ? $details : null,
        $commandId
    ]);
}

if ($status === 'completed') {
    if ($command['command_type'] === 'start') {
        $robotStatus = 'active';
    } elseif ($command['command_type'] === 'stop') {
        $robotStatus = 'inactive';
    } else {
        $robotStatus = null;
    }

    if ($robotStatus !== null) {
        $robotStmt = $pdo->prepare('UPDATE robots SET status = ? WHERE id = ?');
        $robotStmt->execute([$robotStatus, (int)$command['robot_id']]);
    }
}

if ($status === 'error') {
    $robotStmt = $pdo->prepare('UPDATE robots SET status = "error" WHERE id = ?');
    $robotStmt->execute([(int)$command['robot_id']]);
}

$logStmt = $pdo->prepare('
    INSERT INTO robot_action_logs
    (robot_id, user_id, action_type, commande, box_number, status, details)
    VALUES (?, ?, ?, ?, ?, ?, ?)
');
$logStmt->execute([
    (int)$command['robot_id'],
    $command['user_id'] !== null ? (int)$command['user_id'] : null,
    'command_status_update',
    $command['command_type'],
    $command['box_number'] !== null ? (int)$command['box_number'] : null,
    $status,
    $details !== '' ? $details : null
]);

jsonResponse([
    'success' => true,
    'message' => 'Statut de commande mis à jour.'
]);
