<?php
declare(strict_types=1);

/** @var array<string, mixed> $CONFIG */
$CONFIG = require __DIR__ . '/../config.php';

function ensure_session_started(array $config): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) === '443');

    session_name((string)($config['SESSION_NAME'] ?? 'robot_ihm2_session'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function redirect_to(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function normalize_next_path(string $next, string $fallback = '/dashboard.php'): string
{
    $next = trim($next);
    if ($next === '') {
        return $fallback;
    }
    if ($next[0] !== '/') {
        return $fallback;
    }
    if (strpos($next, '://') !== false) {
        return $fallback;
    }
    return $next;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }
    return (string)$_SESSION['_csrf'];
}

function csrf_verify_or_fail(): void
{
    $posted = (string)($_POST['_csrf'] ?? '');
    $session = (string)($_SESSION['_csrf'] ?? '');
    if ($posted === '' || $session === '' || !hash_equals($session, $posted)) {
        http_response_code(400);
        echo 'Requête invalide (CSRF).';
        exit;
    }
}

/**
 * @param array<string, mixed> $payload
 */
function csrf_verify_request_or_fail(array $payload = []): void
{
    $posted = (string)($payload['_csrf'] ?? $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $session = (string)($_SESSION['_csrf'] ?? '');
    if ($posted === '' || $session === '' || !hash_equals($session, $posted)) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Requete invalide (CSRF).'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
