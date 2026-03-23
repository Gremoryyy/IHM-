<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/database.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    robot_json_response([
        'success' => false,
        'message' => 'Methode non autorisee. Utilise POST.',
    ], 405);
}

$data = robot_request_data();
$commandId = (int)($data['command_id'] ?? 0);
$status = trim((string)($data['status'] ?? ''));
$details = trim((string)($data['details'] ?? ''));
$allowedStatuses = ['pending', 'in_progress', 'completed', 'error', 'cancelled'];

if ($commandId <= 0 || !in_array($status, $allowedStatuses, true)) {
    robot_json_response([
        'success' => false,
        'message' => 'command_id ou status invalide.',
    ], 400);
}

try {
    $pdo = robot_get_pdo($CONFIG);

    $stmt = $pdo->prepare('SELECT * FROM robot_commands WHERE id = ? LIMIT 1');
    $stmt->execute([$commandId]);
    $command = $stmt->fetch();

    if (!$command) {
        robot_json_response([
            'success' => false,
            'message' => 'Commande introuvable.',
        ], 404);
    }

    if (in_array($status, ['completed', 'error', 'cancelled'], true)) {
        $updateStmt = $pdo->prepare('
            UPDATE robot_commands
            SET status = ?, details = ?, executed_at = NOW()
            WHERE id = ?
        ');
    } else {
        $updateStmt = $pdo->prepare('
            UPDATE robot_commands
            SET status = ?, details = ?
            WHERE id = ?
        ');
    }

    $updateStmt->execute(
        in_array($status, ['completed', 'error', 'cancelled'], true)
            ? [$status, $details !== '' ? $details : null, $commandId]
            : [$status, $details !== '' ? $details : null, $commandId]
    );

    if ($status === 'completed') {
        $robotStatus = (string)$command['command_type'] === 'stop' ? 'inactive' : 'active';
        $robotStmt = $pdo->prepare('UPDATE robots SET status = ? WHERE id = ?');
        $robotStmt->execute([$robotStatus, (int)$command['robot_id']]);
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
        (string)$command['command_type'],
        $command['box_number'] !== null ? (int)$command['box_number'] : null,
        $status,
        $details !== '' ? $details : null,
    ]);

    robot_json_response([
        'success' => true,
        'message' => 'Statut de commande mis a jour.',
    ]);
} catch (Throwable $e) {
    robot_json_response([
        'success' => false,
        'message' => 'Erreur SQL.',
        'error' => $e->getMessage(),
    ], 500);
}
