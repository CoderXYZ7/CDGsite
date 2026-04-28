<?php
include '../../config.php';
checkAuth();
checkTag('admin');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Gestione Utenti - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../static/css/styles.css">
    <link rel="stylesheet" href="assets/adminEve.css">
    <style>
        .user-self-badge {
            display: inline-block;
            font-size: 0.7rem;
            background: #1a365d;
            color: white;
            padding: 1px 6px;
            border-radius: 3px;
            margin-left: 6px;
            vertical-align: middle;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .user-tag-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .user-tag-badge.tag-admin {
            background: #e53e3e;
            color: white;
        }
        .user-tag-badge.tag-student {
            background: #1a365d;
            color: white;
        }
        .password-input-row {
            position: relative;
        }
        .password-input-row input {
            padding-right: 2.5rem;
        }
        .toggle-pw {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            padding: 0;
            font-size: 0.9rem;
            box-shadow: none;
        }
        .toggle-pw:hover {
            color: #1a365d;
            transform: translateY(-50%);
            box-shadow: none;
        }
    </style>
</head>
<body>
    <div id="nav-placeholder"></div>
    <div id="toast-container"></div>

    <!-- Hidden field carries the current user's ID so JS can highlight "Tu" and block self-delete -->
    <input type="hidden" id="current-user-id" value="<?php echo (int)$_SESSION['user_id']; ?>">

    <main class="main-wrapper">
        <div class="page-header">
            <h1><i class="fas fa-users-cog"></i> Gestione Utenti</h1>
            <p class="page-subtitle">Aggiungi, modifica ed elimina gli account admin</p>
        </div>

        <!-- Add User Section -->
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-user-plus"></i> Aggiungi Utente</h2>
            </div>
            <div class="compact-form">
                <div class="form-row">
                    <div class="form-control">
                        <label for="new-username">Username:</label>
                        <input type="text" id="new-username" placeholder="es. mario.rossi" autocomplete="off">
                    </div>
                    <div class="form-control">
                        <label for="new-password">Password:</label>
                        <div class="password-input-row">
                            <input type="password" id="new-password" placeholder="Password" autocomplete="new-password">
                            <button type="button" class="toggle-pw" onclick="togglePw('new-password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-control" style="flex:0 0 180px">
                        <label for="new-tag">Ruolo:</label>
                        <select id="new-tag">
                            <option value="student">Collaboratore</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" id="add-user-btn" onclick="addUser()" class="primary-btn">
                        <i class="fas fa-plus"></i> Crea Utente
                    </button>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-users"></i> Utenti Registrati</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Username</th>
                            <th><i class="fas fa-tag"></i> Ruolo</th>
                            <th><i class="fas fa-cogs"></i> Azioni</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body"></tbody>
                </table>
            </div>
        </div>

        <!-- Edit User Dialog -->
        <div id="edit-user-dialog" class="custom-input-dialog">
            <div class="dialog-content dialog-wide">
                <h3><i class="fas fa-user-edit"></i> Modifica — <span id="edit-user-title"></span></h3>
                <input type="hidden" id="edit-user-id">

                <div class="form-group">
                    <label for="edit-tag">Ruolo:</label>
                    <select id="edit-tag">
                        <option value="student">Collaboratore</option>
                        <option value="admin">Admin</option>
                    </select>
                    <small style="color:#666;display:block;margin-top:4px">
                        Il ruolo Admin ha accesso a tutti i moduli.
                    </small>
                </div>

                <div class="form-group">
                    <label for="edit-password">Nuova password: <small style="font-weight:normal">(lascia vuoto per non cambiare)</small></label>
                    <div class="password-input-row">
                        <input type="password" id="edit-password" placeholder="Nuova password" autocomplete="new-password">
                        <button type="button" class="toggle-pw" onclick="togglePw('edit-password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="dialog-buttons">
                    <button type="button" onclick="closeEditDialog()" class="secondary">Annulla</button>
                    <button type="button" onclick="saveUserEdit()" class="primary-btn-dialog">Salva</button>
                </div>
            </div>
        </div>
    </main>

    <div id="admin-username" style="display: none;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
    <script src="assets/adminUsers.js"></script>
    <script src="assets/adminNav.js"></script>
    <script>
        function togglePw(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon  = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
