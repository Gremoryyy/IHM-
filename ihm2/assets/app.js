(() => {
  const qs = (sel, root = document) => root.querySelector(sel);
  const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  const cards = qsa('[data-robot-card]');
  for (const card of cards) {
    const state = {
      active: card.getAttribute('data-active') === '1',
      running: card.getAttribute('data-running') === '1',
    };

    const badge = qs('[data-status]', card);
    const dot = qs('[data-dot]', card);
    const btnToggleActive = qs('[data-action="toggle-active"]', card);
    const btnStartStop = qs('[data-action="start-stop"]', card);

    const render = () => {
      if (state.active) {
        badge.textContent = state.running ? 'Actif • En cours' : 'Actif • Prêt';
        dot.className = 'dot good';
      } else {
        badge.textContent = 'Inactif';
        dot.className = 'dot bad';
      }
      btnStartStop.disabled = !state.active;
      btnStartStop.textContent = state.running ? 'Arrêter' : 'Démarrer';
    };

    btnToggleActive?.addEventListener('click', () => {
      state.active = !state.active;
      if (!state.active) state.running = false;
      render();
    });

    btnStartStop?.addEventListener('click', () => {
      if (!state.active) return;
      state.running = !state.running;
      render();
    });

    render();
  }
})();

