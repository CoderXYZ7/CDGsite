<?php
include 'config.php';
checkAuth();
checkTag('admin');

// Add User
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $tag = $_POST['tag'];
    
    $stmt = $db->prepare("INSERT INTO users (username, password, tag) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $tag]);
}

// Update Tags
if (isset($_POST['update_tags'])) {
    foreach ($_POST['tags'] as $userId => $tag) {
        $stmt = $db->prepare("UPDATE users SET tag = ? WHERE id = ?");
        $stmt->execute([$tag, $userId]);
    }
}

// Get all users
$users = $db->query("SELECT * FROM users")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
</head>
<body>
    <div id="nav-placeholder"></div>
    <main class="main-wrapper">
        <h1>Admin Panel</h1>
        
        <h2>Add User</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="tag">
                <option value="admin">Admin</option>
                <option value="student">Student</option>
            </select>
            <button type="submit" name="add_user">Add User</button>
        </form>

        <h2>Manage Users</h2>
        <form method="POST">
            <?php foreach ($users as $user): ?>
                <div>
                    <?= htmlspecialchars($user['username']) ?>
                    <select name="tags[<?= $user['id'] ?>]">
                        <option value="admin" <?= $user['tag'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="student" <?= $user['tag'] === 'student' ? 'selected' : '' ?>>Student</option>
                    </select>
                </div>
            <?php endforeach; ?>
            <button type="submit" name="update_tags">Update Tags</button>
        </form>
        
        <a href="hub.php">Back to Hub</a>
    </main>
    <div id="admin-username" style="display: none;"><?= htmlspecialchars($_SESSION['username']) ?></div>
    <script src="adminNav.js"></script>
</body>
</html>