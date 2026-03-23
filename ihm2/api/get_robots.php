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

try {
    $pdo = robot_get_pdo($CONFIG);
    $stmt = $pdo->query('
        SELECT id, robot_name, esp32_role, status, created_at
        FROM robots
        ORDER BY id ASC
    ');

    robot_json_response([
        'success' => true,
        'count' => $stmt->rowCount(),
        'robots' => $stmt->fetchAll(),
    ]);
} catch (Throwable $e) {
    robot_json_response([
        'success' => false,
        'message' => 'Erreur SQL.',
        'error' => $e->getMessage(),
    ], 500);
}
