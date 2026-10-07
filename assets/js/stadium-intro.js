// Adapted from the supplied Farm 1.2.6 controller. Native scroll only.
(()=>{
const track=document.querySelector('.ymc-stadium-scroll');if(!track)return;const stage=track.querySelector('.ymc-stadium-stage'),world=track.querySelector('.ymc-stadium-world'),opening=track.querySelector('.ymc-stadium-opening'),shade=track.querySelector('.ymc-stadium-shade'),cue=track.querySelector('.ymc-stadium-cue'),aperture=track.querySelector('.ymc-stadium-aperture'),reveal=track.querySelector('.ymc-stadium-reveal'),cards=[...track.querySelectorAll('.ymc-stadium-reveal-card')],progress=track.querySelector('.ymc-stadium-progress span');

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
const motion=matchMedia('(prefers-reduced-motion: reduce)');let geometry,pending=false,previous=-1,interactive=true;
const clamp=n=>Math.max(0,Math.min(1,n));const ease=n=>{n=clamp(n);return n*n*(3-2*n)};
function measure(){const headerEl=document.querySelector('[data-ymc-section="0"]');if(headerEl){const admin=document.querySelector('#wpadminbar');track.style.setProperty('--header-height',(headerEl.offsetHeight+(admin?admin.offsetHeight:0))+'px')}const w=stage.clientWidth,h=stage.clientHeight,iw=Math.max(w,h*1.5),ih=iw/1.5;geometry={iw,ih,distance:track.offsetHeight-h,header:parseFloat(getComputedStyle(track).getPropertyValue('--header-height')),zoom:Math.max(w/(iw*.13),h/(ih*.141))*1.12};world.style.width=iw+'px';world.style.height=ih+'px';previous=-1;requestFrame()}
function render(){pending=false;if(motion.matches||!geometry)return;document.body.classList.toggle('ymc-stadium-active',track.getBoundingClientRect().bottom>geometry.header+100);const p=clamp((geometry.header-track.getBoundingClientRect().top)/geometry.distance);if(Math.abs(previous-p)<.0001)return;previous=p;const approach=ease((p-.05)/.53),copyOut=ease(p/.21),enter=ease((p-.52)/.27),sceneOut=ease((p-.73)/.14),zoom=1+(geometry.zoom-1)*approach;
world.style.transform=`translate(-50%,-50%) translate(${geometry.iw*.001*approach}px,${-geometry.ih*.1915*approach}px) scale(${zoom})`;world.style.opacity=1-sceneOut;aperture.style.opacity=1-ease((p-.30)/.22);opening.style.opacity=1-copyOut;opening.style.transform=`translateY(${-70*copyOut}px) scale(${1-.07*copyOut})`;opening.style.visibility=copyOut>=1?'hidden':'visible';shade.style.opacity=1-ease(p/.3);cue.style.opacity=1-ease(p/.09);reveal.style.visibility=p>.3?'visible':'hidden';reveal.style.opacity=ease((p-.31)/.22);reveal.style.transform=`scale(${.82+.18*enter})`;cards.forEach((card,i)=>{const c=i-(cards.length-1)/2,n=ease((p-.49-i*.015)/.26);card.style.transform=`translate3d(${c*70*(1-n)}px,${(1-n)*(160+Math.abs(c)*70)}px,0) rotateY(${c*20*(1-n)}deg)`});const active=p>.74;if(interactive!==active){interactive=active;reveal.inert=!active;reveal.setAttribute('aria-hidden',String(!active))}progress.style.transform=`scaleX(${p})`;track.dataset.progress=p.toFixed(3)}
function requestFrame(){if(!pending){pending=true;requestAnimationFrame(render)}}function configure(){if(motion.matches){document.body.classList.remove('ymc-stadium-active');delete track.dataset.motion;delete track.dataset.progress;[world,opening,shade,cue,aperture,reveal,...cards,progress].forEach(el=>el.removeAttribute('style'));reveal.inert=false;reveal.setAttribute('aria-hidden','false');interactive=true;geometry=null}else{track.dataset.motion='ready';measure()}}
addEventListener('scroll',requestFrame,{passive:true});addEventListener('resize',()=>{if(!motion.matches)measure()},{passive:true});addEventListener('pageshow',()=>{if(!motion.matches)measure()});motion.addEventListener('change',configure);configure();
})();
