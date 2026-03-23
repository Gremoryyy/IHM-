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
            <span class="muted">Vue simplifiée pour piloter sans se noyer dans les détails</span>
          </div>
          <div class="row">
            <a class="btn" href="/history.php">Historique</a>
            <a class="btn" href="/sensors.php">Capteurs</a>
            <a class="btn" href="/">Portail</a>
            <a class="btn danger" href="/logout.php">Déconnexion</a>
          </div>
        </div>

        <div class="content">
          <div class="grid">
            <section class="hero-panel" style="grid-column: span 12;">
              <div class="hero-copy">
                <span class="eyebrow">Vue d'ensemble</span>
                <h1>Supervision simplifiée des 3 robots</h1>
                <p class="muted hero-text">
                  Les actions importantes sont visibles tout de suite. Les angles et détails techniques restent disponibles plus bas, seulement si tu en as besoin.
                </p>
              </div>
              <div class="hero-actions">
                <button class="btn primary btn-wide" type="button" data-global-action="launch-all">Lancer les robots actifs</button>
                <div class="row">
                  <span class="badge"><span class="dot good"></span> Session OK</span>
                  <span class="badge" data-global-summary>Chargement de l'etat...</span>
                  <span class="badge" data-demo-badge>Mode démo inactif</span>
                </div>
                <div class="row">
                  <button class="btn" type="button" data-demo-toggle>Activer le mode démo</button>
                  <a class="btn" href="/history.php">Voir l'historique</a>
                  <a class="btn" href="/sensors.php">Voir les capteurs</a>
                </div>
                <p class="muted hero-feedback" data-global-feedback>
                  L'IHM envoie les commandes principales et garde les retours techniques en second plan.
                </p>
              </div>
            </section>

            <section class="panel demo-visual-panel" style="grid-column: span 12;">
              <div class="robot-card-head">
                <div>
                  <span class="eyebrow">Simulation 3D</span>
                  <h2 style="margin:4px 0 0 0;">Aperçu simplifié du bras robot</h2>
                </div>
                <span class="badge" data-visual-status>Visualisation prête</span>
              </div>
              <p class="muted" style="margin-top:0;">
                Active le mode démo pour voir un bras 3D stylisé suivre les angles de la commande en cours.
              </p>
              <div class="demo-visual-grid">
                <div class="demo-visual-canvas" data-robot-visual></div>
                <div class="demo-visual-info">
                  <span class="badge">Robot affiché: <span data-visual-robot>Robot 1</span></span>
                  <span class="badge">Position: <span data-visual-assignment>Boite 1</span></span>
                  <span class="badge">Etat: <span data-visual-mode>Attente</span></span>
                  <p class="muted" data-visual-caption>
                    Le bras se repositionne automatiquement selon les angles affichés dans le détail technique.
                  </p>
                </div>
              </div>
            </section>

            <section class="section-intro" style="grid-column: span 12;">
              <h2>Robots</h2>
              <p class="muted">
                Chaque carte contient uniquement la position, l'etat et deux boutons d'action. Ouvre les details seulement pour voir les angles.
              </p>
            </section>

            <?php
              $robots = [
                ['id' => 1, 'name' => 'Robot 1'],
                ['id' => 2, 'name' => 'Robot 2'],
                ['id' => 3, 'name' => 'Robot 3'],
              ];
              foreach ($robots as $robot):
            ?>
              <article class="panel robot-panel" style="grid-column: span 4;" data-robot-card data-robot-id="<?php echo (int)$robot['id']; ?>">
                <div class="robot-card-head">
                  <div>
                    <span class="robot-kicker">Commande rapide</span>
                    <h3 style="margin:4px 0 0 0;"><?php echo htmlspecialchars($robot['name'], ENT_QUOTES); ?></h3>
                  </div>
                  <span class="badge"><span class="dot" data-dot></span><span data-status>—</span></span>
                </div>

                <div class="robot-select">
                  <label for="assignment-<?php echo (int)$robot['id']; ?>">Position choisie</label>
                  <select id="assignment-<?php echo (int)$robot['id']; ?>" data-action="assignment">
                    <?php foreach ($assignmentOptions as $key => $option): ?>
                      <option value="<?php echo htmlspecialchars($key, ENT_QUOTES); ?>">
                        <?php echo htmlspecialchars((string)$option['label'], ENT_QUOTES); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="robot-actions">
                  <button class="btn" type="button" data-action="toggle-active">Actif/Inactif</button>
                  <button class="btn primary" type="button" data-action="start-stop">Démarrer</button>
                </div>

                <div class="robot-summary">
                  <span class="badge">Mode: <span data-mode>manuel</span></span>
                  <span class="badge">Dernier log: <span data-log>—</span></span>
                </div>

                <details class="robot-details">
                  <summary>Voir les détails techniques</summary>
                  <div class="robot-details-content">
                    <div class="muted section-label">Retour des positions angulaires</div>
                    <div class="angles-grid">
                      <span class="badge">S6: <strong data-angle="servo_6">—</strong></span>
                      <span class="badge">S7: <strong data-angle="servo_7">—</strong></span>
                      <span class="badge">S8: <strong data-angle="servo_8">—</strong></span>
                      <span class="badge">S9: <strong data-angle="servo_9">—</strong></span>
                      <span class="badge">S10: <strong data-angle="servo_10">—</strong></span>
                      <span class="badge">S11: <strong data-angle="servo_11">—</strong></span>
                    </div>
                  </div>
                </details>
              </article>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="footer">
          IHM2 • PHP/HTML • Portail d’accès (session + CGU + code optionnel)
        </div>
      </div>
    </div>

    <script type="module" src="/assets/app.js"></script>
  </body>
</html>
