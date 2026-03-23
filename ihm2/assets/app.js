(() => {
  const qs = (sel, root = document) => root.querySelector(sel);
  const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const csrfToken = qs('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const cards = qsa('[data-robot-card]');
  const globalSummary = qs('[data-global-summary]');
  const globalFeedback = qs('[data-global-feedback]');
  const btnLaunchAll = qs('[data-global-action="launch-all"]');

  let currentState = null;
  let assignmentOptions = {};

  const assignmentLabel = (key) => assignmentOptions[key]?.label || key || '—';

  const setGlobalMessage = (message) => {
    if (globalFeedback) globalFeedback.textContent = message;
  };

  const updateSummary = (robots) => {
    const activeCount = robots.filter((robot) => robot.active).length;
    const runningCount = robots.filter((robot) => robot.running).length;
    if (globalSummary) {
      globalSummary.textContent = `${activeCount}/3 actifs • ${runningCount}/3 en cours`;
    }
  };

  const renderCard = (card, robot) => {
    const badge = qs('[data-status]', card);
    const dot = qs('[data-dot]', card);
    const btnToggleActive = qs('[data-action="toggle-active"]', card);
    const btnStartStop = qs('[data-action="start-stop"]', card);
    const assignment = qs('[data-action="assignment"]', card);
    const mode = qs('[data-mode]', card);
    const log = qs('[data-log]', card);

    if (badge) {
      badge.textContent = robot.active
        ? (robot.running ? 'Actif • En cours' : 'Actif • Prêt')
        : 'Inactif';
    }

    if (dot) {
      dot.className = robot.active ? (robot.running ? 'dot good pulse' : 'dot good') : 'dot bad';
    }

    if (btnToggleActive) {
      btnToggleActive.textContent = robot.active ? 'Mettre inactif' : 'Mettre actif';
    }

    if (btnStartStop) {
      btnStartStop.disabled = !robot.active;
      btnStartStop.textContent = robot.running ? 'Arrêter' : 'Démarrer';
    }

    if (assignment) {
      assignment.value = robot.assignment;
      assignment.disabled = robot.running;
    }

    if (mode) {
      mode.textContent = assignmentLabel(robot.assignment);
    }

    if (log) {
      log.textContent = robot.last_log || '—';
    }

    Object.entries(robot.current_angles || {}).forEach(([key, value]) => {
      const node = qs(`[data-angle="${key}"]`, card);
      if (node) node.textContent = `${value}°`;
    });
  };

  const render = () => {
    const robots = currentState?.robots || [];
    cards.forEach((card) => {
      const robotId = Number(card.getAttribute('data-robot-id'));
      const robot = robots.find((item) => Number(item.id) === robotId);
      if (robot) renderCard(card, robot);
    });
    updateSummary(robots);
  };

  const postAction = async (payload) => {
    const response = await fetch('/api/action.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken,
      },
      body: JSON.stringify({ ...payload, _csrf: csrfToken }),
    });

    const data = await response.json();
    if (!response.ok || !data.ok) {
      throw new Error(data.error || 'Action impossible.');
    }

    currentState = data.state;
    render();
    return data;
  };

  const fetchState = async () => {
    const response = await fetch('/api/state.php', { headers: { Accept: 'application/json' } });
    const data = await response.json();
    if (!response.ok || !data.ok) {
      throw new Error(data.error || 'Chargement impossible.');
    }

    currentState = data.state;
    assignmentOptions = data.assignment_options || {};
    render();
  };

  btnLaunchAll?.addEventListener('click', async () => {
    btnLaunchAll.disabled = true;
    try {
      await postAction({ action: 'launch_all' });
      setGlobalMessage('Lancement collaboratif envoye aux robots actifs.');
    } catch (error) {
      setGlobalMessage(error.message);
    } finally {
      btnLaunchAll.disabled = false;
    }
  });

  cards.forEach((card) => {
    const robotId = Number(card.getAttribute('data-robot-id'));
    const btnToggleActive = qs('[data-action="toggle-active"]', card);
    const btnStartStop = qs('[data-action="start-stop"]', card);
    const assignment = qs('[data-action="assignment"]', card);

    btnToggleActive?.addEventListener('click', async () => {
      try {
        await postAction({ action: 'toggle_active', robot_id: robotId });
        setGlobalMessage(`Etat mis a jour pour le robot ${robotId}.`);
      } catch (error) {
        setGlobalMessage(error.message);
      }
    });

    btnStartStop?.addEventListener('click', async () => {
      try {
        await postAction({ action: 'toggle_running', robot_id: robotId });
        setGlobalMessage(`Commande envoyee au robot ${robotId}.`);
      } catch (error) {
        setGlobalMessage(error.message);
      }
    });

    assignment?.addEventListener('change', async (event) => {
      try {
        await postAction({
          action: 'set_assignment',
          robot_id: robotId,
          assignment: event.target.value,
        });
        setGlobalMessage(`Affectation mise a jour pour le robot ${robotId}.`);
      } catch (error) {
        setGlobalMessage(error.message);
      }
    });
  });

  fetchState().catch((error) => {
    setGlobalMessage(error.message);
  });
})();
