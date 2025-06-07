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

// Enhanced statistics
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
    'no_colore' => count(array_filter($animatori, fn($a) => $a['Colore'] == 'X')),
    'categoria_m' => count(array_filter($animatori, fn($a) => $a['M'] == 'M')),
    'categoria_j' => count(array_filter($animatori, fn($a) => $a['J'] == 'J')),
    'categoria_s' => count(array_filter($animatori, fn($a) => $a['S'] == 'S'))
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
    <title>ORA 2025 - Dashboard Animatori</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #7c3aed;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #06b6d4;
            --light-bg: #f8fafc;
            --dark-text: #1e293b;
            --light-text: #64748b;
            --border-color: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--dark-text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-link {
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: var(--dark-text);
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background: var(--primary-color);
            color: white;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-color);
        }

        .card-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-text);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 1rem;
            text-align: center;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: scale(1.05);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .filters-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-weight: 600;
            color: var(--dark-text);
            font-size: 0.9rem;
        }

        .filter-select {
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 0.5rem;
            background: white;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .table-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: var(--primary-color);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .table tr:hover {
            background: var(--light-bg);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-fascia-a { background: var(--success-color); color: white; }
        .badge-fascia-d { background: var(--info-color); color: white; }
        .badge-colore-b { background: var(--primary-color); color: white; }
        .badge-colore-r { background: var(--danger-color); color: white; }
        .badge-colore-g { background: var(--warning-color); color: white; }
        .badge-colore-a { background: #f97316; color: white; }
        .badge-gray { background: #6b7280; color: white; }

        .lab-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-color);
        }

        .resp-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--success-color);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .animator-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .animator-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .animator-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .animator-name {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }

        .animator-details {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .categories {
            font-size: 0.8rem;
            color: var(--light-text);
            margin-top: 0.5rem;
        }

        .search-box {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            margin-bottom: 1rem;
            transition: border-color 0.3s ease;
        }

        .search-box:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .color-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .reset-filters {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .reset-filters:hover {
            background: #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--light-text);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .header-content {
                padding: 0 1rem;
                flex-direction: column;
                gap: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .animator-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-users"></i>
                ORA 2025 Dashboard
            </div>
            <div class="nav-links">
                <a href="edit.php" class="nav-link">
                    <i class="fas fa-edit"></i> Modifica
                </a>
                <a href="view.php" class="nav-link">
                    <i class="fas fa-eye"></i> Visualizza
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Section -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon" style="background: var(--primary-color);">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h2 class="card-title">Statistiche Generali</h2>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_laboratori'] ?></div>
                    <div class="stat-label">Laboratori</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_responsabili'] ?></div>
                    <div class="stat-label">Responsabili</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total_animatori'] ?></div>
                    <div class="stat-label">Animatori</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['fascia_a'] ?></div>
                    <div class="stat-label">Fascia A</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['fascia_d'] ?></div>
                    <div class="stat-label">Fascia D</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['categoria_m'] ?></div>
                    <div class="stat-label">Mini</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['categoria_j'] ?></div>
                    <div class="stat-label">Juniores</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['categoria_s'] ?></div>
                    <div class="stat-label">Seniores</div>
                </div>
            </div>
            
            <h3 style="margin-bottom: 1rem; color: var(--dark-text);">
                <i class="fas fa-palette"></i> Distribuzione Colori
            </h3>
            <div class="color-legend">
                <div class="legend-item">
                    <span class="badge badge-colore-b">Blu</span>
                    <span><?= $stats['colore_b'] ?> animatori</span>
                </div>
                <div class="legend-item">
                    <span class="badge badge-colore-r">Rosso</span>
                    <span><?= $stats['colore_r'] ?> animatori</span>
                </div>
                <div class="legend-item">
                    <span class="badge badge-colore-g">Giallo</span>
                    <span><?= $stats['colore_g'] ?> animatori</span>
                </div>
                <div class="legend-item">
                    <span class="badge badge-colore-a">Arancione</span>
                    <span><?= $stats['colore_a'] ?> animatori</span>
                </div>
                <div class="legend-item">
                    <span class="badge badge-gray">Non assegnato</span>
                    <span><?= $stats['no_colore'] ?> animatori</span>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="color: var(--dark-text); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-filter"></i> Filtri di Ricerca
                </h2>
                <button class="reset-filters" onclick="resetFilters()">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
            
            <input type="text" id="search-box" class="search-box" placeholder="🔍 Cerca per nome, cognome o laboratorio...">
            
            <div class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label">Laboratorio:</label>
                    <select id="filter-lab" class="filter-select">
                        <option value="">Tutti i laboratori</option>
                        <?php foreach ($laboratori as $lab): ?>
                        <option value="<?= htmlspecialchars($lab['Nome']) ?>"><?= htmlspecialchars($lab['Nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Fascia:</label>
                    <select id="filter-fascia" class="filter-select">
                        <option value="">Tutte le fasce</option>
                        <option value="A">Fascia A</option>
                        <option value="D">Fascia D</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Colore:</label>
                    <select id="filter-colore" class="filter-select">
                        <option value="">Tutti i colori</option>
                        <option value="B">Blu</option>
                        <option value="R">Rosso</option>
                        <option value="G">Giallo</option>
                        <option value="A">Arancione</option>
                        <option value="X">Non assegnato</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Categoria:</label>
                    <select id="filter-categoria" class="filter-select">
                        <option value="">Tutte le categorie</option>
                        <option value="M">Mini</option>
                        <option value="J">Juniores</option>
                        <option value="S">Seniores</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Animatori per Laboratorio -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon" style="background: var(--primary-color);">
                    <i class="fas fa-flask"></i>
                </div>
                <h2 class="card-title">Animatori per Laboratorio</h2>
            </div>
            
            <div id="lab-sections">
                <?php foreach ($animatori_per_laboratorio as $lab_nome => $animatori_lab): ?>
                <div class="lab-section" data-lab="<?= htmlspecialchars($lab_nome) ?>">
                    <div class="section-title">
                        <i class="fas fa-flask"></i>
                        <?= htmlspecialchars($lab_nome) ?> 
                        <span style="color: var(--light-text); font-weight: normal;">
                            (<span class="lab-count"><?= count($animatori_lab) ?></span> animatori)
                        </span>
                    </div>
                    <div class="animator-grid">
                        <?php foreach ($animatori_lab as $anim): ?>
                        <div class="animator-card" 
                             data-lab="<?= htmlspecialchars($lab_nome) ?>" 
                             data-fascia="<?= $anim['Fascia'] ?>" 
                             data-colore="<?= $anim['Colore'] ?>" 
                             data-m="<?= $anim['M'] ?>" 
                             data-j="<?= $anim['J'] ?>" 
                             data-s="<?= $anim['S'] ?>"
                             data-search="<?= htmlspecialchars(strtolower($anim['Nome'] . ' ' . $anim['Cognome'] . ' ' . $anim['LaboratorioNome'])) ?>">
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
        <div class="table-container">
            <div class="card-header" style="background: var(--info-color); color: white; margin: 0; border-radius: 0;">
                <div class="card-icon" style="background: rgba(255,255,255,0.2);">
                    <i class="fas fa-flask"></i>
                </div>
                <h2 class="card-title" style="color: white;">Laboratori</h2>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-tag"></i> Nome</th>
                        <th><i class="fas fa-info-circle"></i> Descrizione</th>
                        <th><i class="fas fa-users"></i> Numero Animatori</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laboratori as $lab): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($lab['Nome']) ?></strong></td>
                        <td><?= htmlspecialchars($lab['Descrizione']) ?></td>
                        <td>
                            <span class="badge" style="background: var(--primary-color); color: white;">
                                <?= $lab['NumAnimatori'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabella Responsabili -->
        <div class="table-container">
            <div class="card-header" style="background: var(--success-color); color: white; margin: 0; border-radius: 0;">
                <div class="card-icon" style="background: rgba(255,255,255,0.2);">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h2 class="card-title" style="color: white;">Responsabili</h2>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> Nome</th>
                        <th><i class="fas fa-info-circle"></i> Descrizione</th>
                        <th><i class="fas fa-users"></i> Numero Animatori</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($responsabili as $resp): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($resp['Nome']) ?></strong></td>
                        <td><?= htmlspecialchars($resp['Descrizione']) ?></td>
                        <td>
                            <span class="badge" style="background: var(--success-color); color: white;">
                                <?= $resp['NumAnimatori'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Enhanced filter functionality with search
        function applyFilters() {
            const searchTerm = document.getElementById('search-box').value.toLowerCase();
            const filterLab = document.getElementById('filter-lab').value;
            const filterFascia = document.getElementById('filter-fascia').value;
            const filterColore = document.getElementById('filter-colore').value;
            const filterCategoria = document.getElementById('filter-categoria').value;
            
            let visibleCount = 0;
            
            // Filter animator cards
            document.querySelectorAll('.animator-card').forEach(card => {
                let show = true;
                
                // Search filter
                if (searchTerm && !card.dataset.search.includes(searchTerm)) {
                    show = false;
                }
                
                // Other filters
                if (filterLab && card.dataset.lab !== filterLab) show = false;
                if (filterFascia && card.dataset.fascia !== filterFascia) show = false;
                if (filterColore && card.dataset.colore !== filterColore) show = false;
                if (filterCategoria) {
                    const hasCategoria = card.dataset[filterCategoria.toLowerCase()] === filterCategoria;
                    if (!hasCategoria) show = false;
                }
                
                card.style.display = show ? 'block' : 'none';
                if (show) visibleCount++;
            });
            
            // Filter table rows
            document.querySelectorAll('#animatori-table tbody tr').forEach(row => {
                let show = true;
                
                // Search filter
                if (searchTerm && !row.dataset.search.includes(searchTerm)) {
                    show = false;
                }
                
                // Other filters
                if (filterLab && row.dataset.lab !== filterLab) show = false;
                if (filterFascia && row.dataset.fascia !== filterFascia) show = false;
                if (filterColore && row.dataset.colore !== filterColore) show = false;
                if (filterCategoria) {
                    const hasCategoria = row.dataset[filterCategoria.toLowerCase()] === filterCategoria;
                    if (!hasCategoria) show = false;
                }
                
                row.style.display = show ? '' : 'none';
            });
            
            // Update lab section visibility and counts
            document.querySelectorAll('.lab-section').forEach(section => {
                const visibleCards = section.querySelectorAll('.animator-card:not([style*="none"])').length;
                section.style.display = visibleCards > 0 ? 'block' : 'none';
                
                // Update count
                const countElement = section.querySelector('.lab-count');
                if (countElement) {
                    countElement.textContent = visibleCards;
                }
            });
            
            // Show/hide no results message
            const noResults = document.getElementById('no-results');
            const labSections = document.getElementById('lab-sections');
            if (visibleCount === 0) {
                noResults.style.display = 'block';
                labSections.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                labSections.style.display = 'block';
            }
        }
        
        function resetFilters() {
            document.getElementById('search-box').value = '';
            document.getElementById('filter-lab').value = '';
            document.getElementById('filter-fascia').value = '';
            document.getElementById('filter-colore').value = '';
            document.getElementById('filter-categoria').value = '';
            applyFilters();
        }
        
        // Add event listeners
        document.getElementById('search-box').addEventListener('input', applyFilters);
        document.getElementById('filter-lab').addEventListener('change', applyFilters);
        document.getElementById('filter-fascia').addEventListener('change', applyFilters);
        document.getElementById('filter-colore').addEventListener('change', applyFilters);
        document.getElementById('filter-categoria').addEventListener('change', applyFilters);
        
        // Smooth scroll for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Add loading animation
        window.addEventListener('load', function() {
            document.querySelectorAll('.card, .table-container').forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    element.style.transition = 'all 0.6s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        
        // Enhanced table interactions
        document.querySelectorAll('.table tbody tr').forEach(row => {
            row.addEventListener('click', function() {
                // Remove previous selections
                document.querySelectorAll('.table tbody tr').forEach(r => r.classList.remove('selected'));
                // Add selection to current row
                this.classList.add('selected');
            });
        });
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('search-box').focus();
            }
            // Escape to clear search
            if (e.key === 'Escape') {
                resetFilters();
            }
        });
    </script>
    
    <style>
        .table tbody tr.selected {
            background: var(--primary-color) !important;
            color: white;
        }
        
        .table tbody tr.selected .badge {
            opacity: 0.9;
        }
        
        /* Loading states */
        .card, .table-container {
            opacity: 0;
            transform: translateY(20px);
        }
        
        /* Responsive improvements */
        @media (max-width: 1024px) {
            .table-container {
                overflow-x: auto;
            }
            
            .table {
                min-width: 800px;
            }
        }
        
        /* Print styles */
        @media print {
            .header, .filters-section, .nav-links {
                display: none !important;
            }
            
            .card, .table-container {
                box-shadow: none !important;
                border: 1px solid #ccc;
            }
            
            body {
                background: white !important;
            }
        }
        
        /* Accessibility improvements */
        .filter-select:focus,
        .search-box:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .nav-link:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --light-bg: #1e293b;
                --dark-text: #f1f5f9;
                --light-text: #94a3b8;
                --border-color: #334155;
            }
        }
    </style>
</body>
</html> htmlspecialchars(strtolower($anim['Nome'] . ' ' . $anim['Cognome'] . ' ' . $lab_nome)) ?>">
                            <div class="animator-name">
                                <?= htmlspecialchars($anim['Nome'] . ' ' . $anim['Cognome']) ?>
                            </div>
                            <div class="animator-details">
                                <span class="badge badge-fascia-<?= strtolower($anim['Fascia']) ?>">
                                    <?= $anim['Fascia'] ?>
                                </span>
                                <?php if ($anim['Colore'] != 'X'): ?>
                                <span class="badge badge-colore-<?= strtolower($anim['Colore']) ?>">
                                    <?= $anim['Colore'] ?>
                                </span>
                                <?php else: ?>
                                <span class="badge badge-gray">No colore</span>
                                <?php endif; ?>
                            </div>
                            <div class="categories">
                                <i class="fas fa-tags"></i>
                                <?php 
                                $categorie = [];
                                if ($anim['M'] == 'M') $categorie[] = 'Mini';
                                if ($anim['J'] == 'J') $categorie[] = 'Juniores';
                                if ($anim['S'] == 'S') $categorie[] = 'Seniores';
                                echo implode(', ', $categorie) ?: 'Nessuna categoria';
                                ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div id="no-results" class="empty-state" style="display: none;">
                <i class="fas fa-search"></i>
                <h3>Nessun risultato trovato</h3>
                <p>Prova a modificare i filtri di ricerca</p>
            </div>
        </div>

        <!-- Animatori per Responsabile -->
        <?php if (!empty($animatori_per_responsabile)): ?>
        <div class="card">
            <div class="card-header">
                <div class="card-icon" style="background: var(--success-color);">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h2 class="card-title">Animatori per Responsabile</h2>
            </div>
            
            <?php foreach ($animatori_per_responsabile as $resp_nome => $animatori_resp): ?>
            <div class="resp-section">
                <div class="section-title">
                    <i class="fas fa-user-tie"></i>
                    <?= htmlspecialchars($resp_nome) ?> 
                    <span style="color: var(--light-text); font-weight: normal;">
                        (<?= count($animatori_resp) ?> animatori)
                    </span>
                </div>
                <div class="animator-grid">
                    <?php foreach ($animatori_resp as $rel): ?>
                    <div class="animator-card">
                        <div class="animator-name">
                            <?= htmlspecialchars($rel['AnimatoreNome'] . ' ' . $rel['AnimatoreCognome']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Tabella Completa Animatori -->
        <div class="table-container">
            <div class="card-header" style="background: var(--primary-color); color: white; margin: 0; border-radius: 0;">
                <div class="card-icon" style="background: rgba(255,255,255,0.2);">
                    <i class="fas fa-table"></i>
                </div>
                <h2 class="card-title" style="color: white;">Elenco Completo Animatori</h2>
            </div>
            <table class="table" id="animatori-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> Nome</th>
                        <th><i class="fas fa-user"></i> Cognome</th>
                        <th><i class="fas fa-flask"></i> Laboratorio</th>
                        <th><i class="fas fa-layer-group"></i> Fascia</th>
                        <th><i class="fas fa-palette"></i> Colore</th>
                        <th><i class="fas fa-tags"></i> Categorie</th>
                        <th><i class="fas fa-user-tie"></i> Responsabili</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($animatori as $anim): ?>
                    <tr data-lab="<?= htmlspecialchars($anim['LaboratorioNome']) ?>" 
                        data-fascia="<?= $anim['Fascia'] ?>" 
                        data-colore="<?= $anim['Colore'] ?>" 
                        data-m="<?= $anim['M'] ?>" 
                        data-j="<?= $anim['J'] ?>" 
                        data-s="<?= $anim['S'] ?>"
                        data-search="<?=                        data-search="<?= htmlspecialchars(strtolower($anim['Nome'] . ' ' . $anim['Cognome'] . ' ' . $anim['LaboratorioNome'])) ?>">
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