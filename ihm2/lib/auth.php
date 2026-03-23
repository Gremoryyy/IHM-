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

function portal_client_ip(): string
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return trim(explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }

    return (string)($_SERVER['REMOTE_ADDR'] ?? '');
}

/**
 * @return array{ok: bool, error: string|null}
 */
function portal_verify_recaptcha(array $config, string $token): array
{
    $secret = (string)($config['RECAPTCHA_SECRET_KEY'] ?? '');
    if ($secret === '') {
        return ['ok' => false, 'error' => 'reCAPTCHA non configuré.'];
    }

    if ($token === '') {
        return ['ok' => false, 'error' => 'Jeton reCAPTCHA manquant.'];
    }

    $postFields = http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => portal_client_ip(),
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $postFields,
            'timeout' => 10,
        ],
    ]);

    $raw = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    if ($raw === false) {
        return ['ok' => false, 'error' => 'Impossible de vérifier reCAPTCHA.'];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || ($decoded['success'] ?? false) !== true) {
        return ['ok' => false, 'error' => 'reCAPTCHA invalide.'];
    }

    if (($decoded['action'] ?? '') !== 'portal_login') {
        return ['ok' => false, 'error' => 'Action reCAPTCHA invalide.'];
    }

    $score = (float)($decoded['score'] ?? 0.0);
    $minScore = (float)($config['RECAPTCHA_MIN_SCORE'] ?? 0.5);
    if ($score < $minScore) {
        return ['ok' => false, 'error' => 'Score reCAPTCHA trop faible.'];
    }

    return ['ok' => true, 'error' => null];
}

function portal_login(array $config, bool $acceptedTerms, string $code, string $recaptchaToken): array
{
    if (!$acceptedTerms) {
        return ['ok' => false, 'error' => 'Tu dois accepter les conditions d’utilisation.'];
    }

    $captcha = portal_verify_recaptcha($config, $recaptchaToken);
    if (!($captcha['ok'] ?? false)) {
        return ['ok' => false, 'error' => (string)($captcha['error'] ?? 'reCAPTCHA invalide.')];
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
