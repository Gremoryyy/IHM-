<?php
declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/auth.php';

ensure_session_started($CONFIG);
portal_require_auth($CONFIG);
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>IHM — Supervision robots</title>
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
                <button class="btn primary" type="button">Lancement robots collaboratifs</button>
                <span class="badge"><span class="dot good"></span> Session OK</span>
                <span class="badge">À brancher : USB/ESP32/BDD</span>
              </div>
              <p class="muted" style="margin:10px 0 0 0;">
                À faire ensuite : remplacer les boutons par des appels API (PHP → ESP32/Arduino/BDD) et afficher les positions/états réels.
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
              <div class="panel" style="grid-column: span 4;" data-robot-card data-active="1" data-running="0">
                <div class="row" style="justify-content: space-between;">
                  <h3 style="margin:0;"><?php echo htmlspecialchars($robot['name'], ENT_QUOTES); ?></h3>
                  <span class="badge"><span class="dot" data-dot></span><span data-status>—</span></span>
                </div>

                <div style="margin-top: 12px;" class="row">
                  <button class="btn" type="button" data-action="toggle-active">Actif/Inactif</button>
                  <button class="btn primary" type="button" data-action="start-stop">Démarrer</button>
                </div>

                <div style="margin-top: 12px;">
                  <div class="muted" style="font-size:12px;">Positions (exemple)</div>
                  <div class="row" style="margin-top:6px;">
                    <span class="badge">θ1: —</span>
                    <span class="badge">θ2: —</span>
                    <span class="badge">θ3: —</span>
                  </div>
                </div>

                <div style="margin-top: 12px;">
                  <div class="muted" style="font-size:12px;">Affectation / statut</div>
                  <div class="row" style="margin-top:6px;">
                    <span class="badge">Mode: manuel</span>
                    <span class="badge">Dernier log: —</span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="footer">
          IHM web • PHP/HTML • Portail d’accès (session + CGU + code optionnel)
        </div>
      </div>
    </div>

    <script src="/assets/app.js"></script>
  </body>
</html>

