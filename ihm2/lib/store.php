<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * @return array<string, array<string, int|string>>
 */
function robot_assignment_options(): array
{
    return [
        'box_1' => ['label' => 'Boite 1', 'box_number' => 1, 'servo_6_start' => 160],
        'box_2' => ['label' => 'Boite 2', 'box_number' => 2, 'servo_6_start' => 135],
        'box_3' => ['label' => 'Boite 3', 'box_number' => 3, 'servo_6_start' => 110],
        'box_4' => ['label' => 'Boite 4', 'box_number' => 4, 'servo_6_start' => 85],
    ];
}

function robot_assignment_key_from_box(?int $boxNumber): string
{
    $options = robot_assignment_options();
    foreach ($options as $key => $option) {
        if ((int)$option['box_number'] === (int)$boxNumber) {
            return $key;
        }
    }

    return 'box_1';
}

function robot_box_number_from_assignment(string $assignment): int
{
    $options = robot_assignment_options();
    return isset($options[$assignment]) ? (int)$options[$assignment]['box_number'] : 1;
}

/**
 * @return array<string, int>
 */
function robot_default_box_by_robot_id(): array
{
    return [
        '1' => 1,
        '2' => 2,
        '3' => 3,
    ];
}

function robot_default_box_for_robot(int $robotId): int
{
    $defaults = robot_default_box_by_robot_id();
    return $defaults[(string)$robotId] ?? 1;
}

function robot_ensure_position_templates(PDO $pdo): void
{
    $robotsStmt = $pdo->query('SELECT id FROM robots ORDER BY id ASC');
    $robotIds = array_map(static fn (array $row): int => (int)$row['id'], $robotsStmt->fetchAll());

    foreach ($robotIds as $robotId) {
        if ($robotId === 1) {
            continue;
        }

        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM robot_positions WHERE robot_id = ?');
        $countStmt->execute([$robotId]);
        $count = (int)$countStmt->fetchColumn();
        if ($count > 0) {
            continue;
        }

        $insertStmt = $pdo->prepare("
            INSERT INTO robot_positions (
                robot_id,
                commande,
                box_number,
                step_order,
                servo_6_angle,
                servo_7_angle,
                servo_8_angle,
                servo_9_angle,
                servo_10_angle,
                servo_11_angle,
                speed_ms_per_degree,
                commentaire
            )
            SELECT
                ?,
                commande,
                box_number,
                step_order,
                servo_6_angle,
                servo_7_angle,
                servo_8_angle,
                servo_9_angle,
                servo_10_angle,
                servo_11_angle,
                speed_ms_per_degree,
                CONCAT(IFNULL(commentaire, ''), ' [duplique depuis robot_1]')
            FROM robot_positions
            WHERE robot_id = 1
            ORDER BY commande ASC, box_number ASC, step_order ASC
        ");
        $insertStmt->execute([$robotId]);
    }
}

/**
 * @return array<string, mixed>|null
 */
function robot_latest_command(PDO $pdo, int $robotId): ?array
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM robot_commands
        WHERE robot_id = ?
        ORDER BY created_at DESC, id DESC
        LIMIT 1
    ');
    $stmt->execute([$robotId]);
    $row = $stmt->fetch();

    return is_array($row) ? $row : null;
}

/**
 * @return array<string, mixed>|null
 */
function robot_latest_pending_or_progress_command(PDO $pdo, int $robotId): ?array
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM robot_commands
        WHERE robot_id = ?
          AND status IN ("pending", "in_progress")
        ORDER BY created_at DESC, id DESC
        LIMIT 1
    ');
    $stmt->execute([$robotId]);
    $row = $stmt->fetch();

    return is_array($row) ? $row : null;
}

/**
 * @return array<string, mixed>|null
 */
function robot_latest_assignment_command(PDO $pdo, int $robotId): ?array
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM robot_commands
        WHERE robot_id = ?
          AND box_number IS NOT NULL
          AND command_type IN ("assign_box", "charger_boite")
        ORDER BY created_at DESC, id DESC
        LIMIT 1
    ');
    $stmt->execute([$robotId]);
    $row = $stmt->fetch();

    return is_array($row) ? $row : null;
}

/**
 * @return array<string, mixed>|null
 */
