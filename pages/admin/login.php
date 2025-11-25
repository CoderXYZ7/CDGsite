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
    <link rel="stylesheet" href="../../static/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="card login-card">
        <div class="card-content">
            <h1 style="text-align: center; color: var(--primary-color); margin-bottom: 2rem;">Admin Login</h1>

            <?php if (DB_BYPASS): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Development Mode: Database bypass enabled. Use admin/admin to login.
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
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
                        <button type="button" class="toggle-password" aria-label="Show password"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                
                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="remember" name="remember" style="width: auto;">
                    <label for="remember" style="margin: 0;">Remember me</label>
                </div>
                
                <button type="submit" class="button primary" style="width: 100%;">Login</button>
            </form>
        </div>
    </div>
    
    <script src="assets/login.js"></script>
</body>
</html>
