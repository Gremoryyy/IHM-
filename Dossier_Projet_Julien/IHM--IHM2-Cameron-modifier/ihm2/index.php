<?php
declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/auth.php';

ensure_session_started($CONFIG);

if (portal_is_authed($CONFIG)) {
    $next = normalize_next_path((string)($_GET['next'] ?? ''), '/dashboard.php');
    redirect_to($next);
}

$error = null;
$next = normalize_next_path((string)($_GET['next'] ?? ''), '/dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_or_fail();

    $accepted = (bool)($_POST['accept_terms'] ?? false);
    $code = trim((string)($_POST['code'] ?? ''));
    $recaptchaToken = trim((string)($_POST['g-recaptcha-response'] ?? ''));

    $result = portal_login($CONFIG, $accepted, $code, $recaptchaToken);
    if (($result['ok'] ?? false) === true) {
        redirect_to($next);
    }
    $error = (string)($result['error'] ?? 'Erreur inconnue.');
}

$needsCode = ((string)($CONFIG['ACCESS_CODE'] ?? '')) !== '';
$recaptchaSiteKey = (string)($CONFIG['RECAPTCHA_SITE_KEY'] ?? '');
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Portail d'accès — IHM Robots</title>
    <?php if ($recaptchaSiteKey !== ''): ?>
      <script src="https://www.google.com/recaptcha/api.js?render=<?php echo rawurlencode($recaptchaSiteKey); ?>"></script>
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/style.css" />
  </head>
  <body>
    <div class="wrap">
      <div class="card">
        <div class="topbar">
          <div class="brand">
            <strong>Portail d'accès — IHM Robots</strong>
            <span>Projet robot pince • IHM2 (PHP/HTML)</span>
          </div>
          <span class="badge">Accès protégé par session</span>
        </div>

        <div class="content">
          <div class="grid">
            <div class="panel" style="grid-column: span 7;">
              <h2>Connexion</h2>
              <p class="muted" style="margin-top:0;">
                Pour accéder à l'IHM, accepte les conditions d'utilisation puis saisis le mot de passe. La vérification anti-bot se fait automatiquement.
              </p>

              <form method="post" action="/?<?php echo http_build_query(['next' => $next]); ?>" data-recaptcha-form>
                <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>" />
                <input type="hidden" name="g-recaptcha-response" value="" data-recaptcha-token />

                <div style="margin-bottom: 12px;">
                  <label for="code">Mot de passe</label>
                  <input id="code" name="code" type="password" autocomplete="current-password" placeholder="Entre le mot de passe d'accès" <?php echo $needsCode ? 'required' : ''; ?> />
                </div>

                <div style="margin-bottom: 12px;">
                  <?php if ($recaptchaSiteKey !== ''): ?>
                    <div class="badge">Sécurité active : Google reCAPTCHA v3</div>
                    <div class="muted" style="margin-top:6px; font-size:12px;">
                      Aucun clic n'est requis. Le contrôle se fait automatiquement à l'envoi.
                    </div>
                  <?php else: ?>
                    <div class="error">reCAPTCHA n'est pas configuré.</div>
                  <?php endif; ?>
                </div>

                <div style="margin: 10px 0 14px 0;">
                  <label style="margin-bottom:0;">
                    <input type="checkbox" name="accept_terms" value="1" required />
                    <span>J’ai lu et j’accepte les conditions d’utilisation.</span>
                  </label>
                </div>

                <div class="row">
                  <button class="btn primary" type="submit">Accéder à l'IHM</button>
                  <a class="btn" href="/dashboard.php">Aller au dashboard</a>
                </div>

                <?php if ($error): ?>
                  <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
                <?php endif; ?>
              </form>
            </div>

            <div class="panel" style="grid-column: span 5;">
              <h2>Conditions d'utilisation (CGU)</h2>
              <details open>
                <summary>Voir les CGU</summary>
                <div class="muted" style="margin-top:10px; line-height:1.5;">
                  <p style="margin-top:0;">
                    Cette IHM permet de superviser des robots (projet pédagogique). Utiliser uniquement en salle, sous supervision.
                  </p>
                  <ul style="margin:0; padding-left:18px;">
                    <li>Ne pas lancer de mouvement sans zone dégagée.</li>
                    <li>Arrêt d'urgence en cas d'anomalie.</li>
                    <li>Ne pas partager le code d’accès publiquement.</li>
                    <li>Les actions peuvent être enregistrées à des fins de projet.</li>
                  </ul>
                </div>
              </details>
            </div>
          </div>
        </div>

        <div class="footer">
          Acces reserve aux utilisateurs autorises.
        </div>
      </div>
    </div>
    <?php if ($recaptchaSiteKey !== ''): ?>
      <script>
        (() => {
          const form = document.querySelector('[data-recaptcha-form]');
          const tokenInput = document.querySelector('[data-recaptcha-token]');
          if (!form || !tokenInput || typeof grecaptcha === 'undefined') {
            return;
          }

          let submitting = false;
          form.addEventListener('submit', (event) => {
            if (submitting) {
              return;
            }

            event.preventDefault();
            grecaptcha.ready(() => {
              grecaptcha.execute('<?php echo htmlspecialchars($recaptchaSiteKey, ENT_QUOTES); ?>', { action: 'portal_login' }).then((token) => {
                tokenInput.value = token;
                submitting = true;
                form.submit();
              }).catch(() => {
                const existingError = form.querySelector('.error');
                if (existingError) {
                  existingError.textContent = 'Impossible de charger reCAPTCHA.';
                  return;
                }

                const errorBox = document.createElement('div');
                errorBox.className = 'error';
                errorBox.textContent = 'Impossible de charger reCAPTCHA.';
                form.appendChild(errorBox);
              });
            });
          });
        })();
      </script>
    <?php endif; ?>
  </body>
</html>
