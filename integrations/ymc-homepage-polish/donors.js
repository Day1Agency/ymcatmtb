(()=>{
 const root=document.getElementById('ymc-donor-feed');if(!root)return;
 const pause=document.getElementById('ymc-feed-pause'), reduced=matchMedia('(prefers-reduced-motion: reduce)');
 let stopped=reduced.matches,hover=false,focus=false,until=Date.now()+4000,last=0,position=0,direction=1,signature='';
 const text=(tag,cls,value)=>{const el=document.createElement(tag);el.className=cls;el.textContent=value;return el;};
 const money=n=>new Intl.NumberFormat('en-US',{style:'currency',currency:'USD',maximumFractionDigits:Number.isInteger(n)?0:2}).format(n);
 const time=d=>{const n=Math.max(0,Math.floor((Date.now()-new Date(d))/1000)),ago=(v,u)=>v+' '+u+(v===1?'':'s')+' ago';return n<60?'Just now':n<3600?ago(Math.floor(n/60),'minute'):n<86400?ago(Math.floor(n/3600),'hour'):ago(Math.floor(n/86400),'day');};
 function state(){pause.textContent=stopped?'Resume scrolling':'Pause scrolling';pause.setAttribute('aria-pressed',String(stopped));}
 pause.addEventListener('click',()=>{stopped=!stopped;state();});state();
 reduced.addEventListener('change',e=>{stopped=e.matches;state();});
 root.addEventListener('mouseenter',()=>hover=true);root.addEventListener('mouseleave',()=>{hover=false;until=Date.now()+2000;});
 root.addEventListener('focusin',()=>focus=true);root.addEventListener('focusout',()=>focus=false);
 ['touchstart','touchmove','wheel','keydown','pointerdown'].forEach(event=>root.addEventListener(event,()=>{until=Date.now()+12000;},{passive:true}));
 const visible=new IntersectionObserver(entries=>{root.dataset.visible=entries[0].isIntersecting?'yes':'no';});visible.observe(root);
 function animate(now){const delta=Math.min(now-last,50);last=now;
  const max=root.scrollHeight-root.clientHeight;
  if(max>1&&!stopped&&!hover&&!focus&&!document.hidden&&root.dataset.visible==='yes'&&Date.now()>until){
   if(Math.abs(root.scrollTop-position)>2)position=root.scrollTop;
   position=Math.max(0,Math.min(max,position+direction*delta*.018));root.scrollTop=position;
   if(position>=max||position<=0){direction*=-1;until=Date.now()+3500;}
  }else position=root.scrollTop;
  requestAnimationFrame(animate);
 }requestAnimationFrame(animate);
 async function refresh(){try{
  const r=await fetch(window.ymcDonorEndpoint,{credentials:'omit'});if(!r.ok)throw Error();const rows=await r.json();if(!Array.isArray(rows))throw Error();
  const next=JSON.stringify(rows);if(next===signature)return;signature=next;
  const frag=document.createDocumentFragment();for(const row of rows){
   const card=text('article','ymc-donor-row',''),avatar=text('span','ymc-donor-avatar',row.name.split(/\s+/).map(s=>s[0]).slice(0,2).join('').toUpperCase());avatar.setAttribute('aria-hidden','true');
   const body=text('div','ymc-donor-body','');body.append(text('strong','ymc-donor-name',row.name));const date=text('time','ymc-donor-time',time(row.date));date.dateTime=row.date;body.append(date);if(row.message)body.append(text('p','ymc-donor-message',row.message));
   card.append(avatar,body);if(row.amount)card.append(text('span','ymc-donor-amount',money(row.amount)));frag.append(card);
  }root.replaceChildren(frag);if(!rows.length)root.append(text('p','','Be the first to support YMC.'));
 }catch{if(!root.childElementCount)root.append(text('p','ymc-donor-error','View the latest donations on Givebutter below.'));}}

 const campaigns=[
  {id:733202,title:'Annual campaign',detail:'Donations & sponsorships',slug:'ymc-annual-campaign'},
  {id:729740,title:'Adults',detail:'Adult tournament',slug:'ymc-adult-player-registration'},
  {id:749484,title:'Teens',detail:'Teen tournament',slug:'ymc-teen-player-registration'},
  {id:725223,title:'Tickets',detail:'General admission & VIP',slug:'ymc-at-mtb-tickets'}
 ];
 function breakdown(d){
  if(d.currency!=='USD'||!Array.isArray(d.campaigns)||d.campaigns.length!==4)throw Error();
  const rows=campaigns.map(c=>{const matches=d.campaigns.filter(r=>Number(r.id)===c.id);if(matches.length!==1||!Number.isFinite(matches[0].raised)||matches[0].raised<0)throw Error();return {...c,raised:matches[0].raised};});
  if(rows.reduce((sum,r)=>sum+Math.round(r.raised*100),0)!==Math.round(d.raised*100))throw Error();
  const cards=document.getElementById('ymc-campaign-cards');if(!cards)return;
  const frag=document.createDocumentFragment();
  rows.forEach(row=>{
   const card=text('a','ymc-campaign-card','');card.href='https://givebutter.com/'+row.slug;
   card.append(text('span','ymc-campaign-card-title',row.title),text('strong','ymc-campaign-card-amount',money(row.raised)),text('span','ymc-campaign-card-detail',row.detail),text('span','ymc-campaign-card-link','View campaign →'));frag.append(card);
  });cards.replaceChildren(frag);
  const stamp=document.getElementById('ymc-breakdown-updated');
  stamp.textContent=Number.isFinite(Date.parse(d.updated_at))?'Updated '+new Date(d.updated_at).toLocaleString('en-US',{timeZone:'America/New_York',month:'short',day:'numeric',hour:'numeric',minute:'2-digit'})+' ET · Included in the total above.':'Included in the total above.';
 }
 async function total(){try{
  const r=await fetch(window.ymcProgressEndpoint||'/wp-json/ymc/v1/campaign-progress',{credentials:'omit'});if(!r.ok)throw Error();const d=await r.json();if(!Number.isFinite(d.raised)||d.raised<0)throw Error();
  breakdown(d);
  document.getElementById('ymc-raised').textContent=money(d.raised);document.getElementById('ymc-goal-progress').value=d.raised;document.getElementById('ymc-goal-percent').textContent=(d.raised/750000*100).toFixed(1)+'%';
 }catch{
  const cards=document.getElementById('ymc-campaign-cards');if(cards&&!cards.querySelector('a'))cards.replaceChildren(text('p','ymc-breakdown-status','Campaign totals are temporarily unavailable. Please check back shortly.'));
  const stamp=document.getElementById('ymc-breakdown-updated');if(cards?.querySelector('a')&&stamp)stamp.textContent='Showing the last available totals. Refreshing automatically.';
  const amount=document.getElementById('ymc-raised');if(amount.textContent==='—')amount.textContent='Support YMC';
 }}
 refresh();total();setInterval(refresh,120000);setInterval(total,120000);
})();
