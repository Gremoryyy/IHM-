<?php
declare(strict_types=1);

/**
 * Convention de session :
 * - portal_auth = true/false
 * - portal_auth_at = timestamp (time())
 */

function portal_is_authed(array $config): bool
{
    if (empty($_SESSION['portal_auth']) || $_SESSION['portal_auth'] !== true) {
        return false;
    }

    $ttlMinutes = (int)($config['SESSION_TTL_MINUTES'] ?? 240);
    $authAt = (int)($_SESSION['portal_auth_at'] ?? 0);
    if ($authAt <= 0) {
        return false;
    }

    return (time() - $authAt) <= ($ttlMinutes * 60);
}

function portal_require_auth(array $config): void
{
    if (!portal_is_authed($config)) {
        redirect_to('/?next=' . rawurlencode('/dashboard.php'));
    }
}

function portal_login(array $config, bool $acceptedTerms, string $code): array
{
    if (!$acceptedTerms) {
        return ['ok' => false, 'error' => 'Tu dois accepter les conditions d’utilisation.'];
    }

    $requiredCode = (string)($config['ACCESS_CODE'] ?? '');
    if ($requiredCode !== '') {
        if (!hash_equals($requiredCode, $code)) {
            return ['ok' => false, 'error' => 'Code d’accès incorrect.'];
        }
    }

    session_regenerate_id(true);
    $_SESSION['portal_auth'] = true;
    $_SESSION['portal_auth_at'] = time();
    $_SESSION['portal_terms_accepted'] = true;

    return ['ok' => true, 'error' => null];
}

function portal_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
    }

    session_destroy();
}
