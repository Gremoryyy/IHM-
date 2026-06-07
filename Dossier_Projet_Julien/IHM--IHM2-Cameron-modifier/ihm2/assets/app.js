(() => {
  const qs = (sel, root = document) => root.querySelector(sel);
  const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const csrfToken = qs('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const cards = qsa('[data-robot-card]');
  const globalSummary = qs('[data-global-summary]');
  const globalFeedback = qs('[data-global-feedback]');
  const btnLaunchAll = qs('[data-global-action="launch-all"]');
  const btnDemoToggle = qs('[data-demo-toggle]');
  const demoBadge = qs('[data-demo-badge]');
  const visualContainer = qs('[data-robot-visual]');
  const visualRobot = qs('[data-visual-robot]');
  const visualAssignment = qs('[data-visual-assignment]');
  const visualMode = qs('[data-visual-mode]');
  const visualStatus = qs('[data-visual-status]');

  let currentState = null;
  let assignmentOptions = {};
  let demoMode = window.localStorage.getItem('robot-demo-mode') === '1';
  let visual = null;

  const createDemoState = () => ({
    updated_at: new Date().toISOString(),
    robots: [
      { id: 1, name: 'robot_1', active: true, running: false, assignment: 'box_1', box_number: 1, last_log: 'Mode démo prêt', current_angles: { servo_6: 40, servo_7: 130, servo_8: 180, servo_9: 80, servo_10: 0, servo_11: 120 } },
      { id: 2, name: 'robot_2', active: true, running: false, assignment: 'box_2', box_number: 2, last_log: 'Mode démo prêt', current_angles: { servo_6: 40, servo_7: 130, servo_8: 180, servo_9: 80, servo_10: 0, servo_11: 120 } },
      { id: 3, name: 'robot_3', active: true, running: false, assignment: 'box_3', box_number: 3, last_log: 'Mode démo prêt', current_angles: { servo_6: 40, servo_7: 130, servo_8: 180, servo_9: 80, servo_10: 0, servo_11: 120 } },
    ],
  });

  const currentAnglesForAssignment = (assignment, running) => {
    const home = { servo_6: 40, servo_7: 130, servo_8: 180, servo_9: 80, servo_10: 0, servo_11: 120 };
    const activeProfiles = {
      box_1: { servo_6: 160, servo_7: 60, servo_8: 170, servo_9: 80, servo_10: 40, servo_11: 150 },
      box_2: { servo_6: 135, servo_7: 60, servo_8: 170, servo_9: 80, servo_10: 40, servo_11: 150 },
      box_3: { servo_6: 110, servo_7: 60, servo_8: 170, servo_9: 80, servo_10: 40, servo_11: 150 },
      box_4: { servo_6: 85, servo_7: 60, servo_8: 170, servo_9: 80, servo_10: 40, servo_11: 150 },
    };
    return running ? (activeProfiles[assignment] || home) : home;
  };

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
    if (demoBadge) {
      demoBadge.textContent = demoMode ? 'Mode démo actif' : 'Mode démo inactif';
    }
    if (btnDemoToggle) {
      btnDemoToggle.textContent = demoMode ? 'Désactiver le mode démo' : 'Activer le mode démo';
    }
  };

  const renderVisual = (robots) => {
    if (!visual || robots.length === 0) return;
    const targetRobot = robots.find((robot) => robot.running) || robots[0];
    visual.applyAngles(targetRobot.current_angles || {});
    if (visualRobot) visualRobot.textContent = targetRobot.name || `Robot ${targetRobot.id}`;
    if (visualAssignment) visualAssignment.textContent = assignmentLabel(targetRobot.assignment);
    if (visualMode) visualMode.textContent = targetRobot.running ? 'Simulation en mouvement' : 'Attente';
    if (visualStatus) visualStatus.textContent = demoMode ? 'Visualisation démo active' : 'Visualisation liée à l’état courant';
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
    renderVisual(robots);
  };

  const postAction = async (payload) => {
    if (demoMode) {
      const robots = currentState?.robots || createDemoState().robots;
      const robot = robots.find((item) => Number(item.id) === Number(payload.robot_id));

      if (payload.action === 'launch_all') {
        robots.forEach((item) => {
          if (!item.active) return;
          item.running = true;
          item.last_log = 'Démo : lancement collaboratif';
          item.current_angles = currentAnglesForAssignment(item.assignment, true);
        });
      }

      if (robot && payload.action === 'toggle_active') {
        robot.active = !robot.active;
        robot.running = false;
        robot.last_log = robot.active ? 'Démo : robot activé' : 'Démo : robot désactivé';
        robot.current_angles = currentAnglesForAssignment(robot.assignment, false);
      }

      if (robot && payload.action === 'toggle_running') {
        if (!robot.active) throw new Error('Le robot est inactif.');
        robot.running = !robot.running;
        robot.last_log = robot.running ? 'Démo : séquence lancée' : 'Démo : séquence arrêtée';
        robot.current_angles = currentAnglesForAssignment(robot.assignment, robot.running);
      }

      if (robot && payload.action === 'set_assignment') {
        robot.assignment = payload.assignment;
        robot.last_log = `Démo : affectation ${assignmentLabel(payload.assignment)}`;
        robot.current_angles = currentAnglesForAssignment(robot.assignment, robot.running);
      }

      currentState = { updated_at: new Date().toISOString(), robots };
      render();
      return { ok: true, state: currentState };
    }

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
    if (demoMode) {
      currentState = createDemoState();
      assignmentOptions = {
        box_1: { label: 'Boite 1' },
        box_2: { label: 'Boite 2' },
        box_3: { label: 'Boite 3' },
        box_4: { label: 'Boite 4' },
      };
      render();
      setGlobalMessage('Mode démo actif : aucune commande réelle n’est envoyée.');
      return;
    }

    const response = await fetch('/api/state.php', { headers: { Accept: 'application/json' } });
    const data = await response.json();
    if (!response.ok || !data.ok) {
      throw new Error(data.error || 'Chargement impossible.');
    }

    currentState = data.state;
    assignmentOptions = data.assignment_options || {};
    render();
  };

  if (visualContainer) {
    import('./robot-visual.js')
      .then((module) => {
        if (typeof module.createRobotVisual === 'function') {
          visual = module.createRobotVisual(visualContainer);
          render();
        }
      })
      .catch(() => {
        if (visualStatus) {
          visualStatus.textContent = 'Visualisation 3D indisponible';
        }
      });
  }

  btnDemoToggle?.addEventListener('click', async () => {
    demoMode = !demoMode;
    window.localStorage.setItem('robot-demo-mode', demoMode ? '1' : '0');
    await fetchState();
  });

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
