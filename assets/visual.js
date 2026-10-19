(function(){
'use strict';
const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
function button(label,fn){const b=document.createElement('button');b.type='button';b.textContent=label;b.addEventListener('click',fn);return b;}
function init(){
 document.querySelectorAll('.pz-slideshow').forEach(root=>{
  const slides=Array.from(root.querySelector('.pz-slides')?.children||[]);if(slides.length<2)return;
  let index=0,timer=null,playing=root.dataset.autoplay==='true'&&!reduced;
  const nav=document.createElement('div');nav.className='pz-slide-controls';nav.setAttribute('aria-label','Slideshow controls');
  const status=document.createElement('span');status.setAttribute('role','status');status.setAttribute('aria-live',playing?'off':'polite');
  const dots=[];let play;
  function show(next){index=(next+slides.length)%slides.length;slides.forEach((s,i)=>{s.hidden=i!==index;s.setAttribute('aria-label','Slide '+(i+1)+' of '+slides.length);});dots.forEach((b,i)=>b.setAttribute('aria-current',String(i===index)));status.textContent=(index+1)+' / '+slides.length;}
  function stopTimer(){if(timer)clearInterval(timer);timer=null;}
  function startTimer(){stopTimer();if(playing&&!document.hidden)timer=setInterval(()=>show(index+1),Math.max(3,Math.min(30,Number(root.dataset.interval)||6))*1000);}
  function pause(){playing=false;stopTimer();if(play)play.textContent='Play';status.setAttribute('aria-live','polite');}
  nav.append(button('Previous',()=>{pause();show(index-1);}),status,button('Next',()=>{pause();show(index+1);}));
  if(root.dataset.autoplay==='true'){play=button(playing?'Pause':'Play',()=>{playing=!playing;play.textContent=playing?'Pause':'Play';status.setAttribute('aria-live',playing?'off':'polite');if(playing)startTimer();else stopTimer();});nav.append(play);}
  if(root.dataset.dots==='true'||root.dataset.thumbnails==='true')slides.forEach((s,i)=>{const b=button('Slide '+(i+1),()=>{pause();show(i);});if(root.dataset.thumbnails==='true'){const img=s.querySelector('img');if(img){const thumb=img.cloneNode();thumb.removeAttribute('srcset');thumb.removeAttribute('sizes');thumb.alt='';thumb.width=64;thumb.height=48;thumb.loading='lazy';b.replaceChildren(thumb);b.setAttribute('aria-label','Slide '+(i+1));}}dots.push(b);nav.append(b);});
  root.append(nav);root.classList.add('pz-enhanced');root.tabIndex=0;root.setAttribute('role','region');root.setAttribute('aria-label','Image slideshow');
  root.addEventListener('keydown',e=>{if(e.target!==root)return;if(['ArrowLeft','ArrowRight','Home','End'].includes(e.key)){e.preventDefault();pause();show(e.key==='Home'?0:e.key==='End'?slides.length-1:index+(e.key==='ArrowRight'?1:-1));}});
  let start;root.addEventListener('touchstart',e=>{start=[e.changedTouches[0].clientX,e.changedTouches[0].clientY];},{passive:true});root.addEventListener('touchend',e=>{if(!start)return;const dx=e.changedTouches[0].clientX-start[0],dy=e.changedTouches[0].clientY-start[1];if(Math.abs(dx)>50&&Math.abs(dx)>Math.abs(dy)){pause();show(index+(dx<0?1:-1));}start=null;},{passive:true});
  root.addEventListener('mouseenter',pause);root.addEventListener('focusin',e=>{if(e.target!==play)pause();});document.addEventListener('visibilitychange',()=>{if(document.hidden)stopTimer();else startTimer();});show(0);startTimer();
 });
 document.querySelectorAll('.pz-before-after').forEach(root=>{const figures=Array.from(root.children).filter(n=>n.tagName==='FIGURE');if(figures.length!==2)return;const [before,after]=figures;const viewport=document.createElement('div');viewport.className='pz-compare-images';root.prepend(viewport);viewport.append(before,after);root.classList.add('pz-enhanced');const label=document.createElement('label');label.textContent='Before / after position ';const range=document.createElement('input');range.type='range';range.min=0;range.max=100;range.value=50;range.setAttribute('aria-label','Before / after position');const update=()=>{after.style.clipPath='inset(0 0 0 '+range.value+'%)';range.setAttribute('aria-valuetext',range.value+'% before');};range.addEventListener('input',update);label.append(range);root.append(label);update();});
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
