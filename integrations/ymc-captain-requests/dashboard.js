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
  rows.forEach(row=>{row.hidden=!(selected==='all'||row.dataset.bucket===selected)||!row.textContent.toLowerCase().includes(query);if(!row.hidden)count++;});
  buttons.forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.filter===selected)));
  document.querySelector('#request-count').textContent=count+' of '+rows.length+' requests';
  document.querySelector('.filter-empty').hidden=count!==0||rows.length===0;
 }
 buttons.forEach(b=>b.addEventListener('click',()=>{selected=b.dataset.filter;filter();}));
 document.querySelectorAll('[data-filter-shortcut]').forEach(a=>a.addEventListener('click',()=>{selected=a.dataset.filterShortcut;search.value='';filter();}));
 search.addEventListener('input',filter);
 document.querySelectorAll('.dash-sidebar nav a').forEach(a=>a.addEventListener('click',()=>{document.querySelectorAll('.dash-sidebar nav a').forEach(n=>n.classList.toggle('active',n===a));}));
 filter();
})();
