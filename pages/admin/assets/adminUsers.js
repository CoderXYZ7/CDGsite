const USERS_API = '../../api/users.php';
let users = [];

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

async function loadUsers() {
    try {
        const res = await fetch(USERS_API);
        if (res.status === 503) { showDbUnavailable(); return; }
        users = await res.json();
        renderUsers();
    } catch (e) {
        showToast('Errore nel caricamento degli utenti.', 'error');
    }
}

function showDbUnavailable() {
    document.getElementById('users-table-body').innerHTML =
        '<tr><td colspan="3" style="text-align:center;padding:1.5rem;color:#c53030">Database non disponibile</td></tr>';
}

const TAG_LABELS = { admin: 'Admin', student: 'Collaboratore' };

function renderUsers() {
    const tbody = document.getElementById('users-table-body');
    tbody.innerHTML = '';

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;padding:2rem;color:#666">Nessun utente</td></tr>';
        return;
    }

    const currentId = parseInt(document.getElementById('current-user-id').value);

    users.forEach(u => {
        const isSelf = u.id === currentId;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <strong>${u.username}</strong>
                ${isSelf ? '<span class="user-self-badge">Tu</span>' : ''}
            </td>
            <td><span class="user-tag-badge tag-${u.tag}">${TAG_LABELS[u.tag] || u.tag}</span></td>
            <td class="action-cell">
                <button class="edit-btn" onclick="openEditDialog(${u.id})" title="Modifica">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <button class="delete-btn" onclick="deleteUser(${u.id}, '${u.username}')"
                    title="Elimina" ${isSelf ? 'disabled title="Non puoi eliminare te stesso"' : ''}>
                    <i class="fas fa-trash"></i>
                </button>
            </td>`;
        tbody.appendChild(tr);
    });
}

async function addUser() {
    const username = document.getElementById('new-username').value.trim();
    const password = document.getElementById('new-password').value;
    const tag      = document.getElementById('new-tag').value;

    if (!username) { showToast('Inserisci un username.', 'error'); return; }
    if (!password) { showToast('Inserisci una password.', 'error'); return; }

    const btn = document.getElementById('add-user-btn');
    btn.disabled = true;

    try {
        const res  = await fetch(USERS_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password, tag })
        });
        const data = await res.json();

        if (res.ok) {
            showToast(`Utente "${username}" creato.`);
            document.getElementById('new-username').value = '';
            document.getElementById('new-password').value = '';
            document.getElementById('new-tag').value = 'student';
            loadUsers();
        } else {
            showToast(data.error || 'Errore nella creazione.', 'error');
        }
    } catch (e) {
        showToast('Errore di rete.', 'error');
    } finally {
        btn.disabled = false;
    }
}

async function deleteUser(id, username) {
    if (!confirm(`Eliminare l'utente "${username}"?`)) return;
    try {
        const res  = await fetch(USERS_API, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (res.ok) {
            showToast(`Utente "${username}" eliminato.`);
            loadUsers();
        } else {
            showToast(data.error || "Errore nell'eliminazione.", 'error');
        }
    } catch (e) {
        showToast('Errore di rete.', 'error');
    }
}

function openEditDialog(id) {
    const user = users.find(u => u.id === id);
    if (!user) return;

    document.getElementById('edit-user-id').value      = user.id;
    document.getElementById('edit-user-title').textContent = user.username;
    document.getElementById('edit-tag').value          = user.tag;
    document.getElementById('edit-password').value     = '';

    const currentId = parseInt(document.getElementById('current-user-id').value);
    document.getElementById('edit-tag').disabled = (user.id === currentId);

    document.getElementById('edit-user-dialog').style.display = 'flex';
}

function closeEditDialog() {
    document.getElementById('edit-user-dialog').style.display = 'none';
}

async function saveUserEdit() {
    const id       = parseInt(document.getElementById('edit-user-id').value);
    const tag      = document.getElementById('edit-tag').value;
    const password = document.getElementById('edit-password').value;

    if (!tag && !password) { showToast('Nessuna modifica da salvare.', 'error'); return; }

    const payload = { id };
    if (tag)      payload.tag = tag;
    if (password) payload.password = password;

    try {
        const res  = await fetch(USERS_API, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (res.ok) {
            showToast('Utente aggiornato.');
            closeEditDialog();
            loadUsers();
        } else {
            showToast(data.error || "Errore nell'aggiornamento.", 'error');
        }
    } catch (e) {
        showToast('Errore di rete.', 'error');
    }
}

window.addEventListener('DOMContentLoaded', () => {
    loadUsers();

    document.getElementById('edit-user-dialog').addEventListener('click', function(e) {
        if (e.target === this) closeEditDialog();
    });
});
