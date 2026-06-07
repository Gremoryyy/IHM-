<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/database.php';
require __DIR__ . '/../lib/store.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    robot_json_response([
        'success' => false,
        'message' => 'Methode non autorisee. Utilise GET.',
    ], 405);
}

$robotId = isset($_GET['robot_id']) ? (int)$_GET['robot_id'] : 0;
$commande = trim((string)($_GET['commande'] ?? ''));
$boxNumber = isset($_GET['box_number']) && $_GET['box_number'] !== '' ? (int)$_GET['box_number'] : null;

if ($robotId <= 0 || $commande === '') {
    robot_json_response([
        'success' => false,
        'message' => 'robot_id et commande sont obligatoires.',
    ], 400);
}

try {
    $pdo = robot_get_pdo($CONFIG);
    robot_ensure_position_templates($pdo);
    $positions = robot_fetch_positions($pdo, $robotId, $commande, $boxNumber);

    robot_json_response([
        'success' => true,
        'count' => count($positions),
        'positions' => $positions,
    ]);
} catch (Throwable $e) {
    robot_json_response([
        'success' => false,
        'message' => 'Erreur SQL.',
        'error' => $e->getMessage(),
    ], 500);
}
