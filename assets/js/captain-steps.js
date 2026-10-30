/* Progressive enhancement: the original form still works without JavaScript. */
(() => {
  const title = document.getElementById('ymcc-title');
  const page = title?.closest('.ymct-page');
  const form = page?.querySelector('form');
  if (!form) return;
  const fields = [...form.querySelectorAll('.ymct-field')];
  const submit = form.querySelector('button[type="submit"]');
  const note = form.querySelector('#ymcc-privacy');
  /* Grouped by data-step rather than by position, so adding a question does not
     silently push a field into the wrong step or disable the wizard outright. */
  const group = i => fields.filter(f => f.dataset.step === String(i + 1));
  if (!group(0).length || !group(1).length || !submit || !note) return;

  const isCaptain = form.querySelector('#ymcc-iscaptain');
  const proxyFields = fields.filter(f => f.classList.contains('ymcc-ifproxy'));
  const labels = ['Your details', 'Your team', 'Review & send'];
  const heading = page.querySelector('.ymct-card h2');
  const intro = page.querySelector('.ymct-card-intro');
  const progress = document.createElement('ol');
  progress.className = 'ymcc-progress';
  progress.setAttribute('aria-label', 'Team request steps');
  labels.forEach((label, i) => {
    const li = document.createElement('li');
    const number = document.createElement('span');
    number.textContent = String(i + 1).padStart(2, '0');
    li.append(number, document.createTextNode(label)); progress.append(li);
  });
  heading.before(progress);
  const review = document.createElement('dl');
  review.className = 'ymcc-review';
  note.before(review);
  const actions = document.createElement('div'); actions.className = 'ymcc-actions';
  const back = document.createElement('button'); back.type = 'button'; back.className = 'ymct-button ymcc-back'; back.textContent = 'Back';
  const next = document.createElement('button'); next.type = 'button'; next.className = 'ymct-button'; next.textContent = 'Continue';
  submit.before(actions); actions.append(back, next, submit);
  let step = 0;

  /* The captain's details are only asked for when someone else is requesting on
     their behalf. Required is toggled with visibility: a required field that is
     hidden blocks submission with a message the person cannot see. */
  const proxyNeeded = () => isCaptain && isCaptain.value === 'no';
  function syncProxy() {
    const show = proxyNeeded();
    proxyFields.forEach(f => {
      f.dataset.off = show ? '' : 'yes';
      f.querySelectorAll('input').forEach(i => { i.required = show; if (!show) i.value = ''; });
    });
  }
  isCaptain?.addEventListener('change', () => { syncProxy(); if (step === 0) show(0, false); });

  /* A disabled or hidden control is barred from constraint validation and always
     reports valid, so it must not be the one the step is judged on. */
  const control = field => field.querySelector('input:not([disabled]), select:not([disabled]), textarea:not([disabled])');
  const validate = i => {
    for (const field of group(i)) {
      if (field.dataset.off === 'yes') continue;
      const input = control(field);
      if (input && !input.checkValidity()) { input.reportValidity(); return false; }
    }
    return true;
  };

  const labelFor = field => {
    const el = field.querySelector('label, legend');
    if (!el) return '';
    /* The hint and the "(required)" marker live inside the label; neither
       belongs in a review list. */
    return [...el.childNodes].filter(n => n.nodeType === 3).map(n => n.textContent).join(' ').replace(/\s+/g, ' ').trim();
  };
  const valueOf = field => {
    const radio = field.querySelector('input[type=radio]:checked');
    if (radio) return radio.value;
    if (field.querySelector('input[type=radio]')) return '';
    const input = control(field);
    if (!input) return '';
    if (input.tagName === 'SELECT') return input.options[input.selectedIndex]?.text.trim() || '';
    return input.value;
  };

  function show(i, focus = true) {
    step = i;
    fields.forEach(field => {
      field.hidden = i === 2 || field.dataset.step !== String(i + 1) || field.dataset.off === 'yes';
    });
    heading.textContent = labels[i]; heading.tabIndex = -1;
    intro.textContent = ['Start with your contact details so we can send the team link.', 'Choose an available color and tell us about your team.', 'Check your details before sending your team request.'][i];
    [...progress.children].forEach((li, n) => { li.classList.toggle('is-complete', n < i); if (n === i) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current'); });
    back.hidden = i === 0; next.hidden = i === 2; submit.hidden = i !== 2;
    note.hidden = i !== 2; review.hidden = i !== 2;
    if (i === 2) {
      review.replaceChildren();
      fields.forEach(field => {
        if (field.dataset.off === 'yes') return;
        const dt = document.createElement('dt'); dt.textContent = labelFor(field);
        const dd = document.createElement('dd'); dd.textContent = valueOf(field).trim() || 'Not provided';
        review.append(dt, dd);
      });
    }
    if (focus) { heading.focus({preventScroll:true}); heading.scrollIntoView({block:'start',behavior:'instant'}); }
  }

  back.addEventListener('click', () => show(step - 1));
  next.addEventListener('click', () => { if (validate(step)) show(step + 1); });
  form.addEventListener('submit', event => {
    if (step < 2) { event.preventDefault(); if (validate(step)) show(step + 1); return; }
    for (let i = 0; i < 2; i++) {
      if (!validate(i)) { event.preventDefault(); show(i); validate(i); return; }
    }
  });
  form.noValidate = true;
  page.classList.add('ymcc-stepped');
  syncProxy();
  show(0, false);
})();
