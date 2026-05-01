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

<title>Supervision robots</title>

<style>
body { font-family: Arial; background:#0d1117; color:#e6edf3; padding:20px }

.robots {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:15px;
}

.robot {
    background:#161b22;
    padding:15px;
    border-radius:10px;
}

.status {
    width:10px;
    height:10px;
    border-radius:50%;
    display:inline-block;
}

.active { background:green }
.running { background:orange }
.error { background:red }

button {
    margin-top:5px;
    padding:8px;
    width:100%;
    border:0;
    border-radius:6px;
    cursor:pointer;
}

.start { background:#3fb950; color:#fff }
.stop { background:#f85149; color:#fff }
.toggle-on { background:#3fb950; color:#fff }
.toggle-off { background:#f85149; color:#fff }
</style>
</head>

<body>

<h2>Supervision robots</h2>

<div class="robots" id="robots">

<?php foreach ([1=>'Robot A',2=>'Robot B',3=>'Robot C'] as $id=>$name): ?>
<div class="robot" data-id="<?= $id ?>">
    <h3>
        <?= htmlspecialchars($name) ?>
        <span class="status" data-dot></span>
    </h3>

    <p data-log>Chargement...</p>

    <button data-action="start" class="start">▶ Start</button>
    <button data-action="stop" class="stop">⏹ Stop</button>
    <button data-action="toggle-active">Activer / Désactiver</button>
</div>
<?php endforeach; ?>

</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ─────────────────────────────
// API POST
// ─────────────────────────────
async function post(action, id) {
    return fetch('/ihm2/api/action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: action,
            robot_id: id,
            _csrf: CSRF   // ✅ CORRIGÉ ICI
        })
    }).then(r => r.json());
}

// ─────────────────────────────
// REFRESH STATE
// ─────────────────────────────
async function refresh() {
    const res = await fetch('/ihm2/api/state.php');
    const data = await res.json();

    if (!data.robots) return;

    data.robots.forEach(r => {
        const card = document.querySelector(`[data-id="${r.id}"]`);
        if (!card) return;

        const dot = card.querySelector('[data-dot]');
        const log = card.querySelector('[data-log]');
        const btnStart = card.querySelector('[data-action="start"]');
        const btnStop = card.querySelector('[data-action="stop"]');
        const btnToggle = card.querySelector('[data-action="toggle-active"]');

        // status
        dot.className = 'status';
        if (r.running) dot.classList.add('running');
        else if (r.active) dot.classList.add('active');
        else if (r.status === 'error') dot.classList.add('error');

        // log
        log.textContent = r.last_log || 'Aucun log';

        // buttons state
        btnStart.disabled = !r.active || r.running;
        btnStop.disabled = !r.running;

        // toggle UX
        btnToggle.textContent = r.active ? 'Désactiver' : 'Activer';
        btnToggle.className = r.active ? 'toggle-off' : 'toggle-on';
    });
}

// ─────────────────────────────
// CLICK HANDLER
// ─────────────────────────────
document.getElementById('robots').addEventListener('click', async (e) => {
    const btn = e.target.closest('button');
    if (!btn) return;

    const card = btn.closest('[data-id]');
    if (!card) return;

    const id = parseInt(card.dataset.id);
    const type = btn.dataset.action;

    let action = '';

    if (type === 'start') action = 'start_robot';
    else if (type === 'stop') action = 'stop_robot';
    else if (type === 'toggle-active') action = 'toggle_active';
    else return;

    btn.disabled = true;

    await post(action, id);
    await refresh();

    btn.disabled = false;
});

// init
refresh();
setInterval(refresh, 3000);

</script>

</body>
</html>