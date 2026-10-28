/* Progressive enhancement: the original form still works without JavaScript. */
(() => {
  const title = document.getElementById('ymcc-title');
  const page = title?.closest('.ymct-page');
  const form = page?.querySelector('form');
  if (!form) return;
  const fields = [...form.querySelectorAll('.ymct-field')];
  const submit = form.querySelector('button[type="submit"]');
  const note = form.querySelector('#ymcc-privacy');
  if (fields.length !== 6 || !submit || !note) return;
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
  const group = i => fields.slice(i === 0 ? 0 : 3, i === 0 ? 3 : 6);
  const validate = i => {
    for (const field of group(i)) {
      const input = field.querySelector('input, select, textarea');
      if (!input.checkValidity()) { input.reportValidity(); return false; }
    }
    return true;
  };
  function show(i, focus = true) {
    step = i;
    fields.forEach((field, index) => { field.hidden = i === 2 || (i === 0 ? index >= 3 : index < 3); });
    heading.textContent = labels[i]; heading.tabIndex = -1;
    intro.textContent = ['Start with your contact details so we can send your team link.', 'Choose an available color and tell us about your team.', 'Check your details before sending your team request.'][i];
    [...progress.children].forEach((li, n) => { li.classList.toggle('is-complete', n < i); if (n === i) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current'); });
    back.hidden = i === 0; next.hidden = i === 2; submit.hidden = i !== 2;
    note.hidden = i !== 2; review.hidden = i !== 2;
    if (i === 2) {
      review.replaceChildren();
      const names = ['Full name', 'Email address', 'Phone number', 'Team color', 'Play style', 'Team story'];
      fields.forEach((field, n) => {
        const input = field.querySelector('input, select, textarea');
        const dt = document.createElement('dt'); dt.textContent = names[n];
        const dd = document.createElement('dd'); dd.textContent = input.value.trim() || 'Not provided';
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
      if (group(i).some(f => !f.querySelector('input, select, textarea').checkValidity())) { event.preventDefault(); show(i); validate(i); return; }
    }
  });
  form.noValidate = true;
  page.classList.add('ymcc-stepped');
  show(0, false);
})();
