<?php
include '../../config.php';
checkAuth();
checkTag('admin'); // Allowed tags
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Gestione Eventi - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../static/css/styles.css">
    <link rel="stylesheet" href="assets/adminEve.css">
</head>
<body>
    <div id="nav-placeholder"></div>

    <!-- Toast container -->
    <div id="toast-container"></div>

    <main class="main-wrapper">
        <div class="page-header">
            <h1><i class="fas fa-calendar-alt"></i> Gestione Eventi</h1>
            <p class="page-subtitle">Crea, modifica ed elimina gli eventi della comunità</p>
        </div>

        <!-- Create Event Section -->
        <div class="section create-event-section">
            <div class="section-header">
                <h2><i class="fas fa-plus-circle"></i> Crea Nuovo Evento</h2>
            </div>

            <div class="compact-form">
                <!-- Row 1: Event Type & Title -->
                <div class="form-row">
                    <div class="form-control event-type-control">
                        <label>Tipo di Evento:</label>
                        <div class="radio-group compact">
                            <label class="radio-option">
                                <input type="radio" name="event-type" value="single" checked>
                                <span class="radio-label">Singolo</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="event-type" value="continuous">
                                <span class="radio-label">Continuativo</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-control">
                        <label for="event-title">Titolo:</label>
                        <input type="text" id="event-title" placeholder="Titolo dell'evento">
                    </div>
                </div>

                <!-- Row 2: Date & Time -->
                <div class="form-row">
                    <div class="form-control">
                        <label for="event-date">Data:</label>
                        <input type="date" id="event-date">
                    </div>
                    <div class="form-control">
                        <label for="event-time">Ora inizio:</label>
                        <input type="time" id="event-time">
                    </div>
                </div>

                <!-- Row 3: Place & Description -->
                <div class="form-row">
                    <div class="form-control">
                        <label for="event-place">Luogo:</label>
                        <select id="event-place">
                            <option value="">Seleziona luogo</option>
                            <option value="Chiesa di San Giorgio">Chiesa di San Giorgio</option>
                            <option value="Chiesa di San Nicolò">Chiesa di San Nicolò</option>
                            <option value="Oratorio">Oratorio</option>
                            <option value="Salone Parrocchiale">Salone Parrocchiale</option>
                            <option value="Piazza Duomo">Piazza Duomo</option>
                            <option value="Parrocchia di Marano Lagunare">Parrocchia di Marano Lagunare</option>
                            <option value="Parrocchia di Porpetto">Parrocchia di Porpetto</option>
                            <option value="Parrocchia di Carlino">Parrocchia di Carlino</option>
                            <option value="Parrocchia di Corgnolo">Parrocchia di Corgnolo</option>
                            <option value="Parrocchia di Porto Nogaro">Parrocchia di Porto Nogaro</option>
                            <option value="Parrocchia di Villanova">Parrocchia di Villanova</option>
                            <option value="Parrocchia di Zellina">Parrocchia di Zellina</option>
                        </select>
                    </div>
                    <div class="form-control" style="flex: 0 0 auto;">
                        <label>&nbsp;</label>
                        <button type="button" onclick="showCustomPlaceDialog()" class="secondary-btn compact">
                            <i class="fas fa-plus"></i> Altro
                        </button>
                    </div>
                    <div class="form-control">
                        <label for="event-description">Descrizione:</label>
                        <input type="text" id="event-description" placeholder="Descrizione (opzionale)">
                    </div>
                </div>

                <!-- Continuous Event Options -->
                <div id="end-datetime-section" class="continuous-options" style="display: none;">
                    <div class="form-row">
                        <div class="form-control">
                            <label for="event-end-date">Data fine:</label>
                            <input type="date" id="event-end-date" onchange="updateDurationPreview()">
                        </div>
                        <div class="form-control">
                            <label for="event-end-time">Ora fine:</label>
                            <input type="time" id="event-end-time" onchange="updateDurationPreview()">
                        </div>
                        <div class="form-control">
                            <div id="duration-preview" class="duration-info"></div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="form-actions">
                    <button type="button" onclick="addEvent()" class="primary-btn">
                        <i class="fas fa-save"></i> Crea Evento
                    </button>
                </div>
            </div>
        </div>

        <!-- CSV Import Section -->
        <div class="section csv-import-section">
            <div class="section-header">
                <h2><i class="fas fa-file-upload"></i> Importazione CSV</h2>
            </div>

            <div class="csv-input-area">
                <div class="csv-drop-zone" id="csv-drop-zone">
                    <i class="fas fa-cloud-upload-alt csv-drop-icon"></i>
                    <p class="csv-drop-label">Trascina un file CSV qui</p>
                    <p class="csv-drop-or">oppure</p>
                    <label for="csv-file-input" class="primary-btn" style="cursor:pointer; display:inline-flex;">
                        <i class="fas fa-folder-open"></i> Scegli file
                    </label>
                    <input type="file" id="csv-file-input" accept=".csv,text/csv" style="display:none">
                </div>

                <div class="csv-format-hint">
                    <strong><i class="fas fa-info-circle"></i> Formato accettato:</strong>
                    <div class="csv-format-examples">
                        <div class="csv-format-row">
                            <span class="csv-format-tag">Semplice</span>
                            <code>data,ora,luogo,titolo,descrizione</code>
                        </div>
                        <div class="csv-format-row">
                            <span class="csv-format-tag">Esteso</span>
                            <code>tipo,data,ora,data_fine,ora_fine,luogo,titolo,descrizione</code>
                        </div>
                    </div>
                    <small>Date: <code>gg/mm/aaaa</code> oppure <code>aaaa-mm-gg</code> &nbsp;·&nbsp; Tipo: <code>single</code> o <code>continuous</code></small>
                </div>

                <div class="csv-paste-toggle">
                    <button type="button" class="secondary-btn compact" onclick="togglePasteArea()">
                        <i class="fas fa-paste"></i> Incolla testo CSV
                    </button>
                </div>

                <div id="csv-paste-area" style="display:none;">
                    <textarea id="csv-text" rows="7" placeholder="Incolla il contenuto CSV qui...&#10;es: data,ora,luogo,titolo,descrizione&#10;    14/04/2026,18:00,Chiesa di San Giorgio,Santa Messa,"></textarea>
                    <div class="csv-paste-actions">
                        <button type="button" class="primary-btn" onclick="parseFromText()">
                            <i class="fas fa-search"></i> Analizza
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview area -->
            <div id="csv-preview" style="display:none;">
                <div class="csv-preview-header">
                    <h3><span id="csv-row-count">0</span> eventi trovati</h3>
                    <div class="csv-preview-actions">
                        <button type="button" class="action-btn" onclick="selectAllCsvRows(true)">
                            <i class="fas fa-check-square"></i> Tutti
                        </button>
                        <button type="button" class="action-btn" onclick="selectAllCsvRows(false)">
                            <i class="fas fa-square"></i> Nessuno
                        </button>
                        <button type="button" class="primary-btn" id="import-btn" onclick="importCsvEvents()">
                            <i class="fas fa-upload"></i> Importa (<span id="import-count">0</span>)
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table id="csv-preview-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all-csv" onchange="selectAllCsvRows(this.checked)"></th>
                                <th>Tipo</th>
                                <th>Data</th>
                                <th>Ora</th>
                                <th>Fine</th>
                                <th>Luogo</th>
                                <th>Titolo</th>
                                <th>Descrizione</th>
                                <th>Stato</th>
                            </tr>
                        </thead>
                        <tbody id="csv-preview-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Event List Section -->
        <div class="section event-list-section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Lista Eventi</h2>
                <div class="section-actions">
                    <button type="button" onclick="sortEvents('date')" class="action-btn">
                        <i class="fas fa-sort-amount-down"></i> Per Data
                    </button>
                    <button type="button" onclick="sortEvents('place')" class="action-btn">
                        <i class="fas fa-sort-alpha-down"></i> Per Luogo
                    </button>
                    <button type="button" onclick="exportToCsv()" class="action-btn">
                        <i class="fas fa-download"></i> Esporta CSV
                    </button>
                </div>
            </div>

            <div id="events-container">
                <div class="table-container">
                    <table id="events-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th><i class="fas fa-calendar-day"></i> Data/Ora</th>
                                <th><i class="fas fa-map-marker-alt"></i> Luogo</th>
                                <th><i class="fas fa-heading"></i> Titolo</th>
                                <th><i class="fas fa-align-left"></i> Descrizione</th>
                                <th><i class="fas fa-cogs"></i> Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="events-list"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Custom Place Dialog -->
        <div id="custom-place-dialog" class="custom-input-dialog">
            <div class="dialog-content">
                <h3>Inserisci Luogo Personalizzato</h3>
                <div class="form-group">
                    <label for="custom-place">Nome del luogo:</label>
                    <input type="text" id="custom-place" placeholder="es. Sagrato della chiesa">
                </div>
                <div class="dialog-buttons">
                    <button type="button" onclick="closeCustomPlaceDialog()" class="secondary">Annulla</button>
                    <button type="button" onclick="addCustomPlace()">Aggiungi</button>
                </div>
            </div>
        </div>

        <!-- Edit Event Dialog -->
        <div id="edit-event-dialog" class="custom-input-dialog">
            <div class="dialog-content dialog-wide">
                <h3><i class="fas fa-edit"></i> Modifica Evento</h3>
                <input type="hidden" id="edit-event-id">
                <div class="form-group">
                    <label>Tipo di Evento:</label>
                    <div class="radio-group compact">
                        <label class="radio-option">
                            <input type="radio" name="edit-event-type" value="single">
                            <span class="radio-label">Singolo</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="edit-event-type" value="continuous">
                            <span class="radio-label">Continuativo</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-title">Titolo:</label>
                    <input type="text" id="edit-title">
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1">
                        <label for="edit-date">Data:</label>
                        <input type="date" id="edit-date">
                    </div>
                    <div class="form-group" style="flex:1">
                        <label for="edit-time">Ora inizio:</label>
                        <input type="time" id="edit-time">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-place">Luogo:</label>
                    <input type="text" id="edit-place">
                </div>
                <div class="form-group">
                    <label for="edit-description">Descrizione:</label>
                    <input type="text" id="edit-description" placeholder="Opzionale">
                </div>
                <div id="edit-end-datetime-section" style="display:none;">
                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label for="edit-end-date">Data fine:</label>
                            <input type="date" id="edit-end-date">
                        </div>
                        <div class="form-group" style="flex:1">
                            <label for="edit-end-time">Ora fine:</label>
                            <input type="time" id="edit-end-time">
                        </div>
                    </div>
                </div>
                <div class="dialog-buttons">
                    <button type="button" onclick="closeEditDialog()" class="secondary">Annulla</button>
                    <button type="button" onclick="saveEdit()" class="primary-btn-dialog">Salva Modifiche</button>
                </div>
            </div>
        </div>
    </main>

    <div id="admin-username" style="display: none;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
    <script src="assets/adminEve.js"></script>
    <script src="assets/adminNav.js"></script>
</body>
</html>
