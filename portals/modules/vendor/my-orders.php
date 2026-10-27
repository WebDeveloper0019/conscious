// data.orders.forEach(o => { ... }) ke andar
const timeline = (o.history || []).map(h => `
    <div style="font-size:0.75rem; border-left: 2px solid #ccc; padding-left: 8px; margin-bottom: 6px;">
        <strong>${h.status.replace('_', ' ').toUpperCase()}</strong> - ${h.notes} <br>
        <span class="text-muted">${h.created_at}</span>
    </div>
`).join('');

const deliverablesList = (o.deliverables || []).map(d => `<li>${d}</li>`).join('');

// Card ke HTML mein yeh add karein:
<div class="mt-3 mb-2">
    <strong class="small text-dark">Deliverables:</strong>
    <ul class="small text-muted mb-0 pl-3">${deliverablesList}</ul>
</div>

<div class="mt-3">
    <button class="btn btn-sm btn-outline-secondary w-100" data-bs-toggle="collapse" data-bs-target="#history-${o.id}">View Milestones & History</button>
    <div class="collapse mt-2 p-2 bg-light rounded" id="history-${o.id}">
        ${timeline || 'No history yet.'}
    </div>
</div>