<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilise POST.'
    ], 405);
}

$data = getRequestData();

$username = trim((string)($data['username'] ?? ''));
$password = (string)($data['password'] ?? '');

if ($username === '' || $password === '') {
    jsonResponse([
        'success' => false,
        'message' => 'Username et password obligatoires.'
    ], 400);
}

$pdo = getPDO();

$stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

$success = false;
$userId = null;
$role = null;

if ($user) {
    $inputHash = hash('sha256', $password);

    if (hash_equals($user['password_hash'], $inputHash)) {
        $success = true;
        $userId = (int)$user['id'];
        $role = $user['role'];

        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $role;
    }
}

$logStmt = $pdo->prepare('
    INSERT INTO connection_logs (user_id, username, success, ip_address)
    VALUES (?, ?, ?, ?)
');
$logStmt->execute([
    $userId,
    $username,
    $success ? 1 : 0,
    getClientIp()
]);

if (!$success) {
    jsonResponse([
        'success' => false,
        'message' => 'Identifiants invalides.'
    ], 401);
}

jsonResponse([
    'success' => true,
    'message' => 'Connexion réussie.',
    'user' => [
        'id' => $userId,
        'username' => $username,
        'role' => $role
    ]
]);
