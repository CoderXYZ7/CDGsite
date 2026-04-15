let events = [];

const API_URL = '../../api/events.php';

// --- Toast notifications ---
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle'}"></i> ${message}`;
    container.appendChild(toast);
    // Trigger animation
    requestAnimationFrame(() => toast.classList.add('toast-visible'));
    setTimeout(() => {
        toast.classList.remove('toast-visible');
        toast.addEventListener('transitionend', () => toast.remove());
    }, 3500);
}

// --- Load events from API ---
async function loadEvents() {
    try {
        const response = await fetch(API_URL);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        events = data.map(event => ({
            ...event,
            date: new Date(event.date),
            end_date: event.end_date ? new Date(event.end_date) : null
        }));
        displayEvents();
    } catch (error) {
        console.error('Errore nel caricamento degli eventi:', error);
        showToast('Errore nel caricamento degli eventi.', 'error');
    }
}

// --- Toggle end date/time fields ---
function toggleEndDateTime() {
    const eventType = document.querySelector('input[name="event-type"]:checked').value;
    const section = document.getElementById('end-datetime-section');
    if (eventType === 'continuous') {
        section.style.display = 'block';
    } else {
        section.style.display = 'none';
        document.getElementById('event-end-date').value = '';
        document.getElementById('event-end-time').value = '';
    }
}

// --- Date formatting helpers ---
function formatDate(date) {
    if (!(date instanceof Date) || isNaN(date)) return '';
    const d = String(date.getDate()).padStart(2, '0');
    const m = String(date.getMonth() + 1).padStart(2, '0');
    return `${d}/${m}/${date.getFullYear()}`;
}

function formatApiDate(date) {
    const d = String(date.getDate()).padStart(2, '0');
    const m = String(date.getMonth() + 1).padStart(2, '0');
    return `${date.getFullYear()}-${m}-${d}`;
}

function formatCsvDate(date) {
    if (!(date instanceof Date) || isNaN(date)) return '';
    const d = String(date.getDate()).padStart(2, '0');
    const m = String(date.getMonth() + 1).padStart(2, '0');
    return `${date.getFullYear()}/${m}/${d}`;
}

// --- Duration preview ---
function updateDurationPreview() {
    const eventType = document.querySelector('input[name="event-type"]:checked').value;
    const preview = document.getElementById('duration-preview');
    if (eventType !== 'continuous') { preview.textContent = ''; return; }

    const startDateVal = document.getElementById('event-date').value;
    const startTime = document.getElementById('event-time').value;
    const endDate = document.getElementById('event-end-date').value;
    const endTime = document.getElementById('event-end-time').value;

    if (!startDateVal || !startTime || !endDate || !endTime) { preview.textContent = ''; return; }

    const start = new Date(`${startDateVal}T${startTime}`);
    const end = new Date(`${endDate}T${endTime}`);

    if (end <= start) {
        preview.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Fine prima dell\'inizio';
        preview.style.color = '#e53e3e';
        return;
    }

    preview.style.color = '';
    const diff = end - start;
    const days = Math.floor(diff / 86400000);
    const hours = Math.floor((diff % 86400000) / 3600000);
    const mins = Math.floor((diff % 3600000) / 60000);
    let dur = '';
    if (days > 0) dur += `${days}g `;
    if (hours > 0) dur += `${hours}h `;
    if (mins > 0) dur += `${mins}m`;
    preview.innerHTML = `<i class="fas fa-clock"></i> Durata: ${dur.trim() || '0m'}`;
}

// --- Add event ---
async function addEvent() {
    const title = document.getElementById('event-title').value.trim();
    if (!title) { showToast('Inserisci un titolo per l\'evento.', 'error'); return; }

    const dateVal = document.getElementById('event-date').value;
    if (!dateVal) { showToast('Seleziona una data.', 'error'); return; }

    const time = document.getElementById('event-time').value;
    if (!time) { showToast('Seleziona un\'ora di inizio.', 'error'); return; }

    const place = document.getElementById('event-place').value;
    if (!place) { showToast('Seleziona un luogo.', 'error'); return; }

    const description = document.getElementById('event-description').value.trim();
    const eventType = document.querySelector('input[name="event-type"]:checked').value;

    let endDate = null, endTime = null;
    if (eventType === 'continuous') {
        endDate = document.getElementById('event-end-date').value;
        endTime = document.getElementById('event-end-time').value;
        if (!endDate) { showToast('Seleziona una data di fine.', 'error'); return; }
        if (!endTime) { showToast('Seleziona un\'ora di fine.', 'error'); return; }
        if (new Date(`${endDate}T${endTime}`) <= new Date(`${dateVal}T${time}`)) {
            showToast('La data/ora di fine deve essere successiva a quella di inizio.', 'error');
            return;
        }
    }

    const payload = {
        title,
        description,
        date: dateVal,
        time,
        place,
        event_type: eventType,
        end_date: endDate,
        end_time: endTime
    };

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        if (response.ok) {
            showToast('Evento creato con successo!');
            loadEvents();
            document.getElementById('event-title').value = '';
            document.getElementById('event-date').value = '';
            document.getElementById('event-time').value = '';
            document.getElementById('event-place').value = '';
            document.getElementById('event-description').value = '';
            document.getElementById('event-end-date').value = '';
            document.getElementById('event-end-time').value = '';
            document.querySelector('input[name="event-type"][value="single"]').checked = true;
            toggleEndDateTime();
        } else {
            showToast('Errore nella creazione dell\'evento.', 'error');
        }
    } catch (error) {
        console.error('Errore:', error);
        showToast('Errore nella creazione dell\'evento.', 'error');
    }
}

