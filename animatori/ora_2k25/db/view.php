<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ORA 2025 - Database Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #1abc9c;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-icon {
            font-size: 2rem;
            opacity: 0.8;
        }
        
        .section-header {
            border-left: 5px solid var(--primary);
            padding-left: 15px;
            margin-bottom: 25px;
        }
        
        .badge-fascia-a { background-color: var(--secondary); }
        .badge-fascia-d { background-color: var(--info); }
        .badge-colore-b { background-color: #3498db; }
        .badge-colore-r { background-color: var(--danger); }
        .badge-colore-g { background-color: #f1c40f; }
        .badge-colore-a { background-color: var(--warning); }
        
        .animator-card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
            border: 1px solid #e9ecef;
        }
        
        .animator-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }
        
        .laboratory-header {
            background-color: rgba(52, 152, 219, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .responsabile-header {
            background-color: rgba(46, 204, 113, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .filter-section {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .legend-item {
            display: inline-flex;
            align-items: center;
            margin-right: 15px;
            margin-bottom: 5px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 5px;
            display: inline-block;
        }
        
        .category-badge {
            font-size: 0.75rem;
            margin-right: 3px;
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary);
        }
        
        .tab-content {
            background-color: white;
            border-radius: 0 0 8px 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        @media (max-width: 768px) {
            .dashboard-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-people-fill me-2"></i>
                ORA 2025
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="bi bi-eye-fill me-1"></i> Visualizza</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="edit.php"><i class="bi bi-pencil-fill me-1"></i> Modifica</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Laboratori</h6>
                                <h2 class="card-title mb-0"><?= $stats['total_laboratori'] ?></h2>
                            </div>
                            <i class="bi bi-house-door card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Responsabili</h6>
                                <h2 class="card-title mb-0"><?= $stats['total_responsabili'] ?></h2>
                            </div>
                            <i class="bi bi-person-badge card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card text-white bg-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Animatori</h6>
                                <h2 class="card-title mb-0"><?= $stats['total_animatori'] ?></h2>
                            </div>
                            <i class="bi bi-people card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Fasce</h6>
                                <h2 class="card-title mb-0"><?= $stats['fascia_a'] ?> / <?= $stats['fascia_d'] ?></h2>
                                <small>A / D</small>
                            </div>
                            <i class="bi bi-tags card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filter-section mb-4">
            <h4 class="section-header">Filtri</h4>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Laboratorio</label>
                    <select class="form-select" id="filter-lab">
                        <option value="">Tutti</option>
                        <?php foreach ($laboratori as $lab): ?>
                        <option value="<?= htmlspecialchars($lab['Nome']) ?>"><?= htmlspecialchars($lab['Nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Fascia</label>
                    <select class="form-select" id="filter-fascia">
                        <option value="">Tutte</option>
                        <option value="A">Fascia A</option>
                        <option value="D">Fascia D</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Colore</label>
                    <select class="form-select" id="filter-colore">
                        <option value="">Tutti</option>
                        <option value="B">Blu</option>
                        <option value="R">Rosso</option>
                        <option value="G">Giallo</option>
                        <option value="A">Arancione</option>
                        <option value="X">Non assegnato</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Categoria</label>
                    <select class="form-select" id="filter-categoria">
                        <option value="">Tutte</option>
                        <option value="M">Mini</option>
                        <option value="J">Juniores</option>
                        <option value="S">Seniores</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button class="btn btn-outline-secondary w-100" id="reset-filters">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Resetta filtri
                    </button>
                </div>
            </div>
            
            <div class="mt-3">
                <h5 class="mb-3">Legenda colori:</h5>
                <div>
                    <span class="legend-item"><span class="legend-color bg-primary"></span> Blu (<?= $stats['colore_b'] ?>)</span>
                    <span class="legend-item"><span class="legend-color bg-danger"></span> Rosso (<?= $stats['colore_r'] ?>)</span>
                    <span class="legend-item"><span class="legend-color" style="background-color: #f1c40f;"></span> Giallo (<?= $stats['colore_g'] ?>)</span>
                    <span class="legend-item"><span class="legend-color bg-warning"></span> Arancione (<?= $stats['colore_a'] ?>)</span>
                    <span class="legend-item"><span class="legend-color bg-secondary"></span> Non assegnato (<?= $stats['no_colore'] ?>)</span>
                </div>
            </div>
        </div>

        <!-- Main Content Tabs -->
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-animatori-tab" data-bs-toggle="pill" data-bs-target="#pills-animatori" type="button">
                    <i class="bi bi-people me-1"></i> Animatori
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-laboratori-tab" data-bs-toggle="pill" data-bs-target="#pills-laboratori" type="button">
                    <i class="bi bi-house-door me-1"></i> Laboratori
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-responsabili-tab" data-bs-toggle="pill" data-bs-target="#pills-responsabili" type="button">
                    <i class="bi bi-person-badge me-1"></i> Responsabili
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="pills-tabContent">
            <!-- Animatori Tab -->
            <div class="tab-pane fade show active" id="pills-animatori" role="tabpanel">
                <div class="mb-5">
                    <h4 class="section-header">Per Laboratorio</h4>
                    <?php foreach ($animatori_per_laboratorio as $lab_nome => $animatori_lab): ?>
                    <div class="laboratory-header" data-lab="<?= htmlspecialchars($lab_nome) ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <?= htmlspecialchars($lab_nome) ?>
                                <span class="badge bg-primary rounded-pill"><?= count($animatori_lab) ?> animatori</span>
                            </h5>
                            <small class="text-muted"><?= $laboratori[array_search($lab_nome, array_column($laboratori, 'Nome'))]['Descrizione'] ?? '' ?></small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <?php foreach ($animatori_lab as $anim): ?>
                        <div class="col-md-4 mb-3 animator-card" 
                             data-lab="<?= htmlspecialchars($lab_nome) ?>" 
                             data-fascia="<?= $anim['Fascia'] ?>" 
                             data-colore="<?= $anim['Colore'] ?>" 
                             data-m="<?= $anim['M'] ?>" 
                             data-j="<?= $anim['J'] ?>" 
                             data-s="<?= $anim['S'] ?>">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?= htmlspecialchars($anim['Nome'] . ' ' . $anim['Cognome']) ?>
                                    </h5>
                                    <div class="mb-2">
                                        <span class="badge <?= $anim['Fascia'] == 'A' ? 'bg-success' : 'bg-info' ?> me-1">
                                            Fascia <?= $anim['Fascia'] ?>
                                        </span>
                                        <?php if ($anim['Colore'] != 'X'): ?>
                                        <span class="badge <?= 
                                            $anim['Colore'] == 'B' ? 'bg-primary' : 
                                            ($anim['Colore'] == 'R' ? 'bg-danger' : 
                                            ($anim['Colore'] == 'G' ? 'text-dark bg-warning' : 'bg-warning')) ?>">
                                            <?= $anim['Colore'] ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">No colore</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-2">
                                        <?php if ($anim['M'] == 'M'): ?>
                                        <span class="badge category-badge bg-info text-dark">Mini</span>
                                        <?php endif; ?>
                                        <?php if ($anim['J'] == 'J'): ?>
                                        <span class="badge category-badge bg-success">Juniores</span>
                                        <?php endif; ?>
                                        <?php if ($anim['S'] == 'S'): ?>
                                        <span class="badge category-badge bg-primary">Seniores</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    $resp_animatore = array_filter($animatori_responsabili, fn($r) => 
                                        $r['AnimatoreNome'] == $anim['Nome'] && $r['AnimatoreCognome'] == $anim['Cognome']);
                                    if (!empty($resp_animatore)): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Responsabili:</small>
                                        <div>
                                            <?php foreach ($resp_animatore as $rel): ?>
                                            <span class="badge bg-light text-dark"><?= $rel['ResponsabileNome'] ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (!empty($animatori_per_responsabile)): ?>
                <div class="mb-5">
                    <h4 class="section-header">Per Responsabile</h4>
                    <?php foreach ($animatori_per_responsabile as $resp_nome => $animatori_resp): ?>
                    <div class="responsabile-header">
                        <h5><?= htmlspecialchars($resp_nome) ?> <span class="badge bg-success rounded-pill"><?= count($animatori_resp) ?> animatori</span></h5>
                    </div>
                    
                    <div class="row">
                        <?php foreach ($animatori_resp as $rel): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <?= htmlspecialchars($rel['AnimatoreNome'] . ' ' . $rel['AnimatoreCognome']) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?= $animatori[array_search($rel['AnimatoreID'], array_column($animatori, 'ID'))]['LaboratorioNome'] ?? '' ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div>
                    <h4 class="section-header">Elenco Completo</h4>
                    <div class="table-responsive">
                        <table class="table table-hover" id="animatori-table">
                            <thead class="table-light">
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
                                <tr data-lab="<?= htmlspecialchars($anim['LaboratorioNome']) ?>" 
                                    data-fascia="<?= $anim['Fascia'] ?>" 
                                    data-colore="<?= $anim['Colore'] ?>" 
                                    data-m="<?= $anim['M'] ?>" 
                                    data-j="<?= $anim['J'] ?>" 
                                    data-s="<?= $anim['S'] ?>">
                                    <td><?= htmlspecialchars($anim['Nome']) ?></td>
                                    <td><?= htmlspecialchars($anim['Cognome']) ?></td>
                                    <td><?= htmlspecialchars($anim['LaboratorioNome']) ?></td>
                                    <td>
                                        <span class="badge <?= $anim['Fascia'] == 'A' ? 'bg-success' : 'bg-info' ?>">
                                            <?= $anim['Fascia'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($anim['Colore'] != 'X'): ?>
                                        <span class="badge <?= 
                                            $anim['Colore'] == 'B' ? 'bg-primary' : 
                                            ($anim['Colore'] == 'R' ? 'bg-danger' : 
                                            ($anim['Colore'] == 'G' ? 'text-dark bg-warning' : 'bg-warning')) ?>">
                                            <?= $anim['Colore'] ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Non assegnato</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $categorie = [];
                                        if ($anim['M'] == 'M') $categorie[] = '<span class="badge bg-info text-dark">Mini</span>';
                                        if ($anim['J'] == 'J') $categorie[] = '<span class="badge bg-success">Juniores</span>';
                                        if ($anim['S'] == 'S') $categorie[] = '<span class="badge bg-primary">Seniores</span>';
                                        echo implode(' ', $categorie) ?: '<span class="badge bg-light text-dark">Nessuna</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $resp_animatore = array_filter($animatori_responsabili, fn($r) => 
                                            $r['AnimatoreNome'] == $anim['Nome'] && $r['AnimatoreCognome'] == $anim['Cognome']);
                                        $nomi_resp = array_map(fn($r) => '<span class="badge bg-light text-dark">'.$r['ResponsabileNome'].'</span>', $resp_animatore);
                                        echo implode(' ', $nomi_resp) ?: '<span class="badge bg-light text-dark">Nessuno</span>';
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Laboratori Tab -->
            <div class="tab-pane fade" id="pills-laboratori" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Descrizione</th>
                                <th>Animatori</th>
                                <th>Dettagli</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laboratori as $lab): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($lab['Nome']) ?></strong></td>
                                <td><?= htmlspecialchars($lab['Descrizione']) ?></td>
                                <td>
                                    <span class="badge bg-primary rounded-pill"><?= $lab['NumAnimatori'] ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#labModal<?= $lab['ID'] ?>">
                                        <i class="bi bi-eye"></i> Visualizza
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Responsabili Tab -->
            <div class="tab-pane fade" id="pills-responsabili" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Descrizione</th>
                                <th>Animatori</th>
                                <th>Dettagli</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($responsabili as $resp): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($resp['Nome']) ?></strong></td>
                                <td><?= htmlspecialchars($resp['Descrizione']) ?></td>
                                <td>
                                    <span class="badge bg-success rounded-pill"><?= $resp['NumAnimatori'] ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#respModal<?= $resp['ID'] ?>">
                                        <i class="bi bi-eye"></i> Visualizza
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Laboratori Modals -->
    <?php foreach ($laboratori as $lab): ?>
    <div class="modal fade" id="labModal<?= $lab['ID'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= htmlspecialchars($lab['Nome']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?= htmlspecialchars($lab['Descrizione']) ?></p>
                    
                    <h6 class="mt-4">Animatori (<?= $lab['NumAnimatori'] ?>)</h6>
                    <?php if ($lab['NumAnimatori'] > 0): ?>
                    <div class="row">
                        <?php 
                        $lab_animatori = array_filter($animatori, fn($a) => $a['Laboratorio'] == $lab['ID']);
                        foreach ($lab_animatori as $anim): ?>
                        <div class="col-md-6 mb-2">
                            <div class="card animator-card">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($anim['Nome'] . ' ' . $anim['Cognome']) ?></strong>
                                            <div>
                                                <span class="badge <?= $anim['Fascia'] == 'A' ? 'bg-success' : 'bg-info' ?> me-1">
                                                    <?= $anim['Fascia'] ?>
                                                </span>
                                                <?php if ($anim['Colore'] != 'X'): ?>
                                                <span class="badge <?= 
                                                    $anim['Colore'] == 'B' ? 'bg-primary' : 
                                                    ($anim['Colore'] == 'R' ? 'bg-danger' : 
                                                    ($anim['Colore'] == 'G' ? 'text-dark bg-warning' : 'bg-warning')) ?>">
                                                    <?= $anim['Colore'] ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <?php 
                                            $categorie = [];
                                            if ($anim['M'] == 'M') $categorie[] = '<span class="badge bg-info text-dark">Mini</span>';
                                            if ($anim['J'] == 'J') $categorie[] = '<span class="badge bg-success">Juniores</span>';
                                            if ($anim['S'] == 'S') $categorie[] = '<span class="badge bg-primary">Seniores</span>';
                                            echo implode(' ', $categorie);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">Nessun animatore assegnato a questo laboratorio.</div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <!-- Responsabili Modals -->
    <?php foreach ($responsabili as $resp): ?>
    <div class="modal fade" id="respModal<?= $resp['ID'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= htmlspecialchars($resp['Nome']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?= htmlspecialchars($resp['Descrizione']) ?></p>
                    
                    <h6 class="mt-4">Animatori (<?= $resp['NumAnimatori'] ?>)</h6>
                    <?php if ($resp['NumAnimatori'] > 0): ?>
                    <div class="row">
                        <?php 
                        $resp_animatori = array_filter($animatori_responsabili, fn($r) => $r['ResponsabileID'] == $resp['ID']);
                        foreach ($resp_animatori as $rel): 
                            $anim = $animatori[array_search($rel['AnimatoreID'], array_column($animatori, 'ID'))];
                        ?>
                        <div class="col-md-6 mb-2">
                            <div class="card animator-card">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($rel['AnimatoreNome'] . ' ' . $rel['AnimatoreCognome']) ?></strong>
                                            <div>
                                                <span class="text-muted"><?= $anim['LaboratorioNome'] ?? '' ?></span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="badge <?= $anim['Fascia'] == 'A' ? 'bg-success' : 'bg-info' ?> me-1">
                                                <?= $anim['Fascia'] ?>
                                            </span>
                                            <?php if ($anim['Colore'] != 'X'): ?>
                                            <span class="badge <?= 
                                                $anim['Colore'] == 'B' ? 'bg-primary' : 
                                                ($anim['Colore'] == 'R' ? 'bg-danger' : 
                                                ($anim['Colore'] == 'G' ? 'text-dark bg-warning' : 'bg-warning')) ?>">
                                                <?= $anim['Colore'] ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">Nessun animatore assegnato a questo responsabile.</div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                
                card.style.display = show ? '' : 'none';
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
            document.querySelectorAll('.laboratory-header').forEach(section => {
                const labName = section.dataset.lab;
                const visibleCards = document.querySelectorAll(`.animator-card[data-lab="${labName}"][style*="block"], 
                                                              .animator-card[data-lab="${labName}"]:not([style*="none"])`).length;
                section.style.display = visibleCards > 0 ? '' : 'none';
            });
        }
        
        // Reset filters
        document.getElementById('reset-filters').addEventListener('click', function() {
            document.getElementById('filter-lab').value = '';
            document.getElementById('filter-fascia').value = '';
            document.getElementById('filter-colore').value = '';
            document.getElementById('filter-categoria').value = '';
            applyFilters();
        });
        
        // Add event listeners to filters
        document.getElementById('filter-lab').addEventListener('change', applyFilters);
        document.getElementById('filter-fascia').addEventListener('change', applyFilters);
        document.getElementById('filter-colore').addEventListener('change', applyFilters);
        document.getElementById('filter-categoria').addEventListener('change', applyFilters);
        
        // Initialize filters
        applyFilters();
    </script>
</body>
</html>