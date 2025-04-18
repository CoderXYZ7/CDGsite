<?php
// Get list of PDF files
$pdf_files = glob("pdfs/*.pdf");

// Sort PDFs by filename (date) in descending order
usort($pdf_files, function($a, $b) {
    return strcmp(basename($b), basename($a));
});

// Get the most recent PDF
$most_recent_pdf = !empty($pdf_files) ? $pdf_files[0] : null;
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="icon" type="image/png" href="../../static/images/LogoNoBG.png" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Viewer</title>
    <link rel="stylesheet" href="../../static/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Additional styles specific to PDF viewer */
        .pdf-viewer {
            width: 100%;
            border: 1px solid #ddd;
            margin: 10px 0;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .pdf-viewer embed {
            width: 100%;
            height: 600px;
        }
        
        .pdf-list table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .pdf-list th, .pdf-list td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e1e4e8;
        }
        
        .pdf-list th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .pdf-viewer embed {
                height: 400px;
            }
        }
        
        @media (max-width: 480px) {
            .pdf-viewer embed {
                height: 300px;
            }
            
            .pdf-list th, .pdf-list td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Side Navigation -->
    <nav class="main-nav">
        <div class="logo">
            <img src="../../static/images/LogoNoBG.png" alt="Logo">
            <h1>PDF Viewer</h1>
        </div>
        
        <ul class="nav-list">
            <li><a href="index.php" class="active"><i class="fas fa-file-pdf"></i> Visualizzatore PDF</a></li>
            <li><a href="admin.php"><i class="fas fa-lock"></i> Area Admin</a></li>
        </ul>
    </nav>

    <main class="main-wrapper">
        <div class="content">
            <section class="hero">
                <h1>Visualizzatore Documenti PDF</h1>
                <p>Consulta i documenti in formato PDF</p>
            </section>

            <?php if ($most_recent_pdf): ?>
                <section class="card main-card">
                    <div class="card-content">
                        <h3><i class="fas fa-file-pdf"></i> Documento più recente (<?php echo str_replace('.pdf', '', basename($most_recent_pdf)); ?>)</h3>
                        <div class="pdf-viewer">
                            <embed 
                                src="<?php echo $most_recent_pdf; ?>" 
                                type="application/pdf" 
                                width="100%" 
                                height="600px"
                            />
                        </div>
                        <div class="section-footer">
                            <a href="<?php echo $most_recent_pdf; ?>" download class="button primary">
                                <i class="fas fa-download"></i> Scarica Documento
                            </a>
                        </div>
                    </div>
                </section>
            <?php else: ?>
                <section class="card main-card">
                    <div class="card-content">
                        <p>Nessun documento PDF disponibile al momento.</p>
                    </div>
                </section>
            <?php endif; ?>

            <section class="card main-card">
                <div class="card-content">
                    <h3><i class="fas fa-list"></i> Tutti i Documenti</h3>
                    <?php if (!empty($pdf_files)): ?>
                        <div class="pdf-list">
                            <table>
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
                                            <td><?php echo str_replace('.pdf', '', $file_name); ?></td>
                                            <td>
                                                <a href="<?php echo $pdf; ?>" target="_blank" class="button primary small">
                                                    <i class="fas fa-eye"></i> Visualizza
                                                </a>
                                                <a href="<?php echo $pdf; ?>" download class="button primary small">
                                                    <i class="fas fa-download"></i> Scarica
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Nessun documento disponibile.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Mobile menu button -->
    <button class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.main-nav').classList.toggle('active');
        });
    </script>
</body>
</html>