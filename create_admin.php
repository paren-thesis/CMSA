<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>Create Admin User</h2>";

// Check if admin already exists
$existing_admin = fetchOne("SELECT id FROM admins WHERE username = 'admin'");

if ($existing_admin) {
    echo "❌ Admin user already exists!<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
} else {
    // Create new admin
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO admins (username, email, password_hash) VALUES (?, ?, ?)";
    
    if (executeNonQuery($sql, ['admin', 'admin@church.com', $password_hash])) {
        echo "✅ Admin user created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "<a href='login.php'>Go to Login</a>";
    } else {
        echo "❌ Error creating admin user!<br>";
    }
}
?> 