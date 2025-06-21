<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug: Loading login page...</h2>";

try {
    require_once __DIR__ . '/includes/functions.php';
    echo "✅ Functions loaded<br>";
    
    startSecureSession();
    echo "✅ Session started<br>";
    
    // Redirect if already logged in
    if (isLoggedIn()) {
        echo "✅ User is logged in, redirecting...<br>";
        header('Location: dashboard.php');
        exit();
    }
    
    echo "✅ User not logged in, showing login form<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
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
        <p class="text-muted">Debug mode enabled</p>
        
        <?php if (isset($error) && $error): ?>
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
        
        <div class="mt-3">
            <small class="text-muted">
                Default: admin / admin123
            </small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 