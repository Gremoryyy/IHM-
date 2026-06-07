<?php
declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/store.php';

ensure_session_started($CONFIG);
portal_require_auth($CONFIG);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>">
  <title>Robots — Supervision</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: "Segoe UI", sans-serif; background: #0d1117; color: #e6edf3; min-height: 100vh; padding: 24px 16px; }
    header { display: flex; justify-content: space-between; align-items: center; max-width: 900px; margin: 0 auto 32px; }
    header h1 { font-size: 20px; font-weight: 600; }
    header nav { display: flex; gap: 8px; }
    .robots { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; max-width: 900px; margin: 0 auto; }
    .robot-card { background: #161b22; border: 1px solid #30363d; border-radius: 12px; padding: 20px; }
    .robot-card-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    .robot-name { font-size: 16px; font-weight: 600; }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; background: #30363d; display: inline-block; }
    .status-dot.active { background: #3fb950; box-shadow: 0 0 6px #3fb950; }
    .status-dot.running { background: #f0c442; box-shadow: 0 0 6px #f0c442; }
    .status-dot.error { background: #f85149; box-shadow: 0 0 6px #f85149; }
    .log { font-size: 12px; color: #8b949e; background: #0d1117; border-radius: 6px; padding: 10px 12px; min-height: 48px; margin-bottom: 16px; line-height: 1.5; }
    .actions { display: flex; gap: 8px; }
    button { flex: 1; padding: 9px 12px; border-radius: 8px; border: 1px solid #30363d; cursor: pointer; font-size: 13px; font-weight: 600; background: #21262d; color: #e6edf3; transition: background 0.15s; }
    button:disabled { opacity: 0.4; cursor: not-allowed; }
    button.btn-start { background: rgba(63,185,80,0.15); border-color: rgba(63,185,80,0.4); color: #3fb950; }
    button.btn-stop { background: rgba(248,81,73,0.15); border-color: rgba(248,81,73,0.4); color: #f85149; }
    .btn-nav { font-size: 13px; padding: 7px 12px; border-radius: 8px; border: 1px solid #30363d; background: #21262d; color: #8b949e; text-decoration: none; font-weight: 500; }
    .btn-nav.danger { color: #f85149; border-color: rgba(248,81,73,0.4); }
  </style>
</head>
<body>
<header>
  <h1>Supervision robots</h1>
  <nav>
    <a class="btn-nav" href="/history.php">Historique</a>
    <a class="btn-nav danger" href="/logout.php">Déconnexion</a>
  </nav>
</header>
<div class="robots" id="robots">
  <?php foreach ([1 => 'Robot A', 2 => 'Robot B', 3 => 'Robot C'] as $id => $name): ?>
  <div class="robot-card" data-id="<?= $id ?>">
    <div class="robot-card-top">
      <span class="robot-name"><?= htmlspecialchars($name) ?></span>
      <span class="status-dot" data-dot></span>
    </div>
    <div class="log" data-log>Chargement...</div>
    <div class="actions">
      <button class="btn-start" data-action="toggle-active">Activer</button>
      <button class="btn-stop" data-action="toggle-running" disabled>Arreter</button>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

async function post(url, body) {
  const r = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ ...body, _csrf_token: CSRF })
  });
  return r.json();
}

async function refresh() {
  try {
    const r = await fetch('api/state.php');
    const data = await r.json();

    let robots = [];

    if (Array.isArray(data.robots)) {
      robots = data.robots;
    } else if (data.state && Array.isArray(data.state.robots)) {
      robots = data.state.robots;
    } else {
      console.error('Format JSON inattendu:', data);
      return;
    }

    robots.forEach(robot => {
      const card = document.querySelector('[data-id="' + robot.id + '"]');
      if (!card) return;

      const dot = card.querySelector('[data-dot]');
      const log = card.querySelector('[data-log]');
      const btnAct = card.querySelector('[data-action="toggle-active"]');
      const btnRun = card.querySelector('[data-action="toggle-running"]');

      dot.className = 'status-dot';

    if (robot.running) dot.classList.add('running');
    else if (robot.active) dot.classList.add('active');
    else if (robot.status === 'error' || robot.status === 'offline') dot.classList.add('error');

    log.textContent = robot.last_log || 'Aucun log';

      btnAct.textContent = robot.active ? 'Desactiver' : 'Activer';
    btnAct.className = robot.active ? 'btn-stop' : 'btn-start';

    btnRun.textContent = robot.running ? 'Arreter' : 'Demarrer';
    btnRun.className = robot.running ? 'btn-stop' : 'btn-start';
    btnRun.disabled = !robot.active;
    });
  } catch (e) {
    console.error('Erreur refresh:', e);
  }
}

document.getElementById('robots').addEventListener('click', async e => {
  const btn = e.target.closest('button');
  if (!btn) return;

  const card = btn.closest('[data-id]');
  if (!card) return;

  const id = parseInt(card.dataset.id, 10);
  btn.disabled = true;

  try {
    const action = btn.dataset.action === 'toggle-active'
? 'toggle_active'
: 'toggle_running';

const res = await post('api/action.php', {
  action: action,
  robot_id: id
});

console.log('Réponse action:', res);
  } catch (e) {
    console.error('Erreur action:', e);
  }

  await refresh();
  btn.disabled = false;
});

refresh();
setInterval(refresh, 3000);
</script>
</body>
</html>