// --- Display events in table ---
function calculateDuration(event) {
    if (event.event_type !== 'continuous' || !event.end_date || !event.end_time) return '-';
    const start = new Date(event.date.toISOString().split('T')[0] + 'T' + event.time);
    const end = new Date(event.end_date.toISOString().split('T')[0] + 'T' + event.end_time);
    const diff = end - start;
    const days = Math.floor(diff / 86400000);
    const hours = Math.floor((diff % 86400000) / 3600000);
    const mins = Math.floor((diff % 3600000) / 60000);
    let dur = '';
    if (days > 0) dur += `${days}g `;
    if (hours > 0) dur += `${hours}h `;
    if (mins > 0) dur += `${mins}m`;
    return dur.trim() || '0m';
}

function displayEvents() {
    const tbody = document.getElementById('events-list');
    tbody.innerHTML = '';

    if (events.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:#666">Nessun evento presente</td></tr>';
        return;
    }

    events.forEach(event => {
        const row = document.createElement('tr');

        // Type badge
        const typeCell = document.createElement('td');
        const badge = document.createElement('span');
        badge.className = `type-badge ${event.event_type}`;
        badge.textContent = event.event_type === 'continuous' ? 'Cont.' : 'Sing.';
        typeCell.appendChild(badge);
        row.appendChild(typeCell);

        // Date/Time
        const dtCell = document.createElement('td');
        const dtDiv = document.createElement('div');
        dtDiv.className = 'datetime-info';
        dtDiv.innerHTML = `<div class="datetime-start"><strong>${formatDate(event.date)}</strong> ${event.time}</div>`;
        if (event.event_type === 'continuous' && event.end_date && event.end_time) {
            const duration = calculateDuration(event);
            dtDiv.innerHTML += `<div class="datetime-end">→ ${formatDate(event.end_date)} ${event.end_time}</div>`;
            if (duration !== '-') dtDiv.innerHTML += `<div class="datetime-duration"><small>(${duration})</small></div>`;
        }
        dtCell.appendChild(dtDiv);
        row.appendChild(dtCell);

        // Place
        const placeCell = document.createElement('td');
        placeCell.textContent = event.place;
        placeCell.title = event.place;
        row.appendChild(placeCell);

        // Title
        const titleCell = document.createElement('td');
        titleCell.textContent = event.title;
        titleCell.title = event.title;
        row.appendChild(titleCell);

        // Description
        const descCell = document.createElement('td');
        descCell.textContent = event.description || '—';
        descCell.title = event.description || '';
        row.appendChild(descCell);

        // Actions
        const actionCell = document.createElement('td');
        actionCell.className = 'action-cell';

        const editBtn = document.createElement('button');
        editBtn.className = 'edit-btn';
        editBtn.innerHTML = '<i class="fas fa-pencil-alt"></i>';
        editBtn.title = 'Modifica evento';
        editBtn.onclick = () => openEditDialog(event);
        actionCell.appendChild(editBtn);

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'delete-btn';
        deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
        deleteBtn.title = 'Elimina evento';
        deleteBtn.onclick = () => deleteEvent(event.id);
        actionCell.appendChild(deleteBtn);

        row.appendChild(actionCell);
        tbody.appendChild(row);
    });
}

