(() => {
  'use strict';
  const section = document.querySelector('#campaign');
  const goalBox = section?.querySelector('[data-dc-tpl="59"]');
  if (!goalBox || !window.ymcProgressEndpoint) return;
  const money = v => new Intl.NumberFormat('en-US', {style:'currency',currency:'USD',maximumFractionDigits: Number.isInteger(v) ? 0 : 2}).format(v);
  fetch(window.ymcProgressEndpoint, {credentials:'omit',signal:AbortSignal.timeout(10000)})
    .then(r => { if (!r.ok) throw new Error('Unavailable'); return r.json(); })
    .then(data => {
      if (!Number.isFinite(data.raised) || data.raised < 0 || data.goal !== 750000 || data.currency !== 'USD' || data.campaigns?.length !== 4 || !Number.isFinite(Date.parse(data.updated_at))) return;
      const wrap = document.createElement('div'); wrap.className = 'ymc-progress';
      const label = document.createElement('div'); label.className='ymc-progress-label'; label.textContent='Raised together';
      const total = document.createElement('div'); total.className='ymc-progress-total'; total.textContent=money(data.raised);
      const goal = document.createElement('p'); goal.className='ymc-progress-goal'; goal.textContent='of our $750,000 annual goal';
      const bar = document.createElement('progress'); bar.max=data.goal; bar.value=Math.min(data.raised,data.goal); bar.setAttribute('aria-label','Annual campaign fundraising progress');
      const note = document.createElement('p'); note.className='ymc-progress-note'; note.textContent='Combined donations, sponsorships, registrations and spectator tickets.';
      const stamp = document.createElement('p'); stamp.className='ymc-progress-note';
      stamp.textContent='Last updated '+new Date(data.updated_at).toLocaleString('en-US',{timeZone:'America/New_York',month:'short',day:'numeric',hour:'numeric',minute:'2-digit'})+' ET';
      const details = document.createElement('details'); const summary=document.createElement('summary'); summary.textContent='See the breakdown'; details.append(summary);
      const list=document.createElement('ul');
      data.campaigns.forEach(row=>{ const li=document.createElement('li'); li.textContent=row.label+': '+money(row.raised); list.append(li); }); details.append(list);
      wrap.append(label,total,goal,bar,note,stamp,details); goalBox.replaceChildren(wrap);
    }).catch(()=>{ /* Keep original campaign goal when a complete total is unavailable. */ });
})();
