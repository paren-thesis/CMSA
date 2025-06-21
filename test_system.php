<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Church Management System - Complete Test</h1>";

// Test 1: PHP Version
echo "<h2>1. PHP Environment</h2>";
echo "✅ PHP Version: " . phpversion() . "<br>";
echo "✅ Current Directory: " . __DIR__ . "<br>";

// Test 2: File Structure
echo "<h2>2. File Structure Check</h2>";
$required_files = [
    'config/database.php',
    'includes/functions.php',
    'includes/navbar.php',
    'assets/css/style.css',
    'assets/js/main.js',
    'login.php',
    'dashboard.php',
    'members.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

// Test 3: Database Connection
echo "<h2>3. Database Connection</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $conn = getDBConnection();
    if ($conn) {
        echo "✅ Database connection successful<br>";
        
        // Check tables
        $tables = ['admins', 'members', 'meetings', 'attendance'];
        foreach ($tables as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) > 0) {
                echo "✅ Table '$table' exists<br>";
            } else {
                echo "❌ Table '$table' missing<br>";
            }
        }
        
        // Check admin user
        $admin = fetchOne("SELECT id, username FROM admins WHERE username = 'admin'");
        if ($admin) {
            echo "✅ Admin user exists<br>";
        } else {
            echo "❌ Admin user missing<br>";
        }
        
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 4: Functions
echo "<h2>4. Functions Test</h2>";
try {
    require_once __DIR__ . '/includes/functions.php';
    echo "✅ Functions loaded successfully<br>";
    
    // Test session functions
    startSecureSession();
    echo "✅ Session started<br>";
    
    // Test utility functions
    $test_input = "  <script>alert('test')</script>  ";
    $sanitized = sanitizeInput($test_input);
    if ($sanitized === "&lt;script&gt;alert('test')&lt;/script&gt;") {
        echo "✅ Input sanitization working<br>";
    } else {
        echo "❌ Input sanitization failed<br>";
    }
    
    // Test email validation
    if (isValidEmail('test@example.com')) {
        echo "✅ Email validation working<br>";
    } else {
        echo "❌ Email validation failed<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Functions error: " . $e->getMessage() . "<br>";
}

// Test 5: Navigation
echo "<h2>5. Navigation Test</h2>";
echo "<a href='login.php'>Test Login Page</a><br>";
echo "<a href='dashboard.php'>Test Dashboard (should redirect to login if not authenticated)</a><br>";
echo "<a href='members.php'>Test Members (should redirect to login if not authenticated)</a><br>";

echo "<h2>6. System Status</h2>";
echo "🎯 <strong>System appears to be ready!</strong><br>";
echo "📝 Next steps:<br>";
echo "1. Go to <a href='login.php'>login.php</a><br>";
echo "2. Login with: admin / admin123<br>";
echo "3. If login fails, run <a href='fix_admin_password.php'>fix_admin_password.php</a><br>";

?> 