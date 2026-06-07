<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$robotsConfig = [
    1 => ['name' => 'Robot A', 'ip' => '192.168.1.201'],
2 => ['name' => 'Robot B', 'ip' => '192.168.1.202'],
3 => ['name' => 'Robot C', 'ip' => null], // simulé/offline pour l’instant
];

function fetch_robot_status(?string $ip, int $id, string $name): array
{
    if ($ip === null) {
        return [
            'id' => $id,
            'name' => $name,
            'active' => false,
            'running' => false,
            'status' => 'offline',
            'last_log' => 'Non configure pour le moment.',
        ];
    }

    $url = "http://{$ip}/status";

    $context = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => 1.5,
        ]
    ]);

    $raw = @file_get_contents($url, false, $context);

    if ($raw === false) {
        return [
            'id' => $id,
            'name' => $name,
            'active' => false,
            'running' => false,
            'status' => 'error',
            'last_log' => "ESP32 inaccessible sur {$ip}",
        ];
    }

    $json = json_decode($raw, true);

    if (!is_array($json) || !isset($json['robot'])) {
        return [
            'id' => $id,
            'name' => $name,
            'active' => false,
            'running' => false,
            'status' => 'error',
            'last_log' => "Reponse invalide de {$ip}",
        ];
    }

    $robot = $json['robot'];

    return [
        'id' => $id,
        'name' => $name,
        'active' => (bool)($robot['active'] ?? false),
        'running' => (bool)($robot['running'] ?? false),
        'status' => (string)($robot['status'] ?? 'unknown'),
        'last_log' => (string)($robot['last_log'] ?? 'OK'),
    ];
}

$result = [];
foreach ($robotsConfig as $id => $cfg) {
    $result[] = fetch_robot_status($cfg['ip'], $id, $cfg['name']);
}

echo json_encode([
    'ok' => true,
    'robots' => $result,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
