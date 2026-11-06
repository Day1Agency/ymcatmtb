(() => {
 const toolbar=document.querySelector('.request-toolbar');
 if(!toolbar)return;
 toolbar.hidden=false;
 const rows=[...document.querySelectorAll('tr[data-request]')];
 const search=document.querySelector('#request-search');
 const buttons=[...document.querySelectorAll('[data-filter]')];
 let selected='all';
 function filter(){
  const query=search.value.trim().toLowerCase();let count=0;
  rows.forEach(row=>{row.hidden=!(selected==='all'||row.dataset.bucket.split(' ').includes(selected))||!row.textContent.toLowerCase().includes(query);if(!row.hidden)count++;});
  buttons.forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.filter===selected)));
  document.querySelector('#request-count').textContent=count+' of '+rows.length+' requests';
  document.querySelector('.filter-empty').hidden=count!==0||rows.length===0;
 }
 buttons.forEach(b=>b.addEventListener('click',()=>{selected=b.dataset.filter;filter();}));
 document.querySelectorAll('[data-filter-shortcut]').forEach(a=>a.addEventListener('click',()=>{selected=a.dataset.filterShortcut;search.value='';filter();}));
 search.addEventListener('input',filter);
 filter();
})();
/* Its own block: the filtering above returns early on a screen with no request
   table, and the sidebar still has to follow the scroll there. */
(() => {
 /* The last heading to have passed the top of the screen is the one you are
    reading, which is steadier than asking what is merely visible: tall sections
    would otherwise fight over it. */
 const parts=[...document.querySelectorAll('.dash-sidebar nav .nav-part')];
 const targets=parts.map(a=>[a,document.querySelector(a.getAttribute('href'))]).filter(p=>p[1]);
 if(targets.length){
  const mark=a=>parts.forEach(p=>p.classList.toggle('current',p===a));
  let queued=false;
  const spy=()=>{
   queued=false;
   let current=targets[0][0];
   targets.forEach(([link,el])=>{if(el.getBoundingClientRect().top<=140)current=link;});
   /* At the very bottom the last section may never reach the line, so nothing
      would ever mark it. */
   if(window.innerHeight+window.scrollY>=document.body.scrollHeight-4)current=targets[targets.length-1][0];
   mark(current);
  };
  addEventListener('scroll',()=>{if(!queued){queued=true;requestAnimationFrame(spy);}},{passive:true});
  addEventListener('resize',spy,{passive:true});
  parts.forEach(a=>a.addEventListener('click',()=>mark(a)));
  spy();
 }
})();
(() => {
 const tabs=[...document.querySelectorAll('[data-reg-tab]')];
 if(!tabs.length)return;
 const panels=[...document.querySelectorAll('[data-reg-panel]')];
 const show=name=>{
  tabs.forEach(t=>t.setAttribute('aria-selected',String(t.dataset.regTab===name)));
  panels.forEach(p=>{p.hidden=p.dataset.regPanel!==name;});
 };
 tabs.forEach(t=>t.addEventListener('click',()=>show(t.dataset.regTab)));
 /* Hidden until here so the tabs never appear without anything to drive them. */
 document.querySelector('.reg-tabs').hidden=false;
 show('teams');
})();
