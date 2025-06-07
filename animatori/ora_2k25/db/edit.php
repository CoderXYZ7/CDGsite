<?php
// Database configuration
$host = 'localhost';
$dbname = 'ora_2k25';
$username = 'editor';
$password = 'password_editor';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_POST) {
    try {
        // Add Laboratory
        if (isset($_POST['add_lab'])) {
            $stmt = $pdo->prepare("INSERT INTO Laboratori (Nome, Descrizione) VALUES (?, ?)");
            $stmt->execute([$_POST['lab_nome'], $_POST['lab_descrizione']]);
            $success = "Laboratorio aggiunto con successo!";
        }
        
        // Add Responsible
        if (isset($_POST['add_resp'])) {
            $stmt = $pdo->prepare("INSERT INTO Responsabili (Nome, Descrizione) VALUES (?, ?)");
            $stmt->execute([$_POST['resp_nome'], $_POST['resp_descrizione']]);
            $success = "Responsabile aggiunto con successo!";
        }
        
        // Add Animator
        if (isset($_POST['add_anim'])) {
            $stmt = $pdo->prepare("INSERT INTO Animatori (Nome, Cognome, Laboratorio, Fascia, Colore, M, J, S) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['anim_nome'], 
                $_POST['anim_cognome'], 
                $_POST['anim_laboratorio'],
                $_POST['anim_fascia'],
                $_POST['anim_colore'],
                $_POST['anim_m'],
                $_POST['anim_j'],
                $_POST['anim_s']
            ]);
            
            // Add relationships with responsibles
            if (isset($_POST['responsabili']) && is_array($_POST['responsabili'])) {
                $animator_id = $pdo->lastInsertId();
                foreach ($_POST['responsabili'] as $resp_id) {
                    $stmt = $pdo->prepare("INSERT INTO Animatori_Responsabili (AnimatoreID, ResponsabileID) VALUES (?, ?)");
                    $stmt->execute([$animator_id, $resp_id]);
                }
            }
            $success = "Animatore aggiunto con successo!";
        }
        
        // Delete operations
        if (isset($_POST['delete_lab'])) {
            $stmt = $pdo->prepare("DELETE FROM Laboratori WHERE ID = ? AND ID != 13");
            $stmt->execute([$_POST['lab_id']]);
            $success = "Laboratorio eliminato!";
        }
        
        if (isset($_POST['delete_resp'])) {
            $stmt = $pdo->prepare("DELETE FROM Responsabili WHERE ID = ?");
            $stmt->execute([$_POST['resp_id']]);
            $success = "Responsabile eliminato!";
        }
        
        if (isset($_POST['delete_anim'])) {
            $stmt = $pdo->prepare("DELETE FROM Animatori WHERE ID = ?");
            $stmt->execute([$_POST['anim_id']]);
            $success = "Animatore eliminato!";
        }
        
    } catch(PDOException $e) {
        $error = "Errore: " . $e->getMessage();
    }
}

// Fetch data for display
$laboratori = $pdo->query("SELECT * FROM Laboratori ORDER BY Nome")->fetchAll();
$responsabili = $pdo->query("SELECT * FROM Responsabili ORDER BY Nome")->fetchAll();
$animatori = $pdo->query("
    SELECT a.*, l.Nome as LaboratorioNome 
    FROM Animatori a 
    JOIN Laboratori l ON a.Laboratorio = l.ID 
    ORDER BY a.Cognome, a.Nome
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Database ORA 2025</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .section h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; 
        }
        .form-group textarea { height: 80px; resize: vertical; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn:hover { opacity: 0.8; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background-color: #f8f9fa; font-weight: bold; }
        .table tr:hover { background-color: #f5f5f5; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 15px; }
        .checkbox-item { display: flex; align-items: center; gap: 5px; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-fascia-a { background-color: #28a745; color: white; }
        .badge-fascia-d { background-color: #17a2b8; color: white; }
        .badge-colore-b { background-color: #007bff; color: white; }
        .badge-colore-r { background-color: #dc3545; color: white; }
        .badge-colore-g { background-color: #28a745; color: white; }
        .badge-colore-a { background-color: #ffc107; color: black; }
        .nav { display: flex; gap: 20px; margin-bottom: 30px; }
        .nav a { text-decoration: none; color: #007bff; font-weight: bold; }
        .nav a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestione Database ORA 2025</h1>
        
        <div class="nav">
            <a href="edit.php">Modifica</a>
            <a href="view.php">Visualizza</a>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Laboratori Section -->
        <div class="section">
            <h2>Gestione Laboratori</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nome Laboratorio:</label>
                    <input type="text" name="lab_nome" required>
                </div>
                <div class="form-group">
                    <label>Descrizione:</label>
                    <textarea name="lab_descrizione"></textarea>
                </div>
                <button type="submit" name="add_lab" class="btn btn-primary">Aggiungi Laboratorio</button>
            </form>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrizione</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laboratori as $lab): ?>
                    <tr>
                        <td><?= $lab['ID'] ?></td>
                        <td><?= htmlspecialchars($lab['Nome']) ?></td>
                        <td><?= htmlspecialchars($lab['Descrizione']) ?></td>
                        <td>
                            <?php if ($lab['ID'] != 13): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="lab_id" value="<?= $lab['ID'] ?>">
                                <button type="submit" name="delete_lab" class="btn btn-danger" onclick="return confirm('Sei sicuro?')">Elimina</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Responsabili Section -->
        <div class="section">
            <h2>Gestione Responsabili</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nome Responsabile:</label>
                    <input type="text" name="resp_nome" required>
                </div>
                <div class="form-group">
                    <label>Descrizione:</label>
                    <textarea name="resp_descrizione"></textarea>
                </div>
                <button type="submit" name="add_resp" class="btn btn-primary">Aggiungi Responsabile</button>
            </form>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrizione</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($responsabili as $resp): ?>
                    <tr>
                        <td><?= $resp['ID'] ?></td>
                        <td><?= htmlspecialchars($resp['Nome']) ?></td>
                        <td><?= htmlspecialchars($resp['Descrizione']) ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="resp_id" value="<?= $resp['ID'] ?>">
                                <button type="submit" name="delete_resp" class="btn btn-danger" onclick="return confirm('Sei sicuro?')">Elimina</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Animatori Section -->
        <div class="section">
            <h2>Gestione Animatori</h2>
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Nome:</label>
                        <input type="text" name="anim_nome" required>
                    </div>
                    <div class="form-group">
                        <label>Cognome:</label>
                        <input type="text" name="anim_cognome" required>
                    </div>
                    <div class="form-group">
                        <label>Laboratorio:</label>
                        <select name="anim_laboratorio" required>
                            <?php foreach ($laboratori as $lab): ?>
                            <option value="<?= $lab['ID'] ?>"><?= htmlspecialchars($lab['Nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fascia:</label>
                        <select name="anim_fascia" required>
                            <option value="A">A</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Colore:</label>
                        <select name="anim_colore">
                            <option value="X">X (Non assegnato)</option>
                            <option value="B">B (Blu)</option>
                            <option value="R">R (Rosso)</option>
                            <option value="G">G (Giallo)</option>
                            <option value="A">A (Arancione)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Giornate disponibili:</label>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" name="anim_m" value="M" id="m">
                                <label for="m">Mini (M)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="anim_j" value="J" id="j">
                                <label for="j">Juniores (J)</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="anim_s" value="S" id="s">
                                <label for="s">Seniores (S)</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Responsabili associati:</label>
                    <div class="checkbox-group">
                        <?php foreach ($responsabili as $resp): ?>
                        <div class="checkbox-item">
                            <input type="checkbox" name="responsabili[]" value="<?= $resp['ID'] ?>" id="resp_<?= $resp['ID'] ?>">
                            <label for="resp_<?= $resp['ID'] ?>"><?= htmlspecialchars($resp['Nome']) ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <button type="submit" name="add_anim" class="btn btn-primary">Aggiungi Animatore</button>
            </form>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Laboratorio</th>
                        <th>Fascia</th>
                        <th>Colore</th>
                        <th>Categorie</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($animatori as $anim): ?>
                    <tr>
                        <td><?= htmlspecialchars($anim['Nome'] . ' ' . $anim['Cognome']) ?></td>
                        <td><?= htmlspecialchars($anim['LaboratorioNome']) ?></td>
                        <td><span class="badge badge-fascia-<?= strtolower($anim['Fascia']) ?>"><?= $anim['Fascia'] ?></span></td>
                        <td>
                            <?php if ($anim['Colore'] != 'X'): ?>
                            <span class="badge badge-colore-<?= strtolower($anim['Colore']) ?>"><?= $anim['Colore'] ?></span>
                            <?php else: ?>
                            <span class="badge" style="background-color: #6c757d; color: white;">Non assegnato</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $categorie = [];
                            if ($anim['M'] == 'M') $categorie[] = 'Mini';
                            if ($anim['J'] == 'J') $categorie[] = 'Juniores';
                            if ($anim['S'] == 'S') $categorie[] = 'Seniores';
                            echo implode(', ', $categorie) ?: 'Nessuna';
                            ?>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="anim_id" value="<?= $anim['ID'] ?>">
                                <button type="submit" name="delete_anim" class="btn btn-danger" onclick="return confirm('Sei sicuro?')">Elimina</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Handle checkbox behavior for days
        document.querySelectorAll('input[name="anim_m"], input[name="anim_j"], input[name="anim_s"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    this.value = 'X';
                } else {
                    this.value = this.id.toUpperCase();
                }
            });
        });
    </script>
</body>
</html>