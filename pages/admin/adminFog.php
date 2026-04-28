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
    <title>Gestione Foglietti - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../static/css/styles.css">
    <link rel="stylesheet" href="assets/adminEve.css">
    <link rel="stylesheet" href="assets/adminFog.css">
</head>
<body>
    <div id="nav-placeholder"></div>
    <div id="toast-container"></div>

    <main class="main-wrapper">
        <div class="page-header">
            <h1><i class="fas fa-file-pdf"></i> Gestione Foglietti</h1>
            <p class="page-subtitle">Carica e gestisci i foglietti settimanali in PDF</p>
        </div>

        <!-- Upload Section -->
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-upload"></i> Carica Foglietto</h2>
            </div>

            <div class="fog-upload-area">
                <div class="fog-drop-zone" id="fog-drop-zone">
                    <i class="fas fa-cloud-upload-alt fog-drop-icon"></i>
                    <p class="fog-drop-label">Trascina un PDF qui</p>
                    <p class="fog-drop-or">oppure</p>
                    <label for="fog-file-input" class="primary-btn" style="cursor:pointer; display:inline-flex;">
                        <i class="fas fa-folder-open"></i> Scegli file
                    </label>
                    <input type="file" id="fog-file-input" accept=".pdf,application/pdf" style="display:none">
                    <p id="fog-filename-display" class="fog-filename"></p>
                </div>

                <div class="fog-upload-meta">
                    <div class="form-control">
                        <label for="fog-date">Data del foglietto:</label>
                        <input type="date" id="fog-date">
                    </div>
                    <div class="form-control">
                        <label class="checkbox-label">
                            <input type="checkbox" id="fog-overwrite">
                            Sovrascrivi se già esistente
                        </label>
                    </div>
                    <button type="button" id="upload-btn" onclick="uploadPdf()" class="primary-btn">
                        <i class="fas fa-upload"></i> Carica
                    </button>
                </div>
            </div>
        </div>

        <!-- Foglietti List -->
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Foglietti Caricati</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar-day"></i> Data</th>
                            <th><i class="fas fa-hdd"></i> Dimensione</th>
                            <th><i class="fas fa-eye"></i> Visualizza / Scarica</th>
                            <th><i class="fas fa-cogs"></i> Azioni</th>
                        </tr>
                    </thead>
                    <tbody id="fog-list-body"></tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="admin-username" style="display: none;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
    <script src="assets/adminFog.js"></script>
    <script src="assets/adminNav.js"></script>
</body>
</html>
