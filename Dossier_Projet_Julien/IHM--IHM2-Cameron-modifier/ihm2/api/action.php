<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Methode non autorisee'], JSON_UNESCAPED_UNICODE);
    exit;
}

$payload = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($payload)) {
    $payload = [];
}

$action = (string)($payload['action'] ?? '');
$robotId = (int)($payload['robot_id'] ?? 0);

$robotsConfig = [
    1 => ['name' => 'Robot A', 'ip' => '192.168.1.201'],
2 => ['name' => 'Robot B', 'ip' => '192.168.1.202'],
3 => ['name' => 'Robot C', 'ip' => null],
];

if (!isset($robotsConfig[$robotId])) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Robot invalide'], JSON_UNESCAPED_UNICODE);
    exit;
}

$ip = $robotsConfig[$robotId]['ip'];

if ($ip === null) {
    echo json_encode([
        'ok' => false,
        'error' => 'Robot non configure pour les tests',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function get_robot_status(string $ip): ?array
{
    $raw = @file_get_contents("http://{$ip}/status");
    if ($raw === false) {
        return null;
    }

    $json = json_decode($raw, true);
    if (!is_array($json) || !isset($json['robot'])) {
        return null;
    }

    return $json['robot'];
}

function call_robot(string $ip, string $path): array
{
    $url = "http://{$ip}{$path}";
    $raw = @file_get_contents($url);

    if ($raw === false) {
        return [
            'ok' => false,
            'error' => "Echec appel {$url}",
        ];
    }

    $json = json_decode($raw, true);
    if (!is_array($json)) {
        return [
            'ok' => false,
            'error' => "Reponse invalide {$url}",
        ];
    }

    return $json;
}

$status = get_robot_status($ip);
if ($status === null) {
    echo json_encode([
        'ok' => false,
        'error' => 'Impossible de lire le statut du robot',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

switch ($action) {
    case 'toggle_active':
        $path = !empty($status['active']) ? '/deactivate' : '/activate';
        $response = call_robot($ip, $path);
        break;

    case 'toggle_running':
        $path = !empty($status['running']) ? '/stop' : '/start';
        $response = call_robot($ip, $path);
        break;

    default:
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Action inconnue'], JSON_UNESCAPED_UNICODE);
        exit;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
