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

try {
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
        jsonResponse([
            'success' => true,
            'command' => null
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

    jsonResponse([
        'success' => true,
        'command' => $command
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    jsonResponse([
        'success' => false,
        'message' => 'Erreur SQL.',
        'error' => $e->getMessage()
    ], 500);
}
