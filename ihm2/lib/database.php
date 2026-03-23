<?php
declare(strict_types=1);

function robot_get_pdo(array $config): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        (string)($config['DB_HOST'] ?? 'localhost'),
        (int)($config['DB_PORT'] ?? 3306),
        (string)($config['DB_NAME'] ?? 'robot6ddl')
    );

    $pdo = new PDO($dsn, (string)($config['DB_USER'] ?? 'root'), (string)($config['DB_PASS'] ?? ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function robot_json_response(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function robot_request_data(): array
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        return $_GET;
    }

    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw ?: '', true);
    if (is_array($decoded)) {
        return $decoded;
    }

    return $_POST;
}
