<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilise GET.'
    ], 405);
}

$pdo = getPDO();

$stmt = $pdo->query('
    SELECT id, robot_name, esp32_role, status, created_at
    FROM robots
    ORDER BY id ASC
');
$robots = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'count' => count($robots),
    'robots' => $robots
]);
