(() => {
 const PER_PAGE = 25;

 /* Filtering and paging are kept apart. A filter says whether a row belongs in
    the list at all and marks it; paging then decides which part of that list is
    on screen. Done in one step, every filter would have to know about pages and
    every page about filters. */
 const keep = (row, ok) => { row.dataset.out = ok ? '' : '1'; };

 /** Paginates the table sharing a parent with this pager. */
 function pager(el) {
  const scope  = el.parentElement;
  const prev   = el.querySelector('[data-page="prev"]');
  const next   = el.querySelector('[data-page="next"]');
  const status = el.querySelector('[data-page-status]');
  const label  = el.dataset.pagerLabel || 'rows';
  let page = 1;

  const draw = () => {
   /* A hidden tab's rows are nobody's business until it is shown again. */
   if (scope.hidden) { el.hidden = true; return; }
   const rows  = [...scope.querySelectorAll('tbody tr')];
   const alive = rows.filter(r => !r.dataset.out);
   const pages = Math.max(1, Math.ceil(alive.length / PER_PAGE));
   if (page > pages) page = pages;
   const from = (page - 1) * PER_PAGE;
   rows.forEach(r => { r.hidden = true; });
   alive.slice(from, from + PER_PAGE).forEach(r => { r.hidden = false; });
   el.hidden = alive.length <= PER_PAGE;
   status.textContent = alive.length
    ? (from + 1) + '–' + Math.min(from + PER_PAGE, alive.length) + ' of ' + alive.length + ' ' + label
    : 'No ' + label;
   prev.disabled = 1 === page;
   next.disabled = page >= pages;
  };

  prev.addEventListener('click', () => { page--; draw(); scope.scrollIntoView({block: 'nearest'}); });
  next.addEventListener('click', () => { page++; draw(); scope.scrollIntoView({block: 'nearest'}); });
  return { draw, reset() { page = 1; } };
 }

 const pagers = [...document.querySelectorAll('[data-pager]')].map(pager);
 const repage = () => pagers.forEach(p => p.draw());
 const rewind = () => pagers.forEach(p => p.reset());

 /* The captain requests table. Scoped to its own section: page-wide, it also
    drove the filters belonging to the other tables. */
 (() => {
  const toolbar = document.querySelector('.request-toolbar:not([data-toolbar])');
  if (!toolbar) return;
  toolbar.hidden = false;
  const section = toolbar.closest('section');
  const rows    = [...section.querySelectorAll('tr[data-request]')];
  const search  = document.querySelector('#request-search');
  const buttons = [...toolbar.querySelectorAll('[data-filter]')];
  let selected  = 'all';

  const filter = () => {
   const query = search.value.trim().toLowerCase();
   let count = 0;
   rows.forEach(row => {
    const ok = ('all' === selected || row.dataset.bucket.split(' ').includes(selected))
     && row.textContent.toLowerCase().includes(query);
    keep(row, ok);
    if (ok) count++;
   });
   buttons.forEach(b => b.setAttribute('aria-pressed', String(b.dataset.filter === selected)));
   section.querySelector('#request-count').textContent = count + ' of ' + rows.length + ' requests';
   section.querySelector('.filter-empty').hidden = 0 !== count || 0 === rows.length;
   rewind();
   repage();
  };

  buttons.forEach(b => b.addEventListener('click', () => { selected = b.dataset.filter; filter(); }));
  document.querySelectorAll('[data-filter-shortcut]').forEach(a => a.addEventListener('click', () => {
   selected = a.dataset.filterShortcut; search.value = ''; filter();
  }));
  search.addEventListener('input', filter);
  filter();
 })();

 /* Every other toolbar. Where its buttons name panels they switch tables, where
    they name row buckets they filter rows, and both search. */
 document.querySelectorAll('[data-toolbar]').forEach(toolbar => {
  const section = toolbar.closest('section');
  if (!section) return;
  const buttons = [...toolbar.querySelectorAll('[data-filter]')];
  const search  = toolbar.querySelector('[data-search]');
  const panels  = [...section.querySelectorAll('[data-reg-panel]')];
  const empty   = section.querySelector('.filter-empty');
  let selected  = buttons.length ? buttons[0].dataset.filter : 'all';

  const apply = () => {
   const query = search.value.trim().toLowerCase();
   buttons.forEach(b => b.setAttribute('aria-pressed', String(b.dataset.filter === selected)));
   if (panels.length) panels.forEach(p => { p.hidden = p.dataset.regPanel !== selected; });
   let shown = 0;
   section.querySelectorAll('tr[data-row],tr[data-colour]').forEach(row => {
    const panel  = row.closest('[data-reg-panel]');
    const here   = !panel || !panel.hidden;
    const bucket = !!panels.length || selected === buttons[0].dataset.filter
     || row.dataset.bucket === selected;
    const hit = !query || row.textContent.toLowerCase().includes(query);
    const ok  = here && bucket && hit;
    keep(row, ok);
    if (ok) shown++;
   });
   if (empty) empty.hidden = 0 !== shown;
   rewind();
   repage();
  };

  buttons.forEach(b => b.addEventListener('click', () => { selected = b.dataset.filter; apply(); }));
  search.addEventListener('input', apply);
  /* Hidden until here so the controls never appear without anything driving them. */
  toolbar.hidden = false;
  apply();
 });

 /* Any table nothing filtered still needs its first page drawn. */
 repage();

 /* The sidebar follows the page: the last heading past the top of the screen is
    the one you are reading, which is steadier than asking what is merely
    visible, since tall sections would fight over it. */
 const parts   = [...document.querySelectorAll('.dash-sidebar nav .nav-part')];
 const targets = parts.map(a => [a, document.querySelector(a.getAttribute('href'))]).filter(p => p[1]);
 if (targets.length) {
  const mark = a => parts.forEach(p => p.classList.toggle('current', p === a));
  let queued = false;
  const spy = () => {
   queued = false;
   let current = targets[0][0];
   targets.forEach(([link, el]) => { if (el.getBoundingClientRect().top <= 140) current = link; });
   /* At the very bottom the last section may never reach the line. */
   if (innerHeight + scrollY >= document.body.scrollHeight - 4) current = targets[targets.length - 1][0];
   mark(current);
  };
  addEventListener('scroll', () => { if (!queued) { queued = true; requestAnimationFrame(spy); } }, {passive: true});
  addEventListener('resize', spy, {passive: true});
  parts.forEach(a => a.addEventListener('click', () => mark(a)));
  spy();
 }
})();
