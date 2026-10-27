(() => {
  'use strict';
  const root=document.getElementById('home-hero');
  if(!root)return;
  const slides=document.getElementById('ci-hero-slides');
  const controls=document.getElementById('ci-hero-controls');
  const dots=document.getElementById('ci-hero-dots');
  const progress=document.getElementById('ci-hero-progress');
  let index=0;
  let timer=null;
  let pointerPaused=false;
  let focusPaused=false;
  const autoplayDelay=5000;
  function element(tag,className,content){const node=document.createElement(tag);if(className)node.className=className;if(content)node.textContent=content;return node;}
  function show(position){
    const all=Array.from(slides.children);if(!all.length)return;
    index=(position+all.length)%all.length;
    all.forEach((slide,i)=>{slide.classList.toggle('is-active',i===index);slide.setAttribute('aria-hidden',i===index?'false':'true');slide.querySelectorAll('a').forEach(link=>{link.tabIndex=i===index?0:-1;});});
    const nextImage=all[(index+1)%all.length].querySelector('img');
    if(nextImage)nextImage.loading='eager';
    dots.querySelectorAll('button').forEach((button,i)=>button.setAttribute('aria-current',i===index?'true':'false'));
    progress.textContent=String(index+1).padStart(2,'0')+' / '+String(all.length).padStart(2,'0');
  }
  function stop(){if(timer){clearInterval(timer);timer=null;}}
  function isPaused(){return pointerPaused||focusPaused||document.hidden;}
  function start(){stop();if(slides.children.length<2||isPaused())return;timer=setInterval(()=>show(index+1),autoplayDelay);}
  function render(data){
    slides.replaceChildren();dots.replaceChildren();
    if(!data.length){
      const slide=element('div','ci-hero-slide');
      const content=element('div','ci-hero-content');const copy=element('div','ci-hero-copy');
      copy.append(element('p','ci-overline','CONSCIOUS INDIA'),element('h1','', 'Discover thoughtful things.'));
      const link=element('a','ci-btn ci-btn-primary','Explore the marketplace →');link.href='/conscious/shop';copy.append(link);content.append(copy);slide.append(content);slides.append(slide);show(0);controls.hidden=true;return;
    }
    data.forEach((banner,i)=>{
      const slide=element('article','ci-hero-slide');
      const image=element('img');image.src=banner.image_url;image.alt=banner.image_alt;image.loading=i===0?'eager':'lazy';image.decoding='async';
      if(i===0)image.fetchPriority='high';
      const shade=element('div','ci-hero-shade');shade.setAttribute('aria-hidden','true');
      const content=element('div','ci-hero-content');const copy=element('div','ci-hero-copy');
      copy.append(element('p','ci-overline',banner.eyebrow),element(i===0?'h1':'h2','',banner.title),element('p','ci-hero-lead',banner.description));
      const action=element('a','ci-btn ci-btn-primary',banner.button_label+'  →');action.href=banner.button_url;copy.append(action);content.append(copy);slide.append(image,shade,content);slides.append(slide);
      const dot=element('button');dot.type='button';dot.setAttribute('aria-label','Show banner '+(i+1)+': '+banner.title);dot.addEventListener('click',()=>{show(i);start();});dots.append(dot);
    });
    controls.hidden=data.length<2;show(0);start();
  }
  document.getElementById('ci-hero-prev').addEventListener('click',()=>{show(index-1);start();});
  document.getElementById('ci-hero-next').addEventListener('click',()=>{show(index+1);start();});
  root.addEventListener('pointerenter',()=>{pointerPaused=true;stop();});
  root.addEventListener('pointerleave',()=>{pointerPaused=false;start();});
  root.addEventListener('focusin',()=>{focusPaused=true;stop();});
  root.addEventListener('focusout',event=>{if(!root.contains(event.relatedTarget)){focusPaused=false;start();}});
  root.addEventListener('keydown',event=>{if(event.key==='ArrowRight'){show(index+1);event.preventDefault();}if(event.key==='ArrowLeft'){show(index-1);event.preventDefault();}});
  document.addEventListener('visibilitychange',start);
  fetch('api/public/home-hero.php',{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}}).then(async response=>{const data=await response.json();if(!response.ok||!data.success)throw Error(data.message||'Banner content is unavailable.');return data.banners||[];}).then(render).catch(()=>render([]));
})();
