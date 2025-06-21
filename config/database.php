<?php
/**
 * Database Configuration and Connection
 * Church Management System
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'church_management_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection
 * @return mysqli|false
 */
function getDBConnection() {
    static $connection = null;
    
    if ($connection === null) {
        $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if (!$connection) {
            die("Connection failed: " . mysqli_connect_error());
        }
        
        // Set charset
        mysqli_set_charset($connection, DB_CHARSET);
    }
    
    return $connection;
}

/**
 * Execute a prepared statement
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return mysqli_stmt|false
 */
function executeQuery($sql, $params = []) {
    $conn = getDBConnection();
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // Assume all strings for now
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        die("Execute failed: " . mysqli_stmt_error($stmt));
    }
    
    return $stmt;
}

/**
 * Fetch all results from a query
 * @param string $sql SQL query
 * @param array $params Parameters to bind
 * @return array
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $rows;
}

/**
 * Fetch single row from a query
 * @param string $sql SQL query
 * @param array $params Parameters to bind
 * @return array|null
 */
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_stmt_close($stmt);
    return $row;
}

/**
 * Execute INSERT, UPDATE, DELETE query
 * @param string $sql SQL query
 * @param array $params Parameters to bind
 * @return int|false Number of affected rows
 */
function executeNonQuery($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    $affectedRows = mysqli_stmt_affected_rows($stmt);
    
    mysqli_stmt_close($stmt);
    return $affectedRows;
}

/**
 * Get last inserted ID
 * @return int
 */
function getLastInsertId() {
    $conn = getDBConnection();
    return mysqli_insert_id($conn);
}

/**
 * Close database connection
 */
function closeDBConnection() {
    $conn = getDBConnection();
    mysqli_close($conn);
}

// Test database connection
function testConnection() {
    try {
        $conn = getDBConnection();
        if ($conn) {
            return true;
        }
    } catch (Exception $e) {
        return false;
    }
    return false;
}
?> 