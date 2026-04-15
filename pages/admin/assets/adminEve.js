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

// --- CSV Import ---
let csvParsedRows = [];

function togglePasteArea() {
    const area = document.getElementById('csv-paste-area');
    area.style.display = area.style.display === 'none' ? 'block' : 'none';
    if (area.style.display === 'block') document.getElementById('csv-text').focus();
}

function parseDateStr(str) {
    str = str.trim();
    if (/^\d{2}\/\d{2}\/\d{4}$/.test(str)) {
        const [d, m, y] = str.split('/');
        return `${y}-${m}-${d}`;
    }
    if (/^\d{4}-\d{2}-\d{2}$/.test(str)) return str;
    if (/^\d{4}\/\d{2}\/\d{2}$/.test(str)) return str.replace(/\//g, '-');
    return null;
}

function parseCSVLine(line) {
    const fields = [];
    let inQuote = false, current = '';
    for (let i = 0; i < line.length; i++) {
        const ch = line[i];
        if (ch === '"') {
            if (inQuote && line[i + 1] === '"') { current += '"'; i++; }
            else inQuote = !inQuote;
        } else if (ch === ',' && !inQuote) {
            fields.push(current); current = '';
        } else {
            current += ch;
        }
    }
    fields.push(current);
    return fields.map(f => f.trim());
}

function parseCSV(text) {
    const lines = text.trim().split(/\r?\n/).filter(l => l.trim());
    if (lines.length < 2) return { error: 'Il file deve contenere almeno una riga di intestazione e una riga di dati.' };

    const headers = parseCSVLine(lines[0]).map(h => h.toLowerCase().trim());
    const idx = name => headers.indexOf(name);
    const isExtended = idx('tipo') >= 0 || idx('data_fine') >= 0;

    const rows = [];
    for (let i = 1; i < lines.length; i++) {
        const f = parseCSVLine(lines[i]);
        let row;

        if (isExtended) {
            row = {
                event_type: f[idx('tipo')] || 'single',
                date:       parseDateStr(f[idx('data')] || ''),
                time:       f[idx('ora')] || '',
                end_date:   idx('data_fine') >= 0 && f[idx('data_fine')] ? parseDateStr(f[idx('data_fine')]) : null,
                end_time:   idx('ora_fine') >= 0 ? (f[idx('ora_fine')] || null) : null,
                place:      f[idx('luogo')] || '',
                title:      f[idx('titolo')] || '',
                description: idx('descrizione') >= 0 ? (f[idx('descrizione')] || '') : ''
            };
        } else {
            // Simple format: data,ora,luogo,titolo,descrizione
            row = {
                event_type:  'single',
                date:        parseDateStr(f[idx('data')] !== undefined ? f[idx('data')] : f[0]),
                time:        f[idx('ora')] !== undefined ? f[idx('ora')] : f[1] || '',
                end_date:    null,
                end_time:    null,
                place:       f[idx('luogo')] !== undefined ? f[idx('luogo')] : f[2] || '',
                title:       f[idx('titolo')] !== undefined ? f[idx('titolo')] : f[3] || '',
                description: f[idx('descrizione')] !== undefined ? f[idx('descrizione')] : f[4] || ''
            };
        }

        // Normalise event_type
        const typeMap = { 'cont.': 'continuous', 'continuativo': 'continuous', 'sing.': 'single', 'singolo': 'single' };
        row.event_type = typeMap[row.event_type.toLowerCase()] || row.event_type;

        // Validate
        const errs = [];
        if (!row.date)  errs.push('data non valida');
        if (!row.time)  errs.push('ora mancante');
        if (!row.place) errs.push('luogo mancante');
        if (!row.title) errs.push('titolo mancante');
        if (row.event_type === 'continuous' && !row.end_date) errs.push('data_fine mancante');

        row._line   = i + 1;
        row._errors = errs;
        rows.push(row);
    }
    return { rows };
}

function showCsvPreview(rows) {
    csvParsedRows = rows;
    const tbody = document.getElementById('csv-preview-body');
    tbody.innerHTML = '';

    rows.forEach((row, idx) => {
        const hasError = row._errors.length > 0;
        const tr = document.createElement('tr');
        if (hasError) tr.className = 'csv-row-invalid';

        const endStr = row.end_date ? `${row.end_date}${row.end_time ? ' ' + row.end_time : ''}` : '—';
        const typeLabel = row.event_type === 'continuous' ? 'Cont.' : 'Sing.';

        const safe = s => s ? s.replace(/</g, '&lt;').replace(/>/g, '&gt;') : '';

        tr.innerHTML = `
            <td><input type="checkbox" class="csv-row-check" data-index="${idx}"
                ${hasError ? 'disabled title="Correggi gli errori prima di importare"' : 'checked'}
                onchange="updateCsvImportCount()"></td>
            <td><span class="type-badge ${row.event_type}">${typeLabel}</span></td>
            <td>${row.date || '<span class="csv-err">?</span>'}</td>
            <td>${row.time || '<span class="csv-err">?</span>'}</td>
            <td class="csv-end-col">${endStr}</td>
            <td>${safe(row.place) || '<span class="csv-err">?</span>'}</td>
            <td>${safe(row.title) || '<span class="csv-err">?</span>'}</td>
            <td class="csv-desc-col">${safe(row.description) || '—'}</td>
            <td id="csv-status-${idx}">
                ${hasError
                    ? `<span class="csv-status-error" title="${row._errors.join(', ')}"><i class="fas fa-exclamation-triangle"></i> ${row._errors.join(', ')}</span>`
                    : '<span class="csv-status-pending"><i class="fas fa-clock"></i> In attesa</span>'}
            </td>`;
        tbody.appendChild(tr);
    });

    document.getElementById('csv-row-count').textContent = rows.length;
    document.getElementById('csv-preview').style.display = 'block';
    updateCsvImportCount();
    document.getElementById('csv-preview').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function updateCsvImportCount() {
    const n = document.querySelectorAll('.csv-row-check:checked').length;
    document.getElementById('import-count').textContent = n;
    document.getElementById('select-all-csv').checked =
        n > 0 && n === document.querySelectorAll('.csv-row-check:not(:disabled)').length;
}

function selectAllCsvRows(checked) {
    document.querySelectorAll('.csv-row-check:not(:disabled)').forEach(cb => { cb.checked = checked; });
    updateCsvImportCount();
}

async function importCsvEvents() {
    const checks = [...document.querySelectorAll('.csv-row-check:checked')];
    if (checks.length === 0) { showToast('Nessuna riga selezionata.', 'error'); return; }

    const btn = document.getElementById('import-btn');
    btn.disabled = true;
    let ok = 0, fail = 0;

    for (const cb of checks) {
        const idx = parseInt(cb.dataset.index);
        const row = csvParsedRows[idx];
        const cell = document.getElementById(`csv-status-${idx}`);
        cell.innerHTML = '<span class="csv-status-loading"><i class="fas fa-spinner fa-spin"></i> Importazione…</span>';

        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    title: row.title, description: row.description || '',
                    date: row.date, time: row.time, place: row.place,
                    event_type: row.event_type,
                    end_date: row.end_date || null, end_time: row.end_time || null
                })
            });
            if (res.ok) {
                cell.innerHTML = '<span class="csv-status-ok"><i class="fas fa-check-circle"></i> Importato</span>';
                cb.checked = false; cb.disabled = true; ok++;
            } else {
                cell.innerHTML = '<span class="csv-status-error"><i class="fas fa-times-circle"></i> Errore server</span>';
                fail++;
            }
        } catch (e) {
            cell.innerHTML = '<span class="csv-status-error"><i class="fas fa-times-circle"></i> Errore rete</span>';
            fail++;
        }
    }

    btn.disabled = false;
    updateCsvImportCount();
    if (ok > 0)   { showToast(`${ok} event${ok === 1 ? 'o importato' : 'i importati'} con successo!`); loadEvents(); }
    if (fail > 0) { showToast(`${fail} event${fail === 1 ? 'o non importato' : 'i non importati'}.`, 'error'); }
}

function parseFromText() {
    const text = document.getElementById('csv-text').value.trim();
    if (!text) { showToast('Incolla il contenuto CSV prima di analizzare.', 'error'); return; }
    const result = parseCSV(text);
    if (result.error) { showToast(result.error, 'error'); return; }
    showCsvPreview(result.rows);
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

    // CSV file input
    const csvInput = document.getElementById('csv-file-input');
    csvInput.addEventListener('change', function () {
        if (!this.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            const result = parseCSV(e.target.result);
            if (result.error) { showToast(result.error, 'error'); return; }
            showCsvPreview(result.rows);
        };
        reader.readAsText(this.files[0]);
        this.value = '';
    });

    // Drag-and-drop
    const dropZone = document.getElementById('csv-drop-zone');
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (!file || (!file.name.endsWith('.csv') && file.type !== 'text/csv')) {
            showToast('Seleziona un file CSV.', 'error'); return;
        }
        const reader = new FileReader();
        reader.onload = ev => {
            const result = parseCSV(ev.target.result);
            if (result.error) { showToast(result.error, 'error'); return; }
            showCsvPreview(result.rows);
        };
        reader.readAsText(file);
    });

    loadEvents();
});
