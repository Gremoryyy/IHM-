<?php
declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/store.php';

ensure_session_started($CONFIG);
portal_require_auth($CONFIG);

$assignmentOptions = robot_assignment_options();
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>IHM — Supervision robots</title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>" />
    <link rel="stylesheet" href="/assets/style.css" />
  </head>
  <body>
    <div class="wrap" style="align-items: stretch;">
      <div class="card">
        <div class="topbar">
          <div class="brand">
            <strong>IHM — Supervision des 3 robots</strong>
            <span class="muted">Prototype UI (boutons non connectés aux robots pour l’instant)</span>
          </div>
          <div class="row">
            <a class="btn" href="/">Portail</a>
            <a class="btn danger" href="/logout.php">Déconnexion</a>
          </div>
        </div>

        <div class="content">
          <div class="grid">
            <div class="panel" style="grid-column: span 12;">
              <h2>Contrôles globaux</h2>
              <div class="row">
                <button class="btn primary" type="button" data-global-action="launch-all">Lancement robots collaboratifs</button>
                <span class="badge"><span class="dot good"></span> Session OK</span>
                <span class="badge" data-global-summary>Chargement de l'etat...</span>
              </div>
              <p class="muted" style="margin:10px 0 0 0;" data-global-feedback>
                Les boutons pilotent maintenant l'etat IHM des 3 robots et renvoient les positions angulaires memorisees.
              </p>
            </div>

            <?php
              $robots = [
                ['id' => 1, 'name' => 'Robot 1'],
                ['id' => 2, 'name' => 'Robot 2'],
                ['id' => 3, 'name' => 'Robot 3'],
              ];
              foreach ($robots as $robot):
            ?>
              <div class="panel" style="grid-column: span 4;" data-robot-card data-robot-id="<?php echo (int)$robot['id']; ?>">
                <div class="row" style="justify-content: space-between;">
                  <h3 style="margin:0;"><?php echo htmlspecialchars($robot['name'], ENT_QUOTES); ?></h3>
                  <span class="badge"><span class="dot" data-dot></span><span data-status>—</span></span>
                </div>

                <div style="margin-top: 12px;" class="row">
                  <button class="btn" type="button" data-action="toggle-active">Actif/Inactif</button>
                  <button class="btn primary" type="button" data-action="start-stop">Démarrer</button>
                </div>

                <div style="margin-top: 12px;">
                  <div class="muted" style="font-size:12px;">Affectation de position</div>
                  <div style="margin-top:6px;">
                    <label for="assignment-<?php echo (int)$robot['id']; ?>">Position de lancement</label>
                    <select id="assignment-<?php echo (int)$robot['id']; ?>" data-action="assignment">
                      <?php foreach ($assignmentOptions as $key => $option): ?>
                        <option value="<?php echo htmlspecialchars($key, ENT_QUOTES); ?>">
                          <?php echo htmlspecialchars((string)$option['label'], ENT_QUOTES); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div style="margin-top: 12px;">
                  <div class="muted" style="font-size:12px;">Retour des positions angulaires</div>
                  <div class="angles-grid" style="margin-top:6px;">
                    <span class="badge">S6: <strong data-angle="servo_6">—</strong></span>
                    <span class="badge">S7: <strong data-angle="servo_7">—</strong></span>
                    <span class="badge">S8: <strong data-angle="servo_8">—</strong></span>
                    <span class="badge">S9: <strong data-angle="servo_9">—</strong></span>
                    <span class="badge">S10: <strong data-angle="servo_10">—</strong></span>
                    <span class="badge">S11: <strong data-angle="servo_11">—</strong></span>
                  </div>
                </div>

                <div style="margin-top: 12px;">
                  <div class="muted" style="font-size:12px;">Affectation / statut</div>
                  <div class="row" style="margin-top:6px;">
                    <span class="badge">Mode: <span data-mode>manuel</span></span>
                    <span class="badge">Dernier log: <span data-log>—</span></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="footer">
          IHM2 • PHP/HTML • Portail d’accès (session + CGU + code optionnel)
        </div>
      </div>
    </div>

    <script src="/assets/app.js"></script>
  </body>
</html>
