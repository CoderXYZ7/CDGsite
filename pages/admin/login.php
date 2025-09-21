<?php
include '../../config.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $authenticated = false;

    // Check if database connection is available
    if ($db === null) {
        // Database connection failed
        if (DB_BYPASS) {
            // Development bypass: allow login with admin/admin
            if ($username === 'admin' && $password === 'admin') {
                $authenticated = true;
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'admin';
                $_SESSION['user_tag'] = 'admin';
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Database connection failed. Please check your configuration.";
        }
    } else {
        // Try database authentication
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $authenticated = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_tag'] = $user['tag'];
            } else {
                $error = "Invalid username or password";
                usleep(500000); // 0.5 second delay
            }
        } catch (Exception $e) {
            $error = "Database query failed: " . $e->getMessage();
        }
    }

    if ($authenticated) {
        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);
        header("Location: adminHub.php");
        exit();
    } elseif (!isset($error)) {
        // Generic error message to prevent username enumeration
        $error = "Invalid username or password";
        // Add delay to prevent brute force
        usleep(500000); // 0.5 second delay
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="assets/login.css">
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>

        <?php if (DB_BYPASS): ?>
            <div class="error" style="background-color: #fff3cd; color: #856404; border-color: #ffeaa7;">
                ⚠️ Development Mode: Database bypass enabled. Use admin/admin to login.
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="toggle-password" aria-label="Show password">👁️</button>
                </div>
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>
            
            <button type="submit" class="button">Login</button>
        </form>
    </div>
    
    <script src="assets/login.js"></script>
</body>
</html>
