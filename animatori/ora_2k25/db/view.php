<?php
// Database configuration
$host = 'localhost';
$dbname = 'ora_2k25';
$username = 'lettore';
$password = 'password_lettore';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch all data with relationships
$laboratori = $pdo->query("
    SELECT l.*, COUNT(a.ID) as NumAnimatori 
    FROM Laboratori l 
    LEFT JOIN Animatori a ON l.ID = a.Laboratorio 
    GROUP BY l.ID 
    ORDER BY l.Nome
")->fetchAll();

$responsabili = $pdo->query("
    SELECT r.*, COUNT(ar.AnimatoreID) as NumAnimatori 
    FROM Responsabili r 
    LEFT JOIN Animatori_Responsabili ar ON r.ID = ar.ResponsabileID 
    GROUP BY r.ID 
    ORDER BY r.Nome
")->fetchAll();

$animatori = $pdo->query("
    SELECT a.*, l.Nome as LaboratorioNome 
    FROM Animatori a 
    JOIN Laboratori l ON a.Laboratorio = l.ID 
    ORDER BY a.Cognome, a.Nome
")->fetchAll();

// Get animator-responsible relationships
$animatori_responsabili = $pdo->query("
    SELECT ar.*, a.Nome as AnimatoreNome, a.Cognome as AnimatoreCognome, r.Nome as ResponsabileNome
    FROM Animatori_Responsabili ar
    JOIN Animatori a ON ar.AnimatoreID = a.ID
    JOIN Responsabili r ON ar.ResponsabileID = r.ID
    ORDER BY a.Cognome, a.Nome
")->fetchAll();

// Statistics
$stats = [
    'total_laboratori' => count($laboratori),
    'total_responsabili' => count($responsabili),
    'total_animatori' => count($animatori),
    'fascia_a' => count(array_filter($animatori, fn($a) => $a['Fascia'] == 'A')),
    'fascia_d' => count(array_filter($animatori, fn($a) => $a['Fascia'] == 'D')),
    'colore_b' => count(array_filter($animatori, fn($a) => $a['Colore'] == 'B')),
    'colore_r' => count(array_filter($animatori, fn($a) => $a['Colore'] == 'R')),
    'colore_g' => count(array_filter($animatori, fn($a) => $a['Colore'] == 'G')),
    'colore_a' => count(array_filter($animatori, fn($a) => $a['Colore'] == 'A')),
    'no_colore' => count(array_filter($animatori, fn($a) => $a['Colore'] == 'X'))
];

// Group animators by laboratory
$animatori_per_laboratorio = [];
foreach ($animatori as $anim) {
    $animatori_per_laboratorio[$anim['LaboratorioNome']][] = $anim;
}

// Group animators by responsible
$animatori_per_responsabile = [];
foreach ($animatori_responsabili as $rel) {
    $animatori_per_responsabile[$rel['ResponsabileNome']][] = $rel;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizzazione Database ORA 2025</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; }
        .section { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .section h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-top: 0; }
        .section h3 { color: #555; margin-top: 25px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-card h3 { margin: 0; font-size: 2em; }
        .stat-card p { margin: 5px 0 0 0; opacity: 0.9; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background-color: #f8f9fa; font-weight: bold; }
        .table tr:hover { background-color: #f5f5f5; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; white-space: nowrap; }
        .badge-fascia-a { background-color: #28a745; color: white; }
        .badge-fascia-d { background-color: #17a2b8; color: white; }
        .badge-colore-b { background-color: #007bff; color: white; }
        .badge-colore-r { background-color: #dc3545; color: white; }
        .badge-colore-g { background-color: #ffd700; color: black; }
        .badge-colore-a { background-color: #ffc107; color: black; }
        .badge-gray { background-color: #6c757d; color: white; }
        .nav { display: flex; gap: 20px; margin-bottom: 30px; }
        .nav a { text-decoration: none; color: #007bff; font-weight: bold; }
        .nav a:hover { text-decoration: underline; }
        .lab-section { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background-color: #f8f9fa; }
        .resp-section { margin: 20px 0; padding: 15px; border-left: 4px solid #28a745; background-color: #f0f8f0; }
        .animator-card { display: inline-block; margin: 5px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: white; min-width: 200px; }
        .giorni-disponibili { font-size: 12px; color: #666; margin-top: 5px; }
        .filters { display: flex; gap: 15px; margin: 20px 0; flex-wrap: wrap; align-items: center; }
        .filter-group { display: flex; align-items: center; gap: 5px; }
        .filter-group label { font-weight: bold; }
        .filter-group select { padding: 5px; border: 1px solid #ddd; border-radius: 4px; }
        .color-legend { display: flex; gap: 10px; margin: 15px 0; flex-wrap: wrap; }
        .legend-item { display: flex; align-items: center; gap: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Visualizzazione Database ORA 2025</h1>
        
        <div class="nav">
            <a href="edit.php">Modifica</a>
            <a href="view.php">Visualizza</a>
        </div>

        <!-- Statistics Section -->
        <div class="section">
            <h2>Statistiche Generali</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?= $stats['total_laboratori'] ?></h3>
                    <p>Laboratori</p>
                </div>
                <div class="filter-group">
                    <label>Categoria:</label>
                    <select id="filter-categoria">
                        <option value="">Tutte</option>
                        <option value="M">Mini</option>
                        <option value="J">Juniores</option>
                        <option value="S">Seniores</option>
                    </select>
                </div>
            </div>
            <div class="stat-card">
                    <h3><?= $stats['total_responsabili'] ?></h3>
                    <p>Responsabili</p>
                </div>
                <div class="stat-card">
                    <h3><?= $stats['total_animatori'] ?></h3>
                    <p>Animatori</p>
                </div>
                <div class="stat-card">
                    <h3><?= $stats['fascia_a'] ?> / <?= $stats['fascia_d'] ?></h3>
                    <p>Fascia A / Fascia D</p>
                </div>
        </div>

        <!-- Animatori per Laboratorio -->
        <div class="section">
            <h2>Animatori per Laboratorio</h2>
            <?php foreach ($animatori_per_laboratorio as $lab_nome => $animatori_lab): ?>
            <div class="lab-section">
                <h3><?= htmlspecialchars($lab_nome) ?> (<?= count($animatori_lab) ?> animatori)</h3>
                <div>
                    <?php foreach ($animatori_lab as $anim): ?>
                    <div class="animator-card" data-lab="<?= htmlspecialchars($lab_nome) ?>" data-fascia="<?= $anim['Fascia'] ?>" data-colore="<?= $anim['Colore'] ?>" data-m="<?= $anim['M'] ?>" data-j="<?= $anim['J'] ?>" data-s="<?= $anim['S'] ?>">
                        <strong><?= htmlspecialchars($anim['Nome'] . ' ' . $anim['Cognome']) ?></strong><br>
                        <span class="badge badge-fascia-<?= strtolower($anim['Fascia']) ?>"><?= $anim['Fascia'] ?></span>
                        <?php if ($anim['Colore'] != 'X'): ?>
                        <span class="badge badge-colore-<?= strtolower($anim['Colore']) ?>"><?= $anim['Colore'] ?></span>
                        <?php else: ?>
                        <span class="badge badge-gray">No colore</span>
                        <?php endif; ?>
                        <div class="giorni-disponibili">
                            <?php 
                            $categorie = [];
                            if ($anim['M'] == 'M') $categorie[] = 'Mini';
                            if ($anim['J'] == 'J') $categorie[] = 'Juniores';
                            if ($anim['S'] == 'S') $categorie[] = 'Seniores';
                            echo 'Categorie: ' . (implode(', ', $categorie) ?: 'Nessuna');
                            ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Animatori per Responsabile -->
        <?php if (!empty($animatori_per_responsabile)): ?>
        <div class="section">
            <h2>Animatori per Responsabile</h2>
            <?php foreach ($animatori_per_responsabile as $resp_nome => $animatori_resp): ?>
            <div class="resp-section">
                <h3><?= htmlspecialchars($resp_nome) ?> (<?= count($animatori_resp) ?> animatori)</h3>
                <div>
                    <?php foreach ($animatori_resp as $rel): ?>
                    <span class="animator-card">
                        <?= htmlspecialchars($rel['AnimatoreNome'] . ' ' . $rel['AnimatoreCognome']) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Tabella Completa Animatori -->
        <div class="section">
            <h2>Elenco Completo Animatori</h2>
            <table class="table" id="animatori-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Laboratorio</th>
                        <th>Fascia</th>
                        <th>Colore</th>
                        <th>Categorie</th>
                        <th>Responsabili</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($animatori as $anim): ?>
                    <tr data-lab="<?= htmlspecialchars($anim['LaboratorioNome']) ?>" data-fascia="<?= $anim['Fascia'] ?>" data-colore="<?= $anim['Colore'] ?>" data-m="<?= $anim['M'] ?>" data-j="<?= $anim['J'] ?>" data-s="<?= $anim['S'] ?>">
                        <td><?= htmlspecialchars($anim['Nome']) ?></td>
                        <td><?= htmlspecialchars($anim['Cognome']) ?></td>
                        <td><?= htmlspecialchars($anim['LaboratorioNome']) ?></td>
                        <td><span class="badge badge-fascia-<?= strtolower($anim['Fascia']) ?>"><?= $anim['Fascia'] ?></span></td>
                        <td>
                            <?php if ($anim['Colore'] != 'X'): ?>
                            <span class="badge badge-colore-<?= strtolower($anim['Colore']) ?>"><?= $anim['Colore'] ?></span>
                            <?php else: ?>
                            <span class="badge badge-gray">Non assegnato</span>
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
                            <?php
                            $resp_animatore = array_filter($animatori_responsabili, fn($r) => $r['AnimatoreNome'] == $anim['Nome'] && $r['AnimatoreCognome'] == $anim['Cognome']);
                            $nomi_resp = array_map(fn($r) => $r['ResponsabileNome'], $resp_animatore);
                            echo implode(', ', $nomi_resp) ?: 'Nessuno';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabella Laboratori -->
        <div class="section">
            <h2>Laboratori</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Descrizione</th>
                        <th>Numero Animatori</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laboratori as $lab): ?>
                    <tr>
                        <td><?= htmlspecialchars($lab['Nome']) ?></td>
                        <td><?= htmlspecialchars($lab['Descrizione']) ?></td>
                        <td><?= $lab['NumAnimatori'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabella Responsabili -->
        <div class="section">
            <h2>Responsabili</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Descrizione</th>
                        <th>Numero Animatori</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($responsabili as $resp): ?>
                    <tr>
                        <td><?= htmlspecialchars($resp['Nome']) ?></td>
                        <td><?= htmlspecialchars($resp['Descrizione']) ?></td>
                        <td><?= $resp['NumAnimatori'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Filter functionality
        function applyFilters() {
            const filterLab = document.getElementById('filter-lab').value;
            const filterFascia = document.getElementById('filter-fascia').value;
            const filterColore = document.getElementById('filter-colore').value;
            const filterCategoria = document.getElementById('filter-categoria').value;
            
            // Filter animator cards
            document.querySelectorAll('.animator-card').forEach(card => {
                let show = true;
                
                if (filterLab && card.dataset.lab !== filterLab) show = false;
                if (filterFascia && card.dataset.fascia !== filterFascia) show = false;
                if (filterColore && card.dataset.colore !== filterColore) show = false;
                if (filterCategoria) {
                    const hasCategoria = card.dataset[filterCategoria.toLowerCase()] === filterCategoria;
                    if (!hasCategoria) show = false;
                }
                
                card.style.display = show ? 'inline-block' : 'none';
            });
            
            // Filter table rows
            document.querySelectorAll('#animatori-table tbody tr').forEach(row => {
                let show = true;
                
                if (filterLab && row.dataset.lab !== filterLab) show = false;
                if (filterFascia && row.dataset.fascia !== filterFascia) show = false;
                if (filterColore && row.dataset.colore !== filterColore) show = false;
                if (filterCategoria) {
                    const hasCategoria = row.dataset[filterCategoria.toLowerCase()] === filterCategoria;
                    if (!hasCategoria) show = false;
                }
                
                row.style.display = show ? '' : 'none';
            });
            
            // Update lab section visibility
            document.querySelectorAll('.lab-section').forEach(section => {
                const visibleCards = section.querySelectorAll('.animator-card[style*="inline-block"], .animator-card:not([style*="none"])').length;
                section.style.display = visibleCards > 0 ? 'block' : 'none';
            });
        }
        
        // Add event listeners to filters
        document.getElementById('filter-lab').addEventListener('change', applyFilters);
        document.getElementById('filter-fascia').addEventListener('change', applyFilters);
        document.getElementById('filter-colore').addEventListener('change', applyFilters);
        document.getElementById('filter-categoria').addEventListener('change', applyFilters);
    </script>
</body>
</html>
            </div>
        </div>

        <!-- Filters -->
        <div class="section">
            <h2>Filtri di Visualizzazione</h2>
            <div class="filters">
                <div class="filter-group">
                    <label>Laboratorio:</label>
                    <select id="filter-lab">
                        <option value="">Tutti</option>
                        <?php foreach ($laboratori as $lab): ?>
                        <option value="<?= htmlspecialchars($lab['Nome']) ?>"><?= htmlspecialchars($lab['Nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Fascia:</label>
                    <select id="filter-fascia">
                        <option value="">Tutte</option>
                        <option value="A">Fascia A</option>
                        <option value="D">Fascia D</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Colore:</label>
                    <select id="filter-colore">
                        <option value="">Tutti</option>
                        <option value="B">Blu</option>
                        <option value="R">Rosso</option>
                        <option value="G">Giallo</option>
                        <option value="A">Arancione</option>
                        <option value="X">Non assegnato</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Categoria:</label>
                    <select id="filter-categoria">
                        <option value="">Tutte</option>
                        <option value="M">M</option>
                        <option value="J">J</option>
                        <option value="S">S</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
