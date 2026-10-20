// Adapted from the supplied Farm 1.2.6 controller. Native scroll only.
(()=>{
const track=document.querySelector('.ymc-stadium-scroll');if(!track)return;const stage=track.querySelector('.ymc-stadium-stage'),world=track.querySelector('.ymc-stadium-world'),opening=track.querySelector('.ymc-stadium-opening'),shade=track.querySelector('.ymc-stadium-shade'),cue=track.querySelector('.ymc-stadium-cue'),aperture=track.querySelector('.ymc-stadium-aperture'),reveal=track.querySelector('.ymc-stadium-reveal'),cards=[...track.querySelectorAll('.ymc-stadium-reveal-card')],progress=track.querySelector('.ymc-stadium-progress span');

// Normalize the saved WordPress hero as well as the theme pattern.
const deck=opening.querySelector('.ymc-stadium-deck'),counter=opening.querySelector('.ymc-stadium-countdown');
if(deck && counter && deck.textContent.includes('Baltimore, Maryland')){
 const dateText=deck.textContent.split('Baltimore, Maryland')[0].trim();
 const inlineCounter=document.createElement('span');
 inlineCounter.className=counter.className;inlineCounter.dataset.ymcEventDate=counter.dataset.ymcEventDate;inlineCounter.hidden=true;
 deck.replaceChildren(document.createTextNode(dateText),document.createElement('br'),inlineCounter);counter.remove();
}
// Count calendar days in Baltimore, never infer an unconfirmed kickoff time.
const eventCounters = track.querySelectorAll('[data-ymc-event-date]');
function updateEventDays(now = new Date()) {
 const today = new Intl.DateTimeFormat('en-CA', {timeZone:'America/New_York',year:'numeric',month:'2-digit',day:'2-digit'}).formatToParts(now);
 const part = type => Number(today.find(p => p.type === type).value);
 const todayUTC = Date.UTC(part('year'), part('month') - 1, part('day'));
 eventCounters.forEach(el => {
  const [year,month,day] = el.dataset.ymcEventDate.split('-').map(Number);
  const days = Math.round((Date.UTC(year,month - 1,day) - todayUTC) / 86400000);
  el.hidden = days < 0;
  el.textContent = days === 0 ? 'GAME DAY IS HERE' : `${days} ${days === 1 ? 'DAY' : 'DAYS'} UNTIL GAME DAY`;
 });
}
updateEventDays();
setInterval(updateEventDays, 60000);
const motion=matchMedia('(prefers-reduced-motion: reduce)');let geometry,pending=false,previous=-1,interactive=true,onScreen=null,shown=null,openShown=null,lastWidth=innerWidth,lastHeight=innerHeight,resizeTimer=0;
const clamp=n=>Math.max(0,Math.min(1,n));const ease=n=>{n=clamp(n);return n*n*(3-2*n)};
// One detailed close-up, drawn at its visible size to avoid compositor upscaling.
const detail=document.createElement('div');detail.className='ymc-tunnel-detail';detail.setAttribute('aria-hidden','true');
const detailShell=document.createElement('img');detailShell.className='ymc-detail-shell';detailShell.alt='';detailShell.decoding='async';
const detailAperture=detailShell.cloneNode();detailAperture.className='ymc-detail-aperture';
const detailURL=new URL('../images/stadium-tunnel-detail.png',document.currentScript.src).href;
detail.append(detailShell,detailAperture);stage.appendChild(detail);
const ready=()=>{previous=-1;requestFrame()};detailShell.addEventListener('load',ready);detailAperture.addEventListener('load',ready);detailShell.src=detailURL;detailAperture.src=detailURL;
function measure(){const headerEl=document.querySelector('[data-ymc-section="0"]');if(headerEl){/* Pin the stage exactly where the header ends once it is stuck, which is its   own sticky offset plus its height. Counting the admin bar on top of that   left a 32px strip of background showing between the two. */const stickTop=parseFloat(getComputedStyle(headerEl).top)||0;track.style.setProperty('--header-height',(stickTop+headerEl.offsetHeight)+'px')}const w=stage.clientWidth,h=stage.clientHeight,iw=Math.max(w,h*1.5),ih=iw/1.5;geometry={iw,ih,distance:Math.max(1,track.offsetHeight-h),header:parseFloat(getComputedStyle(track).getPropertyValue('--header-height')),zoom:Math.max(w/(iw*.13),h/(ih*.141))*1.12};world.style.width=iw+'px';world.style.height=ih+'px';previous=-1;requestFrame()}
function render(){pending=false;if(motion.matches||!geometry)return;/* One layout read a frame instead of two. */const rect=track.getBoundingClientRect();const near=rect.bottom>geometry.header+100;/* Writing the body class re-runs every :has() rule keyed to it, so only   touch it when it actually changes. */if(near!==onScreen){onScreen=near;document.body.classList.toggle('ymc-stadium-active',near)}const p=clamp((geometry.header-rect.top)/geometry.distance);if(Math.abs(previous-p)<.0001)return;previous=p;const approach=ease((p-.05)/.53),copyOut=ease(p/.21),enter=ease((p-.52)/.27),sceneOut=ease((p-.73)/.14),zoom=1+(geometry.zoom-1)*approach;
world.style.transform=`translate(-50%,-50%) translate(${geometry.iw*.001*approach}px,${-geometry.ih*.1915*approach}px) scale(${zoom})`;world.style.opacity=1-sceneOut;
const wr=world.getBoundingClientRect(),sr=stage.getBoundingClientRect();
Object.assign(detail.style,{left:(wr.left-sr.left+wr.width*.39074)+'px',top:(wr.top-sr.top+wr.height*.58717)+'px',width:(wr.width*.21739)+'px',height:(wr.height*.21013)+'px',opacity:detailShell.naturalWidth&&detailAperture.naturalWidth?ease((p-.11)/.13)*(1-sceneOut):0});
detailAperture.style.opacity=1-ease((p-.30)/.22);aperture.style.opacity=1-ease((p-.30)/.22);opening.style.opacity=1-copyOut;opening.style.transform=`translateY(${-70*copyOut}px) scale(${1-.07*copyOut})`;const ov=copyOut>=1?'hidden':'visible';if(ov!==openShown){openShown=ov;opening.style.visibility=ov}shade.style.opacity=1-ease(p/.3);cue.style.opacity=1-ease(p/.09);const rv=p>.3?'visible':'hidden';if(rv!==shown){shown=rv;reveal.style.visibility=rv}reveal.style.opacity=ease((p-.31)/.22);reveal.style.transform=`scale(${.82+.18*enter})`;cards.forEach((card,i)=>{const c=i-(cards.length-1)/2,n=ease((p-.49-i*.015)/.26);card.style.transform=`translate3d(${c*70*(1-n)}px,${(1-n)*(160+Math.abs(c)*70)}px,0) rotateY(${c*20*(1-n)}deg)`});const active=p>.74;if(interactive!==active){interactive=active;reveal.inert=!active;reveal.setAttribute('aria-hidden',String(!active))}progress.style.transform=`scaleX(${p})`;track.dataset.progress=p.toFixed(3)}
function requestFrame(){if(!pending){pending=true;requestAnimationFrame(render)}}function configure(){if(motion.matches){onScreen=false;shown=null;openShown=null;document.body.classList.remove('ymc-stadium-active');delete track.dataset.motion;delete track.dataset.progress;[world,opening,shade,cue,aperture,reveal,detail,detailAperture,...cards,progress].forEach(el=>el.removeAttribute('style'));reveal.inert=false;reveal.setAttribute('aria-hidden','false');interactive=true;geometry=null}else{track.dataset.motion='ready';measure()}}
addEventListener('scroll',requestFrame,{passive:true});addEventListener('resize',()=>{if(motion.matches)return;/* On a phone, scrolling shows and hides the address bar, which fires resize   constantly. Re-measuring on each one makes the intro jump, so only a real   change of width or a large change of height counts. */const dw=Math.abs(innerWidth-lastWidth),dh=Math.abs(innerHeight-lastHeight);if(dw===0&&dh<120)return;lastWidth=innerWidth;lastHeight=innerHeight;clearTimeout(resizeTimer);resizeTimer=setTimeout(measure,120)},{passive:true});if(document.fonts&&document.fonts.ready)document.fonts.ready.then(()=>{if(!motion.matches)measure()});addEventListener('pageshow',()=>{if(!motion.matches)measure()});motion.addEventListener('change',configure);configure();
})();
