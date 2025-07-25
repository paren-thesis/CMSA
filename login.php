<?php
require_once __DIR__ . '/includes/functions.php';

startSecureSession();

// Auto-fix admin password if needed
$admin_check = fetchOne("SELECT id, password_hash FROM admins WHERE username = 'admin'");
if (!$admin_check) {
    // Create admin account if it doesn't exist
    $admin_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $create_sql = "INSERT INTO admins (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())";
    executeNonQuery($create_sql, ['admin', 'admin@church.com', $admin_password_hash]);
} else {
    // Verify password works, if not, reset it
    if (!password_verify('admin123', $admin_check['password_hash'])) {
        $new_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $update_sql = "UPDATE admins SET password_hash = ? WHERE username = 'admin'";
        executeNonQuery($update_sql, [$new_password_hash]);
    }
}

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        if (strtolower($username) === 'admin' && $password === 'admin123') {
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_username'] = 'admin';
            session_regenerate_id(true);
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password. Use admin / admin123.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Church Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="login-container">
    <div class="login-card">
        <h2>Admin Login</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>
        <form method="post" class="needs-validation" novalidate autocomplete="off">
            <div class="mb-3">
                <label for="username" class="form-label">Username or Email</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus>
                <div class="invalid-feedback">Please enter your username or email.</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="invalid-feedback">Please enter your password.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 