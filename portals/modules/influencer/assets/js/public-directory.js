(function(){
  'use strict';
  const grid=document.querySelector('.creator-directory-grid');
  if(!grid)return;
  const api='api/public/influencers.php';
  const el=(tag,className,text)=>{const node=document.createElement(tag);if(className)node.className=className;if(text!==undefined)node.textContent=text;return node;};
  function count(value){return new Intl.NumberFormat('en-IN',{notation:'compact',maximumFractionDigits:1}).format(Number(value)||0);}
  function icon(name){const item=el('i','fa-solid '+name);item.setAttribute('aria-hidden','true');return item;}
  function card(creator){
    const article=el('article','creator-card');
    article.append(el('div','creator-card__accent'));
    const body=el('div','creator-card__body');
    const photo=el('div','creator-photo');photo.append(el('span','creator-photo__empty'));body.append(photo);
    const title=el('h2','',creator.full_name+' '),badge=el('span',creator.is_demo?'creator-demo':'creator-verified');badge.title=creator.is_demo?'Fictional demo profile':'Verified creator';if(creator.is_demo)badge.textContent='DEMO';else badge.append(icon('fa-check'));title.append(badge);body.append(title);
    body.append(el('div','creator-brand','@'+creator.handle));
    body.append(el('div','creator-niche',creator.region||'India-wide'));
    const tags=el('div','creator-tags');
    (creator.platforms||[]).slice(0,2).forEach(platform=>tags.append(el('span','creator-tag',platform.name)));
    if(!tags.children.length)tags.append(el('span','creator-tag','Conscious creator'));
    body.append(tags);
    const metrics=el('div','creator-card__metrics'),followers=el('div','creator-metric'),verifiedMetric=el('div','creator-metric');
    const countLabel=el('strong','',count(creator.followers));countLabel.prepend(icon('fa-users'));followers.append(countLabel,document.createTextNode('Reported followers'));
    const verification=el('strong','',creator.is_demo?'Demo account':'Verified');if(!creator.is_demo)verification.prepend(icon('fa-circle-check'));verifiedMetric.append(verification,document.createTextNode('Profile'));
    metrics.append(followers,verifiedMetric);body.append(metrics);
    const link=el('a','bc-btn bc-btn-sm','View Profile ');link.href='influencer-profile.html?handle='+encodeURIComponent(creator.handle);link.append(icon('fa-arrow-right'));body.append(link);article.append(body);return article;
  }
  const cta=document.querySelector('.creator-directory ~ section a[href="become-brand-partner.html"]');
  if(cta){cta.href='portals/modules/influencer/auth/register.php';cta.textContent='Apply to Become a Creator';}
  const programme=document.querySelector('section[aria-label="Become a creator"]');
  if(programme){const intro=programme.querySelector('h2 + p');if(intro)intro.textContent='Partner with conscious makers, share products you believe in, and track eligible commission from approved campaigns.';const claims=programme.querySelectorAll('li');if(claims[0])claims[0].textContent='Commission shown clearly for each approved campaign';if(claims[1])claims[1].textContent='Share trackable links to conscious products';const cards=programme.querySelectorAll('[style*="background:rgba(255,255,255,0.08)"]');[['—','Approved creators'],['Manual','Payout review'],['Clear','Campaign terms'],['Live','Performance insights']].forEach((entry,index)=>{if(!cards[index])return;const value=cards[index].querySelector('div:first-child'),label=cards[index].querySelector('div:last-child');if(value){value.textContent=entry[0];value.removeAttribute('data-count');value.removeAttribute('data-suffix');}if(label)label.textContent=entry[1];});}
  fetch(api,{headers:{Accept:'application/json'},cache:'no-store'}).then(async response=>{const data=await response.json();if(!response.ok)throw Error(data.message||'Directory unavailable.');grid.replaceChildren();if(programme){const metric=programme.querySelector('[style*="background:rgba(255,255,255,0.08)"]');const countValue=metric?.querySelector('div:first-child'),countLabel=metric?.querySelector('div:last-child');if(countValue)countValue.textContent=String(data.creators.length);if(countLabel)countLabel.textContent=data.creators.some(creator=>creator.is_demo)?'Local demo profiles':'Approved creators';}if(!data.creators.length){const empty=el('div','creator-card');empty.style.minHeight='180px';empty.style.padding='30px';empty.append(el('h2','','Creator profiles are coming soon'),el('p','','Approved creators will appear here after review.'));grid.append(empty);return;}data.creators.forEach(creator=>grid.append(card(creator)));}).catch(()=>{grid.replaceChildren();const error=el('div','creator-card');error.style.minHeight='180px';error.style.padding='30px';error.append(el('h2','','Creator directory unavailable'),el('p','','Please try again shortly.'));grid.append(error);});
})();
