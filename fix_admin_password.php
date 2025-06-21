<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>Fix Admin Password</h2>";

// Generate a fresh password hash for 'admin123'
$new_password_hash = password_hash('admin123', PASSWORD_DEFAULT);

echo "New password hash generated: " . substr($new_password_hash, 0, 20) . "...<br>";

// Update the admin password
$sql = "UPDATE admins SET password_hash = ? WHERE username = 'admin'";

if (executeNonQuery($sql, [$new_password_hash])) {
    echo "✅ Admin password updated successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<br><a href='login.php' class='btn btn-primary'>Go to Login</a>";
    
    // Test the new password
    $admin_record = fetchOne("SELECT password_hash FROM admins WHERE username = 'admin'");
    if ($admin_record && password_verify('admin123', $admin_record['password_hash'])) {
        echo "<br>✅ Password verification test passed!";
    } else {
        echo "<br>❌ Password verification test failed!";
    }
} else {
    echo "❌ Error updating admin password!<br>";
}
?> 