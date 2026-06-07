<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/database.php';
require __DIR__ . '/../lib/store.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    robot_json_response([
        'success' => false,
        'message' => 'Methode non autorisee. Utilise POST.',
    ], 405);
}

$data = robot_request_data();
$robotId = (int)($data['robot_id'] ?? 0);
$commandType = trim((string)($data['command_type'] ?? ''));
$userId = isset($data['user_id']) && $data['user_id'] !== '' ? (int)$data['user_id'] : null;
$boxNumber = isset($data['box_number']) && $data['box_number'] !== '' ? (int)$data['box_number'] : null;
$details = trim((string)($data['details'] ?? ''));

if ($robotId <= 0 || $commandType === '') {
    robot_json_response([
        'success' => false,
        'message' => 'robot_id et command_type sont obligatoires.',
    ], 400);
}

try {
    if ($commandType === 'charger_boite' && $boxNumber !== null) {
        $commandId = robot_create_motion_command($CONFIG, $robotId, $boxNumber, $userId, $details);
    } elseif ($commandType === 'stop') {
        $commandId = robot_create_stop_command($CONFIG, $robotId, $userId);
    } elseif ($commandType === 'assign_box' && $boxNumber !== null) {
        robot_save_assignment($CONFIG, $robotId, $boxNumber, $userId);
        $commandId = 0;
    } else {
        robot_json_response([
            'success' => false,
            'message' => 'Commande non prise en charge.',
        ], 422);
    }

    robot_json_response([
        'success' => true,
        'message' => 'Commande creee.',
        'command_id' => $commandId,
    ], 201);
} catch (Throwable $e) {
    robot_json_response([
        'success' => false,
        'message' => 'Erreur SQL.',
        'error' => $e->getMessage(),
    ], 500);
}
