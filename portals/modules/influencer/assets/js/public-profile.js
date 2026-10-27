(function(){
  'use strict';
  const handle=new URLSearchParams(location.search).get('handle');
  if(!handle)return;
  const section=document.querySelector('.influencer-profile');
  if(!section)return;
  const oldShowcase=document.querySelector('section[aria-label="Creator profile"]:not(.influencer-profile)');
  if(oldShowcase)oldShowcase.hidden=true;
  
  const set=(selector,value)=>{section.querySelectorAll(selector).forEach(node=>{node.textContent=value;});};
<<<<<<< HEAD:frontend/modules/influencer/assets/js/public-profile.js
  
  fetch('api/public/influencers.php?handle='+encodeURIComponent(handle),{headers:{Accept:'application/json'},cache:'no-store'})
  .then(async response=>{
    const data=await response.json();
    if(!response.ok)throw Error(data.message||'Profile unavailable.');
    const creator=data.creator;
    
    document.title=creator.full_name+(creator.is_demo?' (Demo)':'')+' — Conscious Indi Creator';
=======
  const hide=selector=>{const item=section.querySelector(selector);if(item)item.hidden=true;};
  fetch('api/public/influencers.php?handle='+encodeURIComponent(handle),{headers:{Accept:'application/json'},cache:'no-store'}).then(async response=>{const data=await response.json();if(!response.ok)throw Error(data.message||'Profile unavailable.');const creator=data.creator;
    document.title=creator.full_name+(creator.is_demo?' (Demo)':'')+' — Conscious India Creator';
    if(creator.is_demo){const notice=document.createElement('p');notice.className='alert alert-warning';notice.textContent='Fictional local demo profile. Social activity, partnerships and earnings are not real.';section.prepend(notice);}
>>>>>>> main:portals/modules/influencer/assets/js/public-profile.js
    const crumb=document.querySelector('.breadcrumb-item:last-child');if(crumb)crumb.textContent=creator.full_name;
    
    set('[data-profile-field="fullName"]',creator.full_name);
    set('[data-profile-field="brandName"]','@'+creator.handle);
    set('[data-profile-field="about"]',creator.bio||'Conscious creator on Conscious India.');
    set('[data-profile-field="city"]',creator.region||'India-wide');
    set('[data-profile-field="category"]','Conscious living');
    set('[data-profile-field="availability"]',creator.is_demo?'Demo profile':'Open to partnerships');
<<<<<<< HEAD:frontend/modules/influencer/assets/js/public-profile.js
    
    const availabilityStatus=section.querySelector('.ip-availability-status span');
    if(availabilityStatus)availabilityStatus.textContent=creator.is_demo?'Demo profile':'Open to partnerships';

    // --- RENDER MEDIA ---
    // 1. Profile Photo
    const avatar=section.querySelector('[data-profile-field="profileImage"]');
    if(avatar && creator.profile_image_id){
        avatar.src = 'api/files.php?id=' + creator.profile_image_id;
        avatar.hidden=false;
    }

    // 2. Cover Image
    if(creator.cover_image_id){
        const cover=section.querySelector('.ip-cover');
        if(cover){
            cover.style.backgroundImage = `url('api/files.php?id=${creator.cover_image_id}')`;
            cover.style.backgroundSize = 'cover';
            cover.style.backgroundPosition = 'center';
        }
    }

    // 3. Portfolio
    const portfolioSection=section.querySelector('[aria-labelledby="portfolio-title"]');
    if(creator.portfolio_ids && creator.portfolio_ids.length > 0 && portfolioSection){
        portfolioSection.hidden=false;
        const grid=portfolioSection.querySelector('.ip-portfolio-grid');
        if(grid){
            grid.innerHTML = creator.portfolio_ids.map(id => `
                <a class="ip-portfolio-item" href="#" target="_blank">
                    <img src="api/files.php?id=${id}" alt="Portfolio image" />
                </a>
            `).join('');
        }
    }

    // 4. Products
    if((creator.products||[]).length){
        const card=document.createElement('article');card.className='ip-card';
        const heading=document.createElement('h2');heading.className='ip-title';heading.textContent='Curated products';
        const grid=document.createElement('div');grid.style.cssText='display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:12px;margin-top:16px';
        
        creator.products.forEach(product=>{
            const link=document.createElement('a');
            link.href=creator.referral?'api/public/creator-product-referral.php?code='+encodeURIComponent(creator.referral.code)+'&product_id='+encodeURIComponent(product.id):'frontend/modules/customer/product.php?id='+encodeURIComponent(product.id);
            link.style.cssText='display:block;overflow:hidden;border:1px solid #f2e8da;border-radius:10px;color:inherit;text-decoration:none;background:white';
            
            if(product.image_url){
                const image=document.createElement('img');
                image.src=new URL('api/public/'+product.image_url,location.href).href;
                image.alt=product.name;image.loading='lazy';image.style.cssText='width:100%;height:145px;object-fit:cover';
                link.append(image);
            }
            const copy=document.createElement('div');copy.style.padding='10px';
            const name=document.createElement('strong');name.textContent=product.name;
            const price=document.createElement('p');price.style.margin='5px 0 0';price.textContent=new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR'}).format(product.price);
            copy.append(name,price);link.append(copy);grid.append(link);
        });
        
        card.append(heading);
        if(creator.referral){const disclosure=document.createElement('p');disclosure.className='ip-about';disclosure.textContent=creator.referral.disclosure_text;card.append(disclosure);}
        card.append(grid);
        
        const stack=section.querySelector('.ip-stack');
        // Safely insert it before the portfolio section
        if(stack){
            if(portfolioSection) stack.insertBefore(card, portfolioSection);
            else stack.append(card);
        }
    }

    // 5. Stats & Socials
    const stats=section.querySelector('.ip-stat-grid');
    if(stats){
        stats.replaceChildren();
        const item=document.createElement('div');item.className='ip-stat';
        const strong=document.createElement('strong');strong.textContent=new Intl.NumberFormat('en-IN',{notation:'compact',maximumFractionDigits:1}).format(creator.followers||0);
        const label=document.createElement('span');label.textContent='Reported followers';
        item.append(strong,label);stats.append(item);
    }
    
    const tags=section.querySelector('.ip-tag-list');
    if(tags){tags.replaceChildren();(creator.platforms||[]).forEach(platform=>{const tag=document.createElement('span');tag.className='ip-tag';tag.textContent=platform.name;tags.append(tag);});}
    
    const socials=section.querySelector('.ip-social-list');
    if(socials){socials.replaceChildren();(creator.platforms||[]).forEach(platform=>{const link=document.createElement('a');link.className='ip-social';link.href=platform.url;link.target='_blank';link.rel='noopener noreferrer';link.textContent=platform.name+' ↗';socials.append(link);});}
    
    const topSocials=section.querySelector('.ip-hero-socials');
    if(topSocials){topSocials.replaceChildren();(creator.platforms||[]).forEach(platform=>{const link=document.createElement('a');link.href=platform.url;link.target='_blank';link.rel='noopener noreferrer';link.textContent=platform.name;topSocials.append(link);});}
    
    const modalTitle=document.getElementById('contactInfluencerModalTitle');if(modalTitle)modalTitle.textContent='Contact '+creator.full_name;
    const description=document.getElementById('contactInfluencerModalDescription');if(description)description.textContent='Share your project details. Your inquiry will be sent securely to this creator.';
    
    // 6. Contact Form (Real Submission)
    const form=document.getElementById('ip-contact-form');
    if(form) form.addEventListener('submit', async event => {
        event.preventDefault(); 
        event.stopImmediatePropagation();
        form.classList.add('was-validated');
        if(!form.checkValidity()) return;
        
        const btn = form.querySelector('[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
        
        const feedback = document.getElementById('ip-contact-feedback');
        feedback.classList.remove('show', 'alert-danger', 'alert-success');
        
        try {
            const res = await fetch('api/public/influencer-inquiry.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    handle: creator.handle,
                    name: document.getElementById('contact-name').value.trim(),
                    phone: document.getElementById('contact-number').value.trim(),
                    email: document.getElementById('contact-email').value.trim(),
                    package: document.getElementById('contact-service').value,
                    message: document.getElementById('contact-query').value.trim()
                })
            });
            
            const result = await res.json();
            if (!res.ok) throw Error(result.message || 'Could not send inquiry.');
            
            feedback.textContent = result.message;
            feedback.classList.add('alert-success', 'show');
            form.reset();
            form.classList.remove('was-validated');
            
            // Auto-close modal after 3 seconds
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('contactInfluencerModal')).hide();
                feedback.classList.remove('show');
            }, 3000);
            
        } catch (error) {
            feedback.textContent = error.message;
            feedback.classList.add('alert-danger', 'show');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }, true);

  }).catch(error => {
    console.error("Profile Error:", error);
    document.title='Creator unavailable — Conscious Indi';
    const crumb=document.querySelector('.breadcrumb-item:last-child');if(crumb)crumb.textContent='Creator unavailable';
    section.replaceChildren();
    const message=document.createElement('div');message.className='container';message.style.padding='50px 20px';
    message.textContent='This creator profile is not available. (Error: '+error.message+')';
    section.append(message);
  });
})(); 
=======
    const availabilityStatus=section.querySelector('.ip-availability-status span');if(availabilityStatus)availabilityStatus.textContent=creator.is_demo?'Demo profile':'Open to partnerships';
    if(creator.is_demo){const contactButton=section.querySelector('button[data-bs-target="#contactInfluencerModal"]');if(contactButton)contactButton.textContent='Send demo inquiry';}
    const avatar=section.querySelector('[data-profile-field="profileImage"]');if(avatar){avatar.hidden=true;}
    const portfolio=section.querySelector('[aria-labelledby="portfolio-title"]');if(portfolio)portfolio.hidden=true;
    if((creator.products||[]).length){const card=document.createElement('article');card.className='ip-card';const heading=document.createElement('h2');heading.className='ip-title';heading.textContent='Curated products';const grid=document.createElement('div');grid.style.cssText='display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:12px;margin-top:16px';creator.products.forEach(product=>{const link=document.createElement('a');link.href=creator.referral?'api/public/creator-product-referral.php?code='+encodeURIComponent(creator.referral.code)+'&product_id='+encodeURIComponent(product.id):'portals/modules/customer/product.php?id='+encodeURIComponent(product.id);link.style.cssText='display:block;overflow:hidden;border:1px solid #f2e8da;border-radius:10px;color:inherit;text-decoration:none;background:white';if(product.image_url){const image=document.createElement('img');image.src=new URL('api/public/'+product.image_url,location.href).href;image.alt=product.name;image.loading='lazy';image.style.cssText='width:100%;height:145px;object-fit:cover';link.append(image);}const copy=document.createElement('div');copy.style.padding='10px';const name=document.createElement('strong');name.textContent=product.name;const price=document.createElement('p');price.style.margin='5px 0 0';price.textContent=new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR'}).format(product.price);copy.append(name,price);link.append(copy);grid.append(link);});card.append(heading);if(creator.referral){const disclosure=document.createElement('p');disclosure.className='ip-about';disclosure.textContent=creator.referral.disclosure_text;card.append(disclosure);}card.append(grid);const stack=section.querySelector('.ip-stack');if(stack)stack.insertBefore(card,portfolio?portfolio.nextSibling:null);}
    const stats=section.querySelector('.ip-stat-grid');if(stats){stats.replaceChildren();const item=document.createElement('div');item.className='ip-stat';const strong=document.createElement('strong');strong.textContent=new Intl.NumberFormat('en-IN',{notation:'compact',maximumFractionDigits:1}).format(creator.followers||0);const label=document.createElement('span');label.textContent='Reported followers';item.append(strong,label);stats.append(item);}
    const tags=section.querySelector('.ip-tag-list');if(tags){tags.replaceChildren();(creator.platforms||[]).forEach(platform=>{const tag=document.createElement('span');tag.className='ip-tag';tag.textContent=platform.name;tags.append(tag);});}
    const socials=section.querySelector('.ip-social-list');if(socials){socials.replaceChildren();(creator.platforms||[]).forEach(platform=>{const link=document.createElement('a');link.className='ip-social';link.href=platform.url;link.target='_blank';link.rel='noopener noreferrer';link.textContent=platform.name+' ↗';socials.append(link);});}
    const topSocials=section.querySelector('.ip-hero-socials');if(topSocials){topSocials.replaceChildren();(creator.platforms||[]).forEach(platform=>{const link=document.createElement('a');link.href=platform.url;link.target='_blank';link.rel='noopener noreferrer';link.textContent=platform.name;topSocials.append(link);});}
    const modalTitle=document.getElementById('contactInfluencerModalTitle');if(modalTitle)modalTitle.textContent='Contact '+creator.full_name;
    const description=document.getElementById('contactInfluencerModalDescription');if(description)description.textContent='Share your project details. Your inquiry will be sent securely to this creator.';
    const form=document.getElementById('ip-contact-form');if(form)form.addEventListener('submit',async event=>{event.preventDefault();event.stopImmediatePropagation();form.classList.add('was-validated');if(!form.checkValidity())return;const button=form.querySelector('[type="submit"]');button.disabled=true;try{const response=await fetch('api/public/influencer-inquiry.php',{method:'POST',headers:{Accept:'application/json','Content-Type':'application/json'},body:JSON.stringify({handle:creator.handle,name:document.getElementById('contact-name').value.trim(),phone:document.getElementById('contact-number').value.trim(),email:document.getElementById('contact-email').value.trim(),package:document.getElementById('contact-service').value,message:document.getElementById('contact-query').value.trim()})});const result=await response.json();if(!response.ok)throw Error(result.message||'Could not send your inquiry.');const feedback=document.getElementById('ip-contact-feedback');feedback.textContent=result.message;feedback.classList.remove('alert-danger');feedback.classList.add('alert-success','show');form.reset();form.classList.remove('was-validated');}catch(error){const feedback=document.getElementById('ip-contact-feedback');feedback.textContent=error.message;feedback.classList.remove('alert-success');feedback.classList.add('alert-danger','show');}finally{button.disabled=false;}},true);
  }).catch(()=>{document.title='Creator unavailable — Conscious India';const crumb=document.querySelector('.breadcrumb-item:last-child');if(crumb)crumb.textContent='Creator unavailable';section.replaceChildren();const message=document.createElement('div');message.className='container';message.style.padding='50px 20px';message.textContent='This creator profile is not available.';section.append(message);});
})();
>>>>>>> main:portals/modules/influencer/assets/js/public-profile.js
