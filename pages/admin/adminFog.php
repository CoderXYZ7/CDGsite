<?php
include 'config.php';
checkAuth();
checkTag('admin'); // Allowed tags

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration - move these to a separate config file in production
define('PDF_UPLOAD_DIR', '../foglietto/pdfs');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Handle file upload
if (isset($_FILES['pdf_file'])) {
    // Create upload directory if it doesn't exist
    if (!file_exists(PDF_UPLOAD_DIR)) {
        if (!mkdir(PDF_UPLOAD_DIR, 0755, true)) {
            $message = '<div class="alert alert-error">Errore: Impossibile creare la cartella per i PDF.</div>';
        }
    }

    $file = $_FILES['pdf_file'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = '<div class="alert alert-error">Errore durante il caricamento: ' . $this->uploadErrorToString($file['error']) . '</div>';
    } 
    // Check file size
    elseif ($file['size'] > MAX_FILE_SIZE) {
        $message = '<div class="alert alert-error">Errore: Il file è troppo grande. Dimensione massima: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB.</div>';
    }
    // Check file type
    else {
        $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = basename($file['name']);
        
        if ($file_type !== 'pdf') {
            $message = '<div class="alert alert-error">Errore: Solo file PDF sono consentiti.</div>';
        } 
        // Validate filename format
        elseif (!preg_match('/^\d{4}-\d{2}-\d{2}\.pdf$/', $filename)) {
            $message = '<div class="alert alert-error">Errore: Il nome file deve essere nel formato YYYY-MM-DD.pdf.</div>';
        } 
        else {
            $target_file = PDF_UPLOAD_DIR . '/' . $filename;
            
            // Handle existing file
            if (file_exists($target_file)) {
                if (isset($_POST['overwrite']) && $_POST['overwrite'] === 'yes') {
                    if (move_uploaded_file($file['tmp_name'], $target_file)) {
                        $message = '<div class="alert alert-success">Successo: Il file ' . htmlspecialchars($filename) . ' è stato sovrascritto.</div>';
                    } else {
                        $message = '<div class="alert alert-error">Errore: Si è verificato un errore durante il salvataggio del file.</div>';
                    }
                } else {
                    $message = '<div class="alert alert-error">Errore: Il file esiste già. Seleziona "Sovrascrivi" per sostituirlo.</div>';
                }
            } 
            // Save new file
            else {
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $message = '<div class="alert alert-success">Successo: Il file ' . htmlspecialchars($filename) . ' è stato caricato.</div>';
                } else {
                    $message = '<div class="alert alert-error">Errore: Si è verificato un errore durante il salvataggio del file.</div>';
                }
            }
        }
    }
}

// Helper function to translate upload errors
function uploadErrorToString($error) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File troppo grande (supera il limite del server)',
        UPLOAD_ERR_FORM_SIZE => 'File troppo grande (supera il limite del form)',
        UPLOAD_ERR_PARTIAL => 'Il file è stato caricato solo parzialmente',
        UPLOAD_ERR_NO_FILE => 'Nessun file è stato caricato',
        UPLOAD_ERR_NO_TMP_DIR => 'Cartella temporanea mancante',
        UPLOAD_ERR_CANT_WRITE => 'Impossibile scrivere il file su disco',
        UPLOAD_ERR_EXTENSION => 'Un\'estensione PHP ha interrotto il caricamento'
    ];
    return $errors[$error] ?? 'Errore sconosciuto';
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="icon" type="image/png" href="../../static/images/LogoNoBG.png" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Manager - Admin</title>
    <link rel="stylesheet" href="../../static/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Your existing styles here */
    </style>
</head>
<body>
    <main class="main-wrapper">
        <div class="content">
            <section class="hero">
                <h1>PDF Manager - Area Admin</h1>
                <p>Caricamento e gestione dei file PDF</p>
            </section>

            <?php echo $message; ?>

            <?php if (!$is_authenticated): ?>
                <!-- Login Form -->
                <section class="card main-card">
                    <div class="card-content">
                        <h3><i class="fas fa-lock"></i> Accesso Admin</h3>
                        <form method="post" action="admin.php">
                            <input type="hidden" name="login" value="1">
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            <button type="submit" class="button primary">Accedi</button>
                        </form>
                    </div>
                </section>

            <?php else: ?>
                <!-- Upload Form -->
                <section class="card main-card">
                    <div class="card-content">
                        <h3><i class="fas fa-upload"></i> Carica PDF</h3>
                        <form method="post" action="admin.php" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="pdf_file">Seleziona file PDF (formato YYYY-MM-DD.pdf):</label>
                                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" required>
                            </div>
                            
                            <div class="form-check">
                                <input type="checkbox" id="overwrite" name="overwrite" value="yes">
                                <label for="overwrite">Sovrascrivi se il file esiste già</label>
                            </div>
                            
                            <button type="submit" class="button primary">Carica PDF</button>
                        </form>
                    </div>
                </section>

                <!-- PDF List -->
                <section class="card main-card">
                    <div class="card-content">
                        <h3><i class="fas fa-list"></i> PDF Caricati</h3>
                        <?php
                        $pdf_files = glob(PDF_UPLOAD_DIR . '/*.pdf');
                        if (count($pdf_files) > 0):
                            usort($pdf_files, function($a, $b) {
                                return strcmp(basename($b), basename($a));
                            });
                        ?>
                            <table class="pdf-table">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pdf_files as $pdf): ?>
                                        <?php $file_name = basename($pdf); ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(str_replace('.pdf', '', $file_name)); ?></td>
                                            <td>
                                                <a href="<?php echo htmlspecialchars($pdf); ?>" target="_blank" class="button primary small">
                                                    <i class="fas fa-eye"></i> Visualizza
                                                </a>
                                                <a href="?delete=<?php echo urlencode($file_name); ?>" class="button danger small" onclick="return confirm('Sei sicuro di voler eliminare questo file?');">
                                                    <i class="fas fa-trash"></i> Elimina
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>Nessun PDF caricato.</p>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>