<?php
declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/store.php';

ensure_session_started($CONFIG);
portal_require_auth($CONFIG);

$logs = robot_fetch_action_logs($CONFIG, 40);
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>IHM — Historique des actions</title>
    <link rel="stylesheet" href="/assets/style.css" />
  </head>
  <body>
    <div class="wrap" style="align-items: stretch;">
      <div class="card">
        <div class="topbar">
          <div class="brand">
            <strong>Historique des actions</strong>
            <span class="muted">Les dernières commandes et changements d'état enregistrés</span>
          </div>
          <div class="row">
            <a class="btn" href="/dashboard.php">Dashboard</a>
            <a class="btn" href="/sensors.php">Capteurs</a>
            <a class="btn danger" href="/logout.php">Déconnexion</a>
          </div>
        </div>

        <div class="content">
          <div class="grid">
            <section class="panel" style="grid-column: span 12;">
              <h2>Dernières actions</h2>
              <p class="muted" style="margin-top:0;">Cette page te permet de voir rapidement ce qui a été demandé aux robots et quand.</p>

              <div class="history-list">
                <?php foreach ($logs as $log): ?>
                  <article class="history-item">
                    <div class="history-head">
                      <strong><?php echo htmlspecialchars((string)$log['robot_name'], ENT_QUOTES); ?></strong>
                      <span class="badge"><?php echo htmlspecialchars((string)$log['action_time'], ENT_QUOTES); ?></span>
                    </div>
                    <div class="history-meta">
                      <span class="badge">Action: <?php echo htmlspecialchars((string)$log['action_type'], ENT_QUOTES); ?></span>
                      <span class="badge">Commande: <?php echo htmlspecialchars((string)($log['commande'] ?? '—'), ENT_QUOTES); ?></span>
                      <span class="badge">Statut: <?php echo htmlspecialchars((string)$log['status'], ENT_QUOTES); ?></span>
                      <span class="badge">Boîte: <?php echo htmlspecialchars((string)($log['box_number'] ?? '—'), ENT_QUOTES); ?></span>
                    </div>
                    <p class="muted history-details"><?php echo htmlspecialchars((string)($log['details'] ?? 'Aucun détail'), ENT_QUOTES); ?></p>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
