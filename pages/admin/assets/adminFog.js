const FOG_API = '../../api/foglietto.php';
let foglietti = [];

function showToast(msg, type = 'success') {
    const c = document.getElementById('toast-container');
    const t = document.createElement('div');
    t.className = `toast toast-${type}`;
    const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle';
    t.innerHTML = `<i class="fas fa-${icon}"></i> ${msg}`;
    c.appendChild(t);
    requestAnimationFrame(() => t.classList.add('toast-visible'));
    setTimeout(() => { t.classList.remove('toast-visible'); t.addEventListener('transitionend', () => t.remove()); }, 3500);
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

function getNextSunday() {
    const d = new Date();
    const diff = d.getDay() === 0 ? 7 : 7 - d.getDay();
    d.setDate(d.getDate() + diff);
    return d.toISOString().split('T')[0];
}

async function loadFoglietti() {
    try {
        const res = await fetch(FOG_API);
        foglietti = await res.json();
        renderFoglietti();
    } catch (e) {
        showToast('Errore nel caricamento dei foglietti.', 'error');
    }
}

function renderFoglietti() {
    const tbody = document.getElementById('fog-list-body');
    tbody.innerHTML = '';

    if (foglietti.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:2rem;color:#666">Nessun foglietto caricato</td></tr>';
        return;
    }

    foglietti.forEach(pdf => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${pdf.date}</strong></td>
            <td style="color:#666;font-size:.875rem">${formatSize(pdf.size)}</td>
            <td>
                <a href="${pdf.url}" target="_blank" class="fog-view-btn">
                    <i class="fas fa-eye"></i> Visualizza
                </a>
                <a href="${pdf.url}" download="${pdf.filename}" class="fog-download-btn">
                    <i class="fas fa-download"></i> Scarica
                </a>
            </td>
            <td>
                <button class="delete-btn" onclick="deleteFoglietto('${pdf.filename}', '${pdf.date}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>`;
        tbody.appendChild(tr);
    });
}

async function uploadPdf() {
    const fileInput = document.getElementById('fog-file-input');
    const dateInput = document.getElementById('fog-date');
    const overwrite = document.getElementById('fog-overwrite').checked;

    if (!fileInput.files[0]) { showToast('Seleziona un file PDF.', 'error'); return; }
    if (!dateInput.value)    { showToast('Seleziona la data del foglietto.', 'error'); return; }

    const btn = document.getElementById('upload-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Caricamento…';

    const fd = new FormData();
    fd.append('pdf', fileInput.files[0]);
    fd.append('date', dateInput.value);
    fd.append('overwrite', overwrite ? 'true' : 'false');

    try {
        const res  = await fetch(FOG_API, { method: 'POST', body: fd });
        const data = await res.json();

        if (res.ok) {
            showToast('Foglietto caricato con successo!');
            fileInput.value = '';
            document.getElementById('fog-filename-display').textContent = '';
            loadFoglietti();
        } else {
            showToast(data.error || 'Errore nel caricamento.', 'error');
        }
    } catch (e) {
        showToast('Errore di rete.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload"></i> Carica';
    }
}

async function deleteFoglietto(filename, date) {
    if (!confirm(`Eliminare il foglietto del ${date}?`)) return;
    try {
        const res  = await fetch(FOG_API, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ filename })
        });
        const data = await res.json();
        if (res.ok) {
            showToast('Foglietto eliminato.');
            loadFoglietti();
        } else {
            showToast(data.error || "Errore nell'eliminazione.", 'error');
        }
    } catch (e) {
        showToast('Errore di rete.', 'error');
    }
}

window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('fog-date').value = getNextSunday();
    loadFoglietti();

    const dropZone  = document.getElementById('fog-drop-zone');
    const fileInput = document.getElementById('fog-file-input');
    const display   = document.getElementById('fog-filename-display');

    dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (!file || file.type !== 'application/pdf') { showToast('Seleziona un file PDF.', 'error'); return; }
        // Assign files to input via DataTransfer
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        display.textContent = file.name;
    });

    fileInput.addEventListener('change', function () {
        display.textContent = this.files[0] ? this.files[0].name : '';
    });
});
