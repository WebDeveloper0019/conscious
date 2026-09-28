(function(){
  'use strict';
  const home=document.querySelector('.ci-home');
  if(!home||!window.gsap||!window.ScrollTrigger)return;
  gsap.registerPlugin(ScrollTrigger);
  const q=gsap.utils.selector(home);
  gsap.defaults({ease:'power3.out'});

  const intro=gsap.timeline({defaults:{duration:1.05}});
  intro.from(q('.ci-hero .ci-overline'),{y:24,opacity:0})
    .from(q('.ci-hero h1 span'),{yPercent:115,opacity:0,stagger:.13},'-=.72')
    .from(q('.ci-hero-lead'),{y:30,opacity:0},'-=.62')
    .from(q('.ci-hero-actions > *'),{y:25,opacity:0,stagger:.1},'-=.72')
    .from(q('.ci-hero-notes span'),{y:16,opacity:0,stagger:.08},'-=.55')
    .from(q('.ci-scroll-cue'),{opacity:0,y:-15},'-=.5');

  gsap.to('.ci-hero',{backgroundPosition:'54% 70%',ease:'none',scrollTrigger:{trigger:'.ci-hero',start:'top top',end:'bottom top',scrub:1}});
  gsap.to('.orb-one',{x:-130,y:100,rotation:45,ease:'none',scrollTrigger:{trigger:'.ci-hero',start:'top top',end:'bottom top',scrub:1}});
  gsap.to('.orb-two',{x:90,y:-70,rotation:-30,ease:'none',scrollTrigger:{trigger:'.ci-hero',start:'top top',end:'bottom top',scrub:1}});
  gsap.to('.ci-hero-copy',{y:-90,opacity:.12,ease:'none',scrollTrigger:{trigger:'.ci-hero',start:'45% top',end:'bottom top',scrub:1}});

  q('[data-ci-reveal]').forEach(function(el){
    gsap.from(el,{y:70,opacity:0,duration:1.1,scrollTrigger:{trigger:el,start:'top 86%',once:true}});
  });
  q('[data-ci-lines]').forEach(function(el){
    gsap.from(el,{y:80,opacity:0,duration:1.25,scrollTrigger:{trigger:el,start:'top 82%',once:true}});
  });
  gsap.to('.p-one',{y:-90,rotation:2,ease:'none',scrollTrigger:{trigger:'.ci-manifesto',start:'top bottom',end:'bottom top',scrub:1}});
  gsap.to('.p-two',{y:120,rotation:-3,ease:'none',scrollTrigger:{trigger:'.ci-manifesto',start:'top bottom',end:'bottom top',scrub:1}});

  const mm=gsap.matchMedia();
  mm.add('(min-width: 701px)',function(){
    const scenes=q('.ci-story-scene');
    const panels=q('.ci-story-panel');
    const dots=q('.ci-story-dots button');
    gsap.set(scenes,{autoAlpha:0,clipPath:'inset(0 0 100% 0)',scale:1.12});
    gsap.set(scenes[0],{autoAlpha:1,clipPath:'inset(0 0 0% 0)',scale:1});
    gsap.set(panels,{autoAlpha:0,y:70});
    gsap.set(panels[0],{autoAlpha:1,y:0});
    const story=gsap.timeline({
      scrollTrigger:{trigger:'.ci-story',start:'top top',end:'+=3200',pin:true,scrub:1,anticipatePin:1}
    });
    for(let i=1;i<scenes.length;i++){
      const mark='scene'+i;
      story.addLabel(mark)
        .to(panels[i-1],{autoAlpha:0,y:-70,duration:.35},mark)
        .to(scenes[i-1],{autoAlpha:0,scale:1.08,duration:.5},mark)
        .fromTo(scenes[i],{autoAlpha:1,clipPath:'inset(0 0 100% 0)',scale:1.14},{clipPath:'inset(0 0 0% 0)',scale:1,duration:1},mark)
        .to(panels[i],{autoAlpha:1,y:0,duration:.55},mark+'+=.42')
        .set('.ci-story-count b',{textContent:'0'+(i+1)},mark+'+=.45')
        .set(dots,{className:''},mark+'+=.45')
        .set(dots[i],{className:'active'},mark+'+=.45');
    }
    story.to({}, {duration:.6});

    q('.ci-cat').forEach(function(card,i){
      gsap.from(card,{y:110,opacity:0,duration:1.05,delay:(i%2)*.08,scrollTrigger:{trigger:card,start:'top 90%',once:true}});
      gsap.to(card.querySelector('img'),{yPercent:10,ease:'none',scrollTrigger:{trigger:card,start:'top bottom',end:'bottom top',scrub:1}});
    });

    const rail=q('.ci-product-rail')[0];
    gsap.from('.ci-product-heading',{x:-60,opacity:0,scrollTrigger:{trigger:'.ci-product-journey',start:'top 75%',end:'top 35%',scrub:1}});
    gsap.to(rail,{left:function(){return -Math.max(0,rail.scrollWidth-(window.innerWidth*.60));},ease:'none',scrollTrigger:{trigger:'.ci-product-journey',start:'top top',end:function(){return '+='+Math.max(1800,rail.scrollWidth);},pin:true,scrub:1,anticipatePin:1,invalidateOnRefresh:true}});

    gsap.from('.ci-cycle-steps > div',{y:70,opacity:0,stagger:.14,duration:1,scrollTrigger:{trigger:'.ci-cycle-steps',start:'top 82%',once:true}});
  });

  gsap.to('.ci-cycle-bg',{scale:1.16,yPercent:5,ease:'none',scrollTrigger:{trigger:'.ci-cycle',start:'top bottom',end:'bottom top',scrub:1}});
  gsap.to('.ci-final-image',{scale:1.14,yPercent:5,ease:'none',scrollTrigger:{trigger:'.ci-final',start:'top bottom',end:'bottom top',scrub:1}});
  gsap.from('.ci-final-copy > *',{y:65,opacity:0,stagger:.14,duration:1.15,scrollTrigger:{trigger:'.ci-final',start:'top 68%',once:true}});

  q('.ci-impact-stats strong').forEach(function(el){
    const target=parseInt(el.dataset.count||'0',10); if(!target)return;
    const state={value:0},suffix=el.dataset.suffix||'';
    gsap.to(state,{value:target,duration:2,ease:'power2.out',scrollTrigger:{trigger:el,start:'top 88%',once:true},onUpdate:function(){el.textContent=Math.round(state.value).toLocaleString('en-IN')+suffix;}});
  });

  window.addEventListener('load',function(){ScrollTrigger.refresh();},{once:true});
})();
