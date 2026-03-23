<?php
declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/store.php';

ensure_session_started($CONFIG);
portal_require_auth($CONFIG);

$sensorStates = robot_fetch_sensor_states($CONFIG);
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>IHM — Capteurs et boîtes</title>
    <link rel="stylesheet" href="/assets/style.css" />
  </head>
  <body>
    <div class="wrap" style="align-items: stretch;">
      <div class="card">
        <div class="topbar">
          <div class="brand">
            <strong>Capteurs et boîtes</strong>
            <span class="muted">Vue simple de présence des boîtes et état des capteurs</span>
          </div>
          <div class="row">
            <a class="btn" href="/dashboard.php">Dashboard</a>
            <a class="btn" href="/history.php">Historique</a>
            <a class="btn danger" href="/logout.php">Déconnexion</a>
          </div>
        </div>

        <div class="content">
          <div class="grid">
            <?php foreach ($sensorStates as $sensor): ?>
              <article class="panel sensor-panel" style="grid-column: span 4;">
                <div class="robot-card-head">
                  <div>
                    <span class="robot-kicker"><?php echo htmlspecialchars((string)$sensor['robot_name'], ENT_QUOTES); ?></span>
                    <h3 style="margin:4px 0 0 0;">Boîte <?php echo (int)$sensor['box_number']; ?></h3>
                  </div>
                  <span class="badge">
                    <span class="dot <?php echo ((int)$sensor['box_present'] === 1) ? 'good' : 'bad'; ?>"></span>
                    <?php echo ((int)$sensor['box_present'] === 1) ? 'Présente' : 'Absente'; ?>
                  </span>
                </div>

                <div class="sensor-stats">
                  <span class="badge">Capteur pin: <?php echo (int)$sensor['sensor_pin']; ?></span>
                  <span class="badge">Maj: <?php echo htmlspecialchars((string)$sensor['updated_at'], ENT_QUOTES); ?></span>
                </div>

                <p class="muted history-details"><?php echo htmlspecialchars((string)($sensor['note'] ?? 'Pas de note'), ENT_QUOTES); ?></p>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
