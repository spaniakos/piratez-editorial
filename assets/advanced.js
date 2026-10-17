(function(){
'use strict';
function init(){
 document.querySelectorAll('.pz-map').forEach(root=>{
  const load=root.querySelector('.pz-load-map'),holder=root.querySelector('.pz-map-frame');if(!load||!holder)return;
  const reset=()=>{holder.replaceChildren();load.hidden=false;};
  load.addEventListener('click',()=>{const url=new URL(root.dataset.mapSrc);if(!['https://www.openstreetmap.org/export/embed.html','https://maps.google.com/maps'].includes(url.origin+url.pathname))return;const frame=document.createElement('iframe');frame.title=root.dataset.mapLabel||'Location map';frame.src=url.href;frame.loading='lazy';frame.referrerPolicy='no-referrer';frame.setAttribute('sandbox','allow-scripts allow-same-origin allow-popups');holder.replaceChildren(frame);load.hidden=true;const revoke=document.createElement('button');revoke.type='button';revoke.textContent='Unload map';revoke.addEventListener('click',reset);holder.append(revoke);});
  // PCC has analytics/ads categories, not an external-media category. Neither grants map permission.
  // Any withdrawal clears one-time map permission; reopening requires an explicit map click.
  document.addEventListener('pcc:changed',event=>{if(!event.detail?.analytics||!event.detail?.ads)reset();});
 });
 document.querySelectorAll('.pz-term[data-definition]').forEach((term,i)=>{
  const definition=document.createElement('span');definition.id='pz-term-definition-'+i;definition.className='pz-term-definition';definition.textContent=term.dataset.definition;definition.hidden=true;term.after(definition);term.tabIndex=0;term.setAttribute('role','button');term.setAttribute('aria-expanded','false');term.setAttribute('aria-controls',definition.id);
  const toggle=()=>{definition.hidden=!definition.hidden;term.setAttribute('aria-expanded',String(!definition.hidden));if(!definition.hidden)term.setAttribute('aria-describedby',definition.id);else term.removeAttribute('aria-describedby');};term.addEventListener('click',toggle);term.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();toggle();}if(e.key==='Escape'&&!definition.hidden)toggle();});
 });
 const content=document.querySelector('.pz-editorial-content');if(!content)return;
 const config=window.pzEditorial||{};
 if(config.preferences){const controls=document.createElement('div');controls.className='pz-reading-controls';controls.setAttribute('aria-label','Reading preferences');const font=document.createElement('input');font.type='range';font.min=90;font.max=150;font.step=10;font.value=100;const line=document.createElement('input');line.type='range';line.min=14;line.max=24;line.step=1;line.value=17;const fontLabel=document.createElement('label');fontLabel.textContent='Text size ';fontLabel.append(font);const lineLabel=document.createElement('label');lineLabel.textContent='Line spacing ';lineLabel.append(line);const update=()=>{content.style.fontSize=font.value+'%';content.style.lineHeight=String(Number(line.value)/10);try{localStorage.setItem('pz-editorial-reading',JSON.stringify({font:Number(font.value),line:Number(line.value)}));}catch(e){}};font.addEventListener('input',update);line.addEventListener('input',update);try{const saved=JSON.parse(localStorage.getItem('pz-editorial-reading'));if(saved){font.value=Math.max(90,Math.min(150,Number(saved.font)||100));line.value=Math.max(14,Math.min(24,Number(saved.line)||17));update();}}catch(e){}const reset=document.createElement('button');reset.type='button';reset.textContent='Reset';reset.addEventListener('click',()=>{font.value=100;line.value=17;update();});controls.append(fontLabel,lineLabel,reset);content.before(controls);}
 if(config.print){const b=document.createElement('button');b.type='button';b.className='pz-print';b.textContent='Print article';b.addEventListener('click',()=>window.print());content.before(b);}
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