function robot_latest_action_log(PDO $pdo, int $robotId): ?array
{
    $stmt = $pdo->prepare('
        SELECT *
        FROM robot_action_logs
        WHERE robot_id = ?
        ORDER BY action_time DESC, id DESC
        LIMIT 1
    ');
    $stmt->execute([$robotId]);
    $row = $stmt->fetch();

    return is_array($row) ? $row : null;
}

/**
 * @return array<string, int>
 */
function robot_angles_from_position_step(PDO $pdo, int $robotId, string $commande, ?int $boxNumber, int $stepOrder): array
{
    $sql = '
        SELECT servo_6_angle, servo_7_angle, servo_8_angle, servo_9_angle, servo_10_angle, servo_11_angle
        FROM robot_positions
        WHERE robot_id = ?
          AND commande = ?
    ';
    $params = [$robotId, $commande];

    if ($boxNumber === null) {
        $sql .= ' AND box_number IS NULL';
    } else {
        $sql .= ' AND box_number = ?';
        $params[] = $boxNumber;
    }

    $sql .= ' AND step_order = ? LIMIT 1';
    $params[] = $stepOrder;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();

    if (!is_array($row)) {
        return [
            'servo_6' => 40,
            'servo_7' => 130,
            'servo_8' => 180,
            'servo_9' => 80,
            'servo_10' => 0,
            'servo_11' => 120,
        ];
    }

    return [
        'servo_6' => (int)$row['servo_6_angle'],
        'servo_7' => (int)$row['servo_7_angle'],
        'servo_8' => (int)$row['servo_8_angle'],
        'servo_9' => (int)$row['servo_9_angle'],
        'servo_10' => (int)$row['servo_10_angle'],
        'servo_11' => (int)$row['servo_11_angle'],
    ];
}

/**
 * @return list<array<string, mixed>>
 */
function robot_fetch_positions(PDO $pdo, int $robotId, string $commande, ?int $boxNumber): array
{
    $sql = '
        SELECT
            id,
            robot_id,
            commande,
            box_number,
            step_order,
            servo_6_angle,
            servo_7_angle,
            servo_8_angle,
            servo_9_angle,
            servo_10_angle,
            servo_11_angle,
            speed_ms_per_degree,
            commentaire
        FROM robot_positions
        WHERE robot_id = ?
          AND commande = ?
    ';
    $params = [$robotId, $commande];

    if ($boxNumber === null) {
        $sql .= ' AND box_number IS NULL';
    } else {
        $sql .= ' AND box_number = ?';
        $params[] = $boxNumber;
    }

    $sql .= ' ORDER BY step_order ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/**
 * @return array<string, mixed>
 */
function robot_load_state(array $config): array
{
    $pdo = robot_get_pdo($config);
    robot_ensure_position_templates($pdo);

    $stmt = $pdo->query('
        SELECT id, robot_name, esp32_role, status, created_at
        FROM robots
        ORDER BY id ASC
    ');

    $robots = [];
    foreach ($stmt->fetchAll() as $row) {
        $robotId = (int)$row['id'];
        $assignmentCommand = robot_latest_assignment_command($pdo, $robotId);
        $boxNumber = $assignmentCommand !== null && $assignmentCommand['box_number'] !== null
            ? (int)$assignmentCommand['box_number']
            : robot_default_box_for_robot($robotId);
        $assignment = robot_assignment_key_from_box($boxNumber);

        $pendingCommand = robot_latest_pending_or_progress_command($pdo, $robotId);
        $latestCommand = robot_latest_command($pdo, $robotId);
        $latestLog = robot_latest_action_log($pdo, $robotId);

        $running = $pendingCommand !== null && (string)$pendingCommand['command_type'] === 'charger_boite';
        $currentAngles = $running
            ? robot_angles_from_position_step($pdo, $robotId, 'charger_boite', $boxNumber, 1)
            : robot_angles_from_position_step($pdo, $robotId, 'home', null, 1);

        $robots[] = [
            'id' => $robotId,
            'name' => (string)$row['robot_name'],
            'esp32_role' => (string)$row['esp32_role'],
            'db_status' => (string)$row['status'],
            'active' => (string)$row['status'] !== 'inactive',
            'running' => $running,
            'assignment' => $assignment,
            'box_number' => $boxNumber,
            'status' => $running ? 'En cours' : ((string)$row['status'] === 'inactive' ? 'Inactif' : 'Pret'),
            'last_log' => (string)($latestLog['details'] ?? $latestCommand['details'] ?? 'Synchronise BDD'),
            'current_angles' => $currentAngles,
        ];
    }

    return [
        'updated_at' => gmdate('c'),
        'robots' => $robots,
    ];
}

function robot_update_active_state(array $config, int $robotId, bool $active, ?int $userId = null): void
{
    $pdo = robot_get_pdo($config);

    $status = $active ? 'active' : 'inactive';
    $stmt = $pdo->prepare('UPDATE robots SET status = ? WHERE id = ?');
    $stmt->execute([$status, $robotId]);

    $logStmt = $pdo->prepare('
        INSERT INTO robot_action_logs
        (robot_id, user_id, action_type, commande, box_number, status, details)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $logStmt->execute([
        $robotId,
        $userId,
        'toggle_active',
        $active ? 'active' : 'inactive',
        null,
        $status,
        $active ? 'Robot active depuis IHM.' : 'Robot desactive depuis IHM.',
    ]);
}

function robot_save_assignment(array $config, int $robotId, int $boxNumber, ?int $userId = null): void
{
    $pdo = robot_get_pdo($config);
    $payload = json_encode([
        'commande' => 'charger_boite',
        'box_number' => $boxNumber,
    ], JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare('
        INSERT INTO robot_commands
        (robot_id, user_id, command_type, target_robot_id, box_number, payload, status, created_at, executed_at, details)
        VALUES (?, ?, "assign_box", NULL, ?, ?, "completed", NOW(), NOW(), ?)
    ');
    $stmt->execute([
        $robotId,
        $userId,
        $boxNumber,
        $payload,
        'Affectation IHM en boite ' . $boxNumber,
    ]);

    $logStmt = $pdo->prepare('
        INSERT INTO robot_action_logs
        (robot_id, user_id, action_type, commande, box_number, status, details)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $logStmt->execute([
        $robotId,
        $userId,
        'assignment_update',
        'assign_box',
        $boxNumber,
        'completed',
        'Affectation mise a jour vers la boite ' . $boxNumber,
    ]);
}

function robot_create_motion_command(array $config, int $robotId, int $boxNumber, ?int $userId = null, string $details = ''): int
{
    $pdo = robot_get_pdo($config);
    $positions = robot_fetch_positions($pdo, $robotId, 'charger_boite', $boxNumber);
    $payload = json_encode([
        'commande' => 'charger_boite',
        'box_number' => $boxNumber,
        'positions' => $positions,
    ], JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare('
        INSERT INTO robot_commands
        (robot_id, user_id, command_type, target_robot_id, box_number, payload, status, details)
        VALUES (?, ?, "charger_boite", NULL, ?, ?, "pending", ?)
    ');
    $stmt->execute([
        $robotId,
        $userId,
        $boxNumber,
        $payload,
        $details !== '' ? $details : 'Commande de chargement vers boite ' . $boxNumber,
    ]);

    $commandId = (int)$pdo->lastInsertId();

    $logStmt = $pdo->prepare('
        INSERT INTO robot_action_logs
        (robot_id, user_id, action_type, commande, box_number, status, details)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $logStmt->execute([
        $robotId,
        $userId,
        'command_created',
        'charger_boite',
        $boxNumber,
        'pending',
        'Commande creee. ID=' . $commandId,
    ]);

    $statusStmt = $pdo->prepare('UPDATE robots SET status = "active" WHERE id = ?');
    $statusStmt->execute([$robotId]);

    return $commandId;
}

function robot_create_stop_command(array $config, int $robotId, ?int $userId = null): int
{
    $pdo = robot_get_pdo($config);
    $stmt = $pdo->prepare('
        INSERT INTO robot_commands
        (robot_id, user_id, command_type, target_robot_id, box_number, payload, status, details)
        VALUES (?, ?, "stop", NULL, NULL, NULL, "pending", ?)
    ');
    $stmt->execute([
        $robotId,
        $userId,
        'Arret demande depuis l IHM',
    ]);

    $commandId = (int)$pdo->lastInsertId();

    $logStmt = $pdo->prepare('
        INSERT INTO robot_action_logs
        (robot_id, user_id, action_type, commande, box_number, status, details)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $logStmt->execute([
        $robotId,
        $userId,
        'command_created',
        'stop',
        null,
        'pending',
        'Commande stop creee. ID=' . $commandId,
    ]);

    return $commandId;
}

/**
 * @return list<array<string, mixed>>
 */
function robot_fetch_action_logs(array $config, int $limit = 30): array
{
    $pdo = robot_get_pdo($config);
    $stmt = $pdo->prepare('
        SELECT
            l.id,
            l.robot_id,
            r.robot_name,
            l.action_type,
            l.commande,
            l.box_number,
            l.status,
            l.details,
            l.action_time
        FROM robot_action_logs l
        INNER JOIN robots r ON r.id = l.robot_id
        ORDER BY l.action_time DESC, l.id DESC
        LIMIT ?
    ');
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * @return list<array<string, mixed>>
 */
function robot_fetch_sensor_states(array $config): array
{
    $pdo = robot_get_pdo($config);
    $stmt = $pdo->query('
        SELECT
            s.id,
            s.robot_id,
            r.robot_name,
            s.sensor_pin,
            s.box_number,
            s.box_present,
            s.note,
            s.updated_at
        FROM sensor_states s
        INNER JOIN robots r ON r.id = s.robot_id
        ORDER BY s.robot_id ASC, s.box_number ASC
    ');

    return $stmt->fetchAll();
}
