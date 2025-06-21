<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>Database Connection Test</h2>";

// Test connection
try {
    $conn = getDBConnection();
    if ($conn) {
        echo "✅ Database connection successful!<br>";
        
        // Check if admins table exists
        $result = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
        if (mysqli_num_rows($result) > 0) {
            echo "✅ Admins table exists<br>";
            
            // Check admin records
            $admins = fetchAll("SELECT id, username, email FROM admins");
            echo "📊 Found " . count($admins) . " admin(s):<br>";
            foreach ($admins as $admin) {
                echo "- ID: {$admin['id']}, Username: {$admin['username']}, Email: {$admin['email']}<br>";
            }
            
            // Test password verification
            $test_password = 'admin123';
            $admin_record = fetchOne("SELECT password_hash FROM admins WHERE username = 'admin'");
            if ($admin_record) {
                if (password_verify($test_password, $admin_record['password_hash'])) {
                    echo "✅ Password verification works!<br>";
                } else {
                    echo "❌ Password verification failed!<br>";
                    echo "Hash in database: " . substr($admin_record['password_hash'], 0, 20) . "...<br>";
                }
            } else {
                echo "❌ No admin user found with username 'admin'<br>";
            }
            
        } else {
            echo "❌ Admins table does not exist<br>";
        }
        
    } else {
        echo "❌ Database connection failed!<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Show database configuration
echo "<h3>Database Configuration:</h3>";
echo "Host: " . DB_HOST . "<br>";
echo "Database: " . DB_NAME . "<br>";
echo "User: " . DB_USER . "<br>";
echo "Password: " . (DB_PASS ? '***set***' : '***empty***') . "<br>";
?> 