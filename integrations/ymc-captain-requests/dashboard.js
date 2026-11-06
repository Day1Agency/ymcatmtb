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
/* One driver for every toolbar on the screen. Two kinds of list use it: the
   colour board, where the buttons filter rows of one table, and the
   registrations, where they swap between two tables entirely. Both also search,
   so the behaviour is shared rather than written twice. */
document.querySelectorAll('[data-toolbar]').forEach(toolbar => {
 const section=toolbar.closest('section');
 if(!section)return;
 const buttons=[...toolbar.querySelectorAll('[data-filter]')];
 const search=toolbar.querySelector('[data-search]');
 const panels=[...section.querySelectorAll('[data-reg-panel]')];
 const empty=section.querySelector('.filter-empty');
 let selected=buttons.length?buttons[0].dataset.filter:'all';

 const apply=()=>{
  const query=search.value.trim().toLowerCase();
  buttons.forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.filter===selected)));
  /* Where the buttons name panels they switch panels; where they name row
     buckets they filter rows. One toolbar, told apart by what is on the page. */
  if(panels.length)panels.forEach(p=>{p.hidden=p.dataset.regPanel!==selected;});
  let shown=0;
  section.querySelectorAll('tr[data-row],tr[data-colour]').forEach(row=>{
   const inPanel=!row.closest('[data-reg-panel]')||!row.closest('[data-reg-panel]').hidden;
   const inBucket=!panels.length?(selected===buttons[0].dataset.filter||row.dataset.bucket===selected):true;
   const hit=!query||row.textContent.toLowerCase().includes(query);
   row.hidden=!(inPanel&&inBucket&&hit);
   if(!row.hidden)shown++;
  });
  if(empty)empty.hidden=shown!==0;
 };

 buttons.forEach(b=>b.addEventListener('click',()=>{selected=b.dataset.filter;apply();}));
 search.addEventListener('input',apply);
 /* Hidden until here so the controls never appear without anything driving them. */
 toolbar.hidden=false;
 apply();
});