// --- Delete event ---
async function deleteEvent(id) {
    if (!confirm('Sei sicuro di voler eliminare questo evento?')) return;
    try {
        const response = await fetch(API_URL, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        if (response.ok) {
            showToast('Evento eliminato.');
            loadEvents();
        } else {
            showToast('Errore nell\'eliminazione dell\'evento.', 'error');
        }
    } catch (error) {
        console.error('Errore:', error);
        showToast('Errore nell\'eliminazione dell\'evento.', 'error');
    }
}

// --- Sort events ---
function sortEvents(criteria) {
    if (criteria === 'date') {
        events.sort((a, b) => (a.date - b.date) || a.time.localeCompare(b.time));
    } else if (criteria === 'place') {
        events.sort((a, b) => a.place.localeCompare(b.place) || (a.date - b.date) || a.time.localeCompare(b.time));
    }
    displayEvents();
}

// --- Export CSV ---
function exportToCsv() {
    if (events.length === 0) { showToast('Nessun evento da esportare.', 'error'); return; }

    let csv = 'data:text/csv;charset=utf-8,data,ora,luogo,titolo,descrizione\n';
    events.forEach(event => {
        const esc = s => `"${(s || '').replace(/"/g, '""')}"`;
        csv += `${formatCsvDate(event.date)},${event.time},${event.place},${esc(event.title)},${esc(event.description)}\n`;
    });
    const link = document.createElement('a');
    link.setAttribute('href', encodeURI(csv));
    link.setAttribute('download', 'eventi.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// --- Custom place dialog ---
function showCustomPlaceDialog() {
    document.getElementById('custom-place-dialog').style.display = 'flex';
    document.getElementById('custom-place').focus();
}

function closeCustomPlaceDialog() {
    document.getElementById('custom-place-dialog').style.display = 'none';
    document.getElementById('custom-place').value = '';
}

function addCustomPlace() {
    const val = document.getElementById('custom-place').value.trim();
    if (!val) { showToast('Inserisci un nome per il luogo.', 'error'); return; }

    const sel = document.getElementById('event-place');
    for (let i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value === val) { sel.selectedIndex = i; closeCustomPlaceDialog(); return; }
    }
    const opt = document.createElement('option');
    opt.value = val; opt.text = val;
    sel.add(opt);
    sel.value = val;
    closeCustomPlaceDialog();
}

// --- Edit dialog ---
function openEditDialog(event) {
    document.getElementById('edit-event-id').value = event.id;
    document.getElementById('edit-title').value = event.title;
    document.getElementById('edit-date').value = formatApiDate(event.date);
    document.getElementById('edit-time').value = event.time;
    document.getElementById('edit-place').value = event.place;
    document.getElementById('edit-description').value = event.description || '';

    const type = event.event_type || 'single';
    document.querySelector(`input[name="edit-event-type"][value="${type}"]`).checked = true;

    const endSection = document.getElementById('edit-end-datetime-section');
    if (type === 'continuous' && event.end_date && event.end_time) {
        endSection.style.display = 'block';
        document.getElementById('edit-end-date').value = formatApiDate(event.end_date);
        document.getElementById('edit-end-time').value = event.end_time;
    } else {
        endSection.style.display = 'none';
        document.getElementById('edit-end-date').value = '';
        document.getElementById('edit-end-time').value = '';
    }

    document.getElementById('edit-event-dialog').style.display = 'flex';
}

function closeEditDialog() {
    document.getElementById('edit-event-dialog').style.display = 'none';
}

function toggleEditEndDateTime() {
    const type = document.querySelector('input[name="edit-event-type"]:checked').value;
    document.getElementById('edit-end-datetime-section').style.display = type === 'continuous' ? 'block' : 'none';
}

async function saveEdit() {
    const id = document.getElementById('edit-event-id').value;
    const title = document.getElementById('edit-title').value.trim();
    if (!title) { showToast('Inserisci un titolo.', 'error'); return; }

    const dateVal = document.getElementById('edit-date').value;
    if (!dateVal) { showToast('Seleziona una data.', 'error'); return; }

    const time = document.getElementById('edit-time').value;
    if (!time) { showToast('Seleziona un\'ora di inizio.', 'error'); return; }

    const place = document.getElementById('edit-place').value.trim();
    if (!place) { showToast('Inserisci un luogo.', 'error'); return; }

    const description = document.getElementById('edit-description').value.trim();
    const eventType = document.querySelector('input[name="edit-event-type"]:checked').value;

    let endDate = null, endTime = null;
    if (eventType === 'continuous') {
        endDate = document.getElementById('edit-end-date').value;
        endTime = document.getElementById('edit-end-time').value;
        if (!endDate) { showToast('Seleziona una data di fine.', 'error'); return; }
        if (!endTime) { showToast('Seleziona un\'ora di fine.', 'error'); return; }
        if (new Date(`${endDate}T${endTime}`) <= new Date(`${dateVal}T${time}`)) {
            showToast('La data/ora di fine deve essere successiva a quella di inizio.', 'error');
            return;
        }
    }

    const payload = { id, title, description, date: dateVal, time, place, event_type: eventType, end_date: endDate, end_time: endTime };

    try {
        const response = await fetch(API_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        if (response.ok) {
            showToast('Evento aggiornato con successo!');
            closeEditDialog();
            loadEvents();
        } else {
            showToast('Errore nell\'aggiornamento dell\'evento.', 'error');
        }
    } catch (error) {
        console.error('Errore:', error);
        showToast('Errore nell\'aggiornamento dell\'evento.', 'error');
    }
}

// --- Init ---
window.addEventListener('DOMContentLoaded', () => {
    // Set today's date as default
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('event-date').value = today;

    // Event type radio listeners
    document.querySelectorAll('input[name="event-type"]').forEach(r => r.addEventListener('change', toggleEndDateTime));
    document.querySelectorAll('input[name="edit-event-type"]').forEach(r => r.addEventListener('change', toggleEditEndDateTime));

    // Duration preview on time change
    document.getElementById('event-time').addEventListener('change', updateDurationPreview);
    document.getElementById('event-date').addEventListener('change', updateDurationPreview);

    // Close dialogs on backdrop click
    document.getElementById('custom-place-dialog').addEventListener('click', function(e) {
        if (e.target === this) closeCustomPlaceDialog();
    });
    document.getElementById('edit-event-dialog').addEventListener('click', function(e) {
        if (e.target === this) closeEditDialog();
    });

    loadEvents();
});
