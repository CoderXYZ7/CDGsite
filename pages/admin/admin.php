<?php
include '../../config.php';
checkAuth();
checkTag('admin');

// Check database availability
$db_available = ($db !== null);

if ($db_available) {
    // Add User
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $tag = $_POST['tag'];

        try {
            $stmt = $db->prepare("INSERT INTO users (username, password, tag) VALUES (?, ?, ?)");
            $stmt->execute([$username, $password, $tag]);
            $success_message = "User added successfully!";
        } catch (Exception $e) {
            $error_message = "Failed to add user: " . $e->getMessage();
        }
    }

    // Update Tags
    if (isset($_POST['update_tags'])) {
        try {
            foreach ($_POST['tags'] as $userId => $tag) {
                $stmt = $db->prepare("UPDATE users SET tag = ? WHERE id = ?");
                $stmt->execute([$tag, $userId]);
            }
            $success_message = "User roles updated successfully!";
        } catch (Exception $e) {
            $error_message = "Failed to update user roles: " . $e->getMessage();
        }
    }

    // Get all users
    try {
        $users = $db->query("SELECT * FROM users")->fetchAll();
    } catch (Exception $e) {
        $users = [];
        $error_message = "Failed to load users: " . $e->getMessage();
    }
} else {
    $users = [];
    $error_message = "Database unavailable - User management features are disabled";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../static/css/styles.css">
</head>
<body>
    <div id="nav-placeholder"></div>
    <main class="main-wrapper">
        <div class="content">
            <section class="hero">
                <h1>Admin Panel</h1>
                <p>Add or manage user accounts</p>
            </section>

            <?php if (isset($success_message)): ?>
                <div class="success" style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 1rem; margin-bottom: 1rem; border-radius: 4px;">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="error" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 1rem; margin-bottom: 1rem; border-radius: 4px;">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <section class="card main-card">
                <div class="card-content">
                    <h3><i class="fas fa-user-plus"></i> Add User</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" placeholder="Username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Password" required>
                        </div>
                        <div class="form-group">
                            <label for="tag">User Role</label>
                            <select name="tag" id="tag">
                                <option value="admin">Admin</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                        <button type="submit" name="add_user" class="button primary">Add User</button>
                    </form>
                </div>
            </section>

            <section class="card main-card">
                <div class="card-content">
                    <h3><i class="fas fa-users-cog"></i> Manage Users</h3>
                    <form method="POST">
                        <table class="pdf-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td>
                                            <select name="tags[<?= $user['id'] ?>]">
                                                <option value="admin" <?= $user['tag'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="student" <?= $user['tag'] === 'student' ? 'selected' : '' ?>>Student</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="submit" name="update_tags" class="button primary" style="margin-top: 1rem;">Update Roles</button>
                    </form>
                </div>
            </section>
        </div>
    </main>
    <div id="admin-username" style="display: none;"><?= htmlspecialchars($_SESSION['username']) ?></div>
    <script src="assets/adminNav.js"></script>
</body>
</html>
