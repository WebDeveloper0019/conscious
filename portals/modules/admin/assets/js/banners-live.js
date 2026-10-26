(() => {
  'use strict';
  const api='../../../api/admin/';
  const list=document.getElementById('ah-list');
  const message=document.getElementById('ah-message');
  const dialog=document.getElementById('ah-dialog');
  const form=document.getElementById('ah-form');
  const preview=document.getElementById('ah-preview');
  const save=document.getElementById('ah-save');
  const formMessage=document.getElementById('ah-form-message');
  let csrf='';
  let banners=[];
  async function request(path,method='GET',body=null,isFile=false){
    const response=await fetch(api+path,{method,credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json',...(method!=='GET'?{'X-CSRF-Token':csrf}:{}),...(!isFile&&body?{'Content-Type':'application/json'}:{})},body:body?(isFile?body:JSON.stringify(body)):undefined});
    const data=await response.json().catch(()=>({}));
    if(!response.ok||!data.success)throw Error(data.message||'The banner request could not be completed.');
    return data;
  }
  function status(text,error=false){message.textContent=text;message.classList.toggle('error',error);}
  function text(tag,value,className){const node=document.createElement(tag);node.textContent=value;if(className)node.className=className;return node;}
  function render(){
    list.replaceChildren();
    if(!banners.length){list.append(text('p','No banners yet. Add a slide to start the home page carousel.'));return;}
    banners.forEach(banner=>{
      const item=document.createElement('article');item.className='ah-item';
      const image=document.createElement('img');image.src=banner.image_url;image.alt=banner.image_alt;image.loading='lazy';
      const copy=document.createElement('div');copy.className='ah-item-copy';copy.append(text('small',banner.eyebrow),text('h3',banner.title),text('p',banner.description),text('span','Order '+banner.sort_order+' · '+(Number(banner.is_active)===1?'Visible':'Hidden'),'ah-state'));
      const actions=document.createElement('div');actions.className='ah-item-actions';
      const edit=text('button','Edit','ac-btn small');edit.type='button';edit.addEventListener('click',()=>open(banner));
      const toggle=text('button',Number(banner.is_active)===1?'Hide':'Show','ac-btn small');toggle.type='button';toggle.addEventListener('click',async()=>{toggle.disabled=true;try{await request('home-hero.php','PUT',{...banner,is_active:Number(banner.is_active)!==1});await load();status('Banner visibility updated.');}catch(error){status(error.message,true);toggle.disabled=false;}});
      const remove=text('button','Remove','ac-btn small ah-remove');remove.type='button';remove.addEventListener('click',async()=>{if(!confirm('Remove this banner permanently?'))return;remove.disabled=true;try{await request('home-hero.php','DELETE',{id:banner.id});await load();status('Banner removed.');}catch(error){status(error.message,true);remove.disabled=false;}});
      actions.append(edit,toggle,remove);item.append(image,copy,actions);list.append(item);
    });
  }
  function open(banner=null){
    form.reset();
    formMessage.hidden=true;
    form.elements.id.value=banner?.id||'';
    for(const key of ['eyebrow','title','description','button_label','button_url','image_url','image_alt','sort_order'])if(banner&&form.elements[key])form.elements[key].value=banner[key]??'';
    if(!banner){form.elements.button_url.value='/conscious/shop';form.elements.sort_order.value=100;}
    form.elements.is_active.checked=!banner||Number(banner.is_active)===1;
    form.elements.image.value='';
    preview.hidden=!banner?.image_url;
    if(banner?.image_url)preview.src=banner.image_url;else preview.removeAttribute('src');
    document.getElementById('ah-dialog-title').textContent=banner?'Edit banner':'Add banner';
    dialog.showModal();
  }
  async function load(){const result=await request('home-hero.php');banners=result.banners||[];render();status(banners.length+' banner'+(banners.length===1?'':'s')+' available.');}
  document.getElementById('ah-add').addEventListener('click',()=>open());
  document.getElementById('ah-close').addEventListener('click',()=>dialog.close());
  document.getElementById('ah-cancel').addEventListener('click',()=>dialog.close());
  form.elements.image.addEventListener('change',()=>{const file=form.elements.image.files[0];if(!file)return;preview.src=URL.createObjectURL(file);preview.hidden=false;});
  form.addEventListener('submit',async event=>{
    event.preventDefault();if(!form.reportValidity())return;
    save.disabled=true;save.textContent='Saving…';
    try{
      let imageUrl=form.elements.image_url.value;
      const file=form.elements.image.files[0];
      if(file){const files=new FormData();files.append('image',file);imageUrl=(await request('home-hero-image.php','POST',files,true)).image_url;}
      if(!imageUrl)throw Error('Choose a banner image.');
      const payload={id:form.elements.id.value,eyebrow:form.elements.eyebrow.value.trim(),title:form.elements.title.value.trim(),description:form.elements.description.value.trim(),button_label:form.elements.button_label.value.trim(),button_url:form.elements.button_url.value.trim(),image_url:imageUrl,image_alt:form.elements.image_alt.value.trim(),sort_order:Number(form.elements.sort_order.value),is_active:form.elements.is_active.checked};
      await request('home-hero.php',payload.id?'PUT':'POST',payload);
      dialog.close();await load();status('Banner saved. The home page will show the latest published slides.');
    }catch(error){formMessage.textContent=error.message;formMessage.hidden=false;}
    finally{save.disabled=false;save.textContent='Save banner';}
  });
  request('auth/me.php').then(result=>{csrf=result.csrf_token||'';if(!result.permissions?.includes('content.manage'))throw Error('You do not have permission to manage banners.');return load();}).catch(error=>status(error.message,true));
})();
