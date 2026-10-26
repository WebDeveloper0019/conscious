(function () {
  'use strict';
  var status = document.getElementById('el-status');
  var card = document.getElementById('el-event');
  if (!status || !card) return;
  function el(tag, className, text) { var node = document.createElement(tag); if (className) node.className = className; if (text !== undefined) node.textContent = text; return node; }
  function money(value) { return new Intl.NumberFormat('en-IN', {style:'currency', currency:'INR', maximumFractionDigits:0}).format(Number(value || 0)); }
  function date(value, timezone) { try { return new Intl.DateTimeFormat('en-IN', {dateStyle:'long', timeStyle:'short', timeZone:timezone || 'Asia/Kolkata'}).format(new Date(String(value).replace(' ', 'T') + 'Z')); } catch (_) { return value; } }
  function iconRow(icon, text) { var row=el('p'); var i=el('i','fa-solid '+icon); i.setAttribute('aria-hidden','true'); row.append(i,document.createTextNode(text)); return row; }
  var id = new URLSearchParams(location.search).get('id') || '';
  if (!/^[a-f0-9-]{8,64}$/i.test(id)) { status.textContent='This event link is not valid.'; return; }
  fetch('api/public/events.php?id=' + encodeURIComponent(id), {headers:{Accept:'application/json'}}).then(function (response) { return response.json().catch(function(){ return {}; }).then(function(data){ if(!response.ok) throw new Error(data.message || 'Event details are unavailable.'); return data.event; }); }).then(function (event) {
    document.title = event.title + ' | Conscious Indi';
    var media=el('div','el-media'); var imageId=event.cover_file_asset_id || event.primary_file_asset_id || '';
    if (imageId) { var image=el('img'); image.src='api/files.php?id='+encodeURIComponent(imageId); image.alt=event.title; image.addEventListener('error',function(){ media.replaceChildren(el('i','fa-solid fa-calendar-days')); }); media.append(image); } else media.append(el('i','fa-solid fa-calendar-days'));
    var copy=el('div','el-copy'); copy.append(el('div','el-kicker',String(event.event_type || 'Event').replaceAll('_',' ')),el('h1','',event.title));
    if(event.short_description) copy.append(el('p','el-description',event.short_description));
    var facts=el('div','el-facts'); facts.append(iconRow('fa-calendar-days',date(event.starts_at,event.timezone)),iconRow(event.venue_type==='online'?'fa-video':'fa-location-dot',event.venue_type==='online'?'Online event':(event.venue_name || 'Venue to be announced')),iconRow('fa-user-group','Hosted by '+event.host_name)); copy.append(facts);
    var tickets=el('section','el-tickets'); tickets.append(el('h2','h5','Current ticket options')); var active=Array.isArray(event.ticket_types)?event.ticket_types:[];
    if(!active.length) tickets.append(el('p','el-description','No active ticket options are listed yet.'));
    active.forEach(function(ticket){ var row=el('div','el-ticket'); var label=el('div'); label.append(el('strong','',ticket.name),el('small','',Math.max(0,Number(ticket.available_quantity))+' available')); row.append(label,el('strong','',Number(ticket.price)>0?money(ticket.price):'Free')); tickets.append(row); }); copy.append(tickets);
    var actions=el('div','el-actions'); var book=el('a','bc-btn bc-btn-primary','Sign in to view booking'); book.href='frontend/modules/customer/events.php?event_id='+encodeURIComponent(event.id); var home=el('a','bc-btn bc-btn-outline','Back to home'); home.href='./'; actions.append(book,home); copy.append(actions);
    card.append(media,copy); status.hidden=true; card.hidden=false;
  }).catch(function(error){ status.textContent=error.message || 'Event details are unavailable.'; });
}());
