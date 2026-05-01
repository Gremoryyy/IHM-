<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/database.php';

header('Content-Type: application/json; charset=utf-8');

$robotId = (int)($_GET['robot_id'] ?? 0);

if ($robotId <= 0) {
    robot_json_response([
        'ok' => false,
        'error' => 'robot_id manquant ou invalide.'
    ], 400);
}

$mapping = [
    'mouvement1' => 'home_pince_ouverte',
'mouvement2' => 'home_pince_fermee',
'mouvement3' => 'aller_prise_boite_palette1',
'mouvement4' => 'prendre_boite_palette1',
'mouvement5' => 'transfert_palette1_vers_palette2',
'mouvement6' => 'deposer_boite_palette2',
'mouvement7' => 'retour_home_pince_ouverte',
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

    foreach ($mapping as $movementName => $actionName) {
        $stmt->execute([$robotId, $actionName]);
        $rows = $stmt->fetchAll();

        if (!$rows) {
            robot_json_response([
                'ok' => false,
                'error' => "Aucune position trouvée pour {$movementName} / {$actionName}.",
                'robot_id' => $robotId
            ], 404);
        }

        $movements[$movementName] = [];

        foreach ($rows as $row) {
            // Conversion BDD -> ordre du code prof
            // BDD : base, epaule, coude, poignet, rotation, pince
            // Code prof : pince, poignet, rotation, coude, epaule, base
            $movements[$movementName][] = [
                (int)$row['servo_grip_angle'],
                (int)$row['servo_wrist_angle'],
                (int)$row['servo_rotate_angle'],
                (int)$row['servo_elbow_angle'],
                (int)$row['servo_shoulder_angle'],
                (int)$row['servo_base_angle'],
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
