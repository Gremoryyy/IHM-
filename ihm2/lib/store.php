<?php
declare(strict_types=1);

function robot_data_dir(): string
{
    return __DIR__ . '/../data';
}

function robot_state_file(): string
{
    return robot_data_dir() . '/robots.json';
}

/**
 * @return array<string, mixed>
 */
function robot_default_state(): array
{
    return [
        'updated_at' => gmdate('c'),
        'robots' => [
            robot_default_robot(1, 'Robot 1', 'position_1'),
            robot_default_robot(2, 'Robot 2', 'position_2'),
            robot_default_robot(3, 'Robot 3', 'position_3'),
        ],
    ];
}

/**
 * @return array<string, mixed>
 */
function robot_default_robot(int $id, string $name, string $assignment): array
{
    return [
        'id' => $id,
        'name' => $name,
        'active' => true,
        'running' => false,
        'assignment' => $assignment,
        'status' => 'Pret',
        'last_log' => 'Initialisation',
        'current_angles' => robot_angles_for_assignment($assignment, false),
    ];
}

/**
 * @return array<string, int>
 */
function robot_angles_for_assignment(string $assignment, bool $running): array
{
    $home = [
        'servo_6' => 40,
        'servo_7' => 130,
        'servo_8' => 180,
        'servo_9' => 80,
        'servo_10' => 0,
        'servo_11' => 120,
    ];

    $profiles = [
        'position_1' => [
            'servo_6' => 160,
            'servo_7' => 60,
            'servo_8' => 170,
            'servo_9' => 80,
            'servo_10' => 40,
            'servo_11' => 150,
        ],
        'position_2' => [
            'servo_6' => 110,
            'servo_7' => 60,
            'servo_8' => 170,
            'servo_9' => 80,
            'servo_10' => 40,
            'servo_11' => 150,
        ],
        'position_3' => [
            'servo_6' => 85,
            'servo_7' => 60,
            'servo_8' => 170,
            'servo_9' => 80,
            'servo_10' => 40,
            'servo_11' => 150,
        ],
    ];

    if (!$running) {
        return $home;
    }

    return $profiles[$assignment] ?? $home;
}

/**
 * @return array<string, mixed>
 */
function robot_assignment_options(): array
{
    return [
        'position_1' => ['label' => 'Position 1', 'servo_6_start' => 160],
        'position_2' => ['label' => 'Position 2', 'servo_6_start' => 110],
        'position_3' => ['label' => 'Position 3', 'servo_6_start' => 85],
    ];
}

/**
 * @return array<string, mixed>
 */
function robot_load_state(): array
{
    $dir = robot_data_dir();
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $file = robot_state_file();
    if (!is_file($file)) {
        $state = robot_default_state();
        file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $state;
    }

    $raw = file_get_contents($file);
    $decoded = json_decode($raw ?: '', true);
    if (!is_array($decoded) || !isset($decoded['robots']) || !is_array($decoded['robots'])) {
        $state = robot_default_state();
        file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $state;
    }

    return $decoded;
}

/**
 * @param array<string, mixed> $state
 */
function robot_save_state(array $state): void
{
    $state['updated_at'] = gmdate('c');
    file_put_contents(robot_state_file(), json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * @param array<string, mixed> $state
 * @return array<string, mixed>|null
 */
function robot_find_by_id(array &$state, int $robotId): ?array
{
    if (!isset($state['robots']) || !is_array($state['robots'])) {
        return null;
    }

    foreach ($state['robots'] as $index => $robot) {
        if ((int)($robot['id'] ?? 0) === $robotId) {
            return $state['robots'][$index];
        }
    }

    return null;
}
