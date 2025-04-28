<?php 
include 'config.php';
checkAuth();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../static/css/styles.css">
    <style>
        :root {
            --primary-color: #1a365d;
            --accent-color: #e53e3e;
            --nav-width: 280px;
            --attention-color1: #e53e3e;
            --attention-color2: #ff6a3d;
        }

        .attention {
            color: white;
            width: 90%;
            padding: 10px;
            margin-right: 8px;
            border-radius: 8px;
            margin: 10px auto 2rem;
            background-color: var(--attention-color1);
            animation: pulse 2s infinite alternate;
        }

        @keyframes pulse {
            0% {
                background-color: var(--attention-color1);
            }
            50% {
                background-color: var(--attention-color2);
            }
            100% {
                background-color: var(--attention-color1);
            }
        }
    </style>
</head>
<body>
    <div id="nav-placeholder"></div>
    <main class="main-wrapper">
        <h1>Welcome <?= htmlspecialchars($_SESSION['username']) ?></h1>
        <h2>Available Pages:</h2>
        <?php
        $stmt = $db->prepare("SELECT * FROM pages");
        $stmt->execute();
        $pages = $stmt->fetchAll();
        
        foreach ($pages as $page) {
            $allowed = explode(',', $page['allowed_tags']);
            if (in_array($_SESSION['user_tag'], $allowed)) {
                echo "<a href='{$page['path']}'>{$page['page_name']}</a><br>";
            }
        }
        ?>
        <!-- notice of partial functionality -->
        <div class="attention">
            <p>Notice: Attualmente solo la pagina FOGLIETTO è disponibile.</p>
        </div>
        <a href="logout.php">Logout</a>
        <div id="admin-username" style="display: none;">
    </main>
    <script src="adminNav.js"></script>
</body>
</html>