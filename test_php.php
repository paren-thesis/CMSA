<?php
echo "<h1>PHP Test</h1>";
echo "✅ PHP is working!<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "PHP version: " . phpversion() . "<br>";

// Test if we can include the functions file
try {
    require_once __DIR__ . '/includes/functions.php';
    echo "✅ Functions file loaded successfully!<br>";
} catch (Exception $e) {
    echo "❌ Error loading functions: " . $e->getMessage() . "<br>";
}

// Test database connection
try {
    require_once __DIR__ . '/config/database.php';
    $conn = getDBConnection();
    if ($conn) {
        echo "✅ Database connection successful!<br>";
    } else {
        echo "❌ Database connection failed!<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}
?> 