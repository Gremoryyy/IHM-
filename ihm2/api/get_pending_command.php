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
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('
        SELECT *
        FROM robot_commands
        WHERE robot_id = ?
          AND status = "pending"
        ORDER BY created_at ASC, id ASC
        LIMIT 1
        FOR UPDATE
    ');
    $stmt->execute([$robotId]);
    $command = $stmt->fetch();

    if (!$command) {
        $pdo->commit();
        robot_json_response([
            'success' => true,
            'command' => null,
        ]);
    }

    $updateStmt = $pdo->prepare('
        UPDATE robot_commands
        SET status = "in_progress"
        WHERE id = ?
    ');
    $updateStmt->execute([(int)$command['id']]);

    $pdo->commit();
    $command['status'] = 'in_progress';

    robot_json_response([
        'success' => true,
        'command' => $command,
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    robot_json_response([
        'success' => false,
        'message' => 'Erreur SQL.',
        'error' => $e->getMessage(),
    ], 500);
}
