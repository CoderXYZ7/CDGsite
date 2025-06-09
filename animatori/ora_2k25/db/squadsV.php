<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);


$host = 'localhost';
$dbname = 'ora_2k25';
$username = 'lettore';
$password = 'password_lettore';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'move_person':
                $id = $input['id'];
                $colore = $input['colore'];
                $section = $input['section'];
                
                // Reset all sections first
                $stmt = $pdo->prepare("UPDATE Animatori SET M='X', J='X', S='X' WHERE ID = ?");
                $stmt->execute([$id]);
                
                // Update color
                $stmt = $pdo->prepare("UPDATE Animatori SET Colore = ? WHERE ID = ?");
                $stmt->execute([$colore, $id]);
                
                // Set the specific section if not unassigned
                if ($colore !== 'X' && $section !== '') {
                    $stmt = $pdo->prepare("UPDATE Animatori SET $section = ? WHERE ID = ?");
                    $stmt->execute([$section, $id]);
                }
                
                echo json_encode(['success' => true]);
                exit;
        }
    }
}

// Fetch all Animatori
$stmt = $pdo->query("SELECT ID, Nome, Cognome, Fascia, Colore, M, J, S FROM Animatori ORDER BY Nome, Cognome");
$Animatori = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize Animatori by squad and section
$squads = [];
$unassigned = [];

foreach ($Animatori as $person) {
    if ($person['Colore'] === 'X') {
        $unassigned[] = $person;
    } else {
        $squad = $person['Colore'];
        if (!isset($squads[$squad])) {
            $squads[$squad] = ['M' => [], 'J' => [], 'S' => []];
        }
        
        // Determine which section this person belongs to
        if ($person['M'] === 'M') {
            $squads[$squad]['M'][] = $person;
        } elseif ($person['J'] === 'J') {
            $squads[$squad]['J'][] = $person;
        } elseif ($person['S'] === 'S') {
            $squads[$squad]['S'][] = $person;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Squad Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 95%;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .squads-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 5px;
            margin-bottom: 30px;
        }
        
        .squad {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 5px;
        }
        
        .squad-header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            color: white;
        }
        
        .squad-R .squad-header { background-color: #e74c3c; }
        .squad-B .squad-header { background-color: #3498db; }
        .squad-A .squad-header { background-color: #ff8c00; }
        .squad-G .squad-header { background-color: #ffd700; color: #333; }
        
        .sections {
            display: flex;
            gap: 5px;
        }
        
        .section {
            flex: 1;
            min-height: 200px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 5px;
            padding: 10px;
        }
        
        .section.drag-over {
            border-color: #007bff;
            background-color: #e3f2fd;
        }
        
        .section-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 5px;
            background-color: #007bff;
            color: white;
            border-radius: 3px;
        }
        
        .person {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px;
            margin: 5px 0;
            cursor: move;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .person:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transform: translateY(-1px);
        }
        
        .person.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
        }
        
        .person-name {
            font-weight: bold;
            color: #333;
        }
        
        .person-fascia {
            font-size: 12px;
            color: #666;
            margin-top: 2px;
        }
        
        .unassigned-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .unassigned-header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #6c757d;
            color: white;
            border-radius: 5px;
        }
        
        .unassigned-area {
            min-height: 150px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 5px;
            padding: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .unassigned-area.drag-over {
            border-color: #6c757d;
            background-color: #f1f3f4;
        }
        
        .feedback {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Squadre oratorio 2k25</h1>
        
        <div class="squads-container">
            <?php foreach ($squads as $squadName => $sections): ?>
            <div class="squad squad-<?php echo $squadName; ?>">
                <div class="squad-header">
                    Squadra <?php echo $squadName; ?> 
                    <?php 
                    $squadColors = ['R' => '(Rosso)', 'B' => '(Blu)', 'A' => '(Arancione)', 'G' => '(Giallo)'];
                    echo isset($squadColors[$squadName]) ? $squadColors[$squadName] : '';
                    ?>
                </div>
                <div class="sections">
                    <?php foreach (['M', 'J', 'S'] as $sectionName): ?>
                    <div class="section" data-squad="<?php echo $squadName; ?>" data-section="<?php echo $sectionName; ?>">
                        <div class="section-title"><?php echo $sectionName; ?></div>
                        <?php foreach ($sections[$sectionName] as $person): ?>
                        <div class="person" draggable="true" data-id="<?php echo $person['ID']; ?>">
                            <div class="person-name"><?php echo htmlspecialchars($person['Nome'] . ' ' . $person['Cognome']); ?></div>
                            <div class="person-fascia">Fascia: <?php echo htmlspecialchars($person['Fascia']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="unassigned-container">
            <div class="unassigned-header">Persone non assegnate</div>
            <div class="unassigned-area" data-squad="X" data-section="">
                <?php foreach ($unassigned as $person): ?>
                <div class="person" draggable="true" data-id="<?php echo $person['ID']; ?>">
                    <div class="person-name"><?php echo htmlspecialchars($person['Nome'] . ' ' . $person['Cognome']); ?></div>
                    <div class="person-fascia">Fascia: <?php echo htmlspecialchars($person['Fascia']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="feedback" id="feedback"></div>

    <script>
        let draggedElement = null;
        
        // Add event listeners to all Animatori
        document.querySelectorAll('.person').forEach(person => {
            person.addEventListener('dragstart', handleDragStart);
            person.addEventListener('dragend', handleDragEnd);
        });
        
        // Add event listeners to all drop zones
        document.querySelectorAll('.section, .unassigned-area').forEach(zone => {
            zone.addEventListener('dragover', handleDragOver);
            zone.addEventListener('dragenter', handleDragEnter);
            zone.addEventListener('dragleave', handleDragLeave);
            zone.addEventListener('drop', handleDrop);
        });
        
        /*function handleDragStart(e) {
            draggedElement = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.outerHTML);
        }*/
        
        function handleDragEnd(e) {
            this.classList.remove('dragging');
            draggedElement = null;
        }
        
        function handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            return false;
        }
        
        function handleDragEnter(e) {
            this.classList.add('drag-over');
        }
        
        function handleDragLeave(e) {
            this.classList.remove('drag-over');
        }
        
        function handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            
            this.classList.remove('drag-over');
            
            if (draggedElement !== null) {
                const personId = draggedElement.dataset.id;
                const targetSquad = this.dataset.squad;
                const targetSection = this.dataset.section;
                
                // Move the element visually
                this.appendChild(draggedElement);
                
                // Update database
                updatePersonPosition(personId, targetSquad, targetSection);
            }
            
            return false;
        }
        
        function updatePersonPosition(id, colore, section) {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'move_person',
                    id: id,
                    colore: colore,
                    section: section
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFeedback('Person moved successfully!');
                } else {
                    showFeedback('Error moving person', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFeedback('Error moving person', 'error');
            });
        }
        
        function showFeedback(message, type = 'success') {
            const feedback = document.getElementById('feedback');
            feedback.textContent = message;
            feedback.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';
            feedback.style.display = 'block';
            
            setTimeout(() => {
                feedback.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>