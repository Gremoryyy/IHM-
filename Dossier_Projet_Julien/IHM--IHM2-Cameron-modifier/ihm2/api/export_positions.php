<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/database.php';
require __DIR__ . '/../lib/esp_security.php';


header('Content-Type: application/json; charset=utf-8');

require_esp_token($CONFIG);

$robotId = (int)($_GET['robot_id'] ?? 0);

if ($robotId <= 0) {
    robot_json_response([
        'ok' => false,
        'error' => 'robot_id manquant ou invalide.'
    ], 400);
}

$movementNames = [
    'mouvement1',
    'mouvement2',
    'mouvement3',
    'mouvement4',
    'mouvement5',
    'mouvement6',
    'mouvement7',
];

try {
    $pdo = robot_get_pdo($CONFIG);

    $stmt = $pdo->prepare('
        SELECT
            step_order,
            servo_base_angle,
            servo_shoulder_angle,
            servo_elbow_angle,
            servo_wrist_angle,
            servo_rotate_angle,
            servo_grip_angle
        FROM robot_positions
        WHERE robot_id = ?
          AND action_name = ?
        ORDER BY step_order ASC
    ');

    $movements = [];

    foreach ($movementNames as $movementName) {
        $stmt->execute([$robotId, $movementName]);
        $rows = $stmt->fetchAll();

        if (!$rows) {
            robot_json_response([
                'ok' => false,
                'error' => "Aucune position trouvee pour {$movementName}.",
                'robot_id' => $robotId
            ], 404);
        }

        $movements[$movementName] = [];

        foreach ($rows as $row) {
            // Les valeurs sont renvoyees dans le meme ordre que les tableaux du code ESP32 :
            // [servo_0, servo_1, servo_2, servo_3, servo_4, servo_5]
            // Dans notre BDD actuelle, les colonnes servo_base_angle -> servo_grip_angle stockent deja cet ordre.
            $movements[$movementName][] = [
                (int)$row['servo_base_angle'],
                (int)$row['servo_shoulder_angle'],
                (int)$row['servo_elbow_angle'],
                (int)$row['servo_wrist_angle'],
                (int)$row['servo_rotate_angle'],
                (int)$row['servo_grip_angle'],
            ];
        }
    }

    robot_json_response([
        'ok' => true,
        'robot_id' => $robotId,
        'movements' => $movements,
    ]);

} catch (Throwable $e) {
    robot_json_response([
        'ok' => false,
        'error' => 'Erreur serveur.',
        'details' => $e->getMessage(),
    ], 500);
}
