<?php
/**
 * General Utility Functions
 * Church Management System
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Start session with security settings
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        session_start();
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    startSecureSession();
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Logout user
 */
function logout() {
    startSecureSession();
    session_destroy();
    header('Location: login.php');
    exit();
}

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Format date for display
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Calculate age from date of birth
 * @param string $dateOfBirth
 * @return int
 */
function calculateAge($dateOfBirth) {
    $dob = new DateTime($dateOfBirth);
    $now = new DateTime();
    $age = $now->diff($dob);
    return $age->y;
}

/**
 * Get upcoming birthdays (next 30 days)
 * @return array
 */
function getUpcomingBirthdays() {
    $sql = "SELECT id, first_name, last_name, date_of_birth, 
            DATEDIFF(DATE_FORMAT(date_of_birth, '%Y-%m-%d'), CURDATE()) as days_until_birthday
            FROM members 
            WHERE DATE_FORMAT(date_of_birth, '%m-%d') BETWEEN 
                  DATE_FORMAT(CURDATE(), '%m-%d') AND 
                  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 30 DAY), '%m-%d')
            ORDER BY DATE_FORMAT(date_of_birth, '%m-%d')";
    
    return fetchAll($sql);
}

/**
 * Get monthly birthdays
 * @param int $month
 * @return array
 */
function getMonthlyBirthdays($month = null) {
    if ($month === null) {
        $month = date('n');
    }
    
    $sql = "SELECT id, first_name, last_name, date_of_birth, 
            DATE_FORMAT(date_of_birth, '%d') as day
            FROM members 
            WHERE MONTH(date_of_birth) = ?
            ORDER BY DAY(date_of_birth)";
    
    return fetchAll($sql, [$month]);
}

/**
 * Get total member count
 * @return int
 */
function getTotalMembers() {
    $sql = "SELECT COUNT(*) as total FROM members";
    $result = fetchOne($sql);
    return $result['total'] ?? 0;
}

/**
 * Get recent attendance count
 * @param int $days
 * @return int
 */
function getRecentAttendance($days = 7) {
    $sql = "SELECT COUNT(DISTINCT a.member_id) as total
            FROM attendance a
            JOIN meetings m ON a.meeting_id = m.id
            WHERE m.meeting_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            AND a.attended = 1";
    
    $result = fetchOne($sql, [$days]);
    return $result['total'] ?? 0;
}

/**
 * Generate random string
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

/**
 * Display flash message
 * @param string $type
 * @param string $message
 */
function setFlashMessage($type, $message) {
    startSecureSession();
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlashMessage() {
    startSecureSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = $flash['type'];
        $message = $flash['message'];
        $alertClass = $type === 'success' ? 'alert-success' : 'alert-danger';
        
        echo "<div class='alert {$alertClass} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}

/**
 * Validate required fields
 * @param array $data
 * @param array $required
 * @return array
 */
function validateRequired($data, $required) {
    $errors = [];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    
    return $errors;
}

/**
 * Get meeting types
 * @return array
 */
function getMeetingTypes() {
    return [
        'Sunday Service' => 'Sunday Service',
        'Bible Study' => 'Bible Study',
        'Prayer Meeting' => 'Prayer Meeting',
        'Youth Meeting' => 'Youth Meeting',
        'Choir Practice' => 'Choir Practice',
        'Other' => 'Other'
    ];
}

/**
 * Get member roles
 * @return array
 */
function getMemberRoles() {
    return [
        'Member' => 'Member',
        'Executive' => 'Executive'
    ];
}

/**
 * Get program levels
 * @return array
 */
function getProgramLevels() {
    return [
        'Freshman' => 'Freshman',
        'Continuing (2)' => 'Continuing (2)',
        'Continuing (3)' => 'Continuing (3)',
        'Final Year (3)' => 'Final Year (3)',
        'Final Year (4)' => 'Final Year (4)',
        'Top Up' => 'Top Up',
        'Other' => 'Other'
    ];
}

/**
 * Get role badge class for styling
 * @param string $role
 * @return string
 */
function getRoleBadgeClass($role) {
    switch ($role) {
        case 'Executive':
            return 'bg-primary';
        case 'Member':
        default:
            return 'bg-secondary';
    }
}

/**
 * Get level badge class for styling
 * @param string $level
 * @return string
 */
function getLevelBadgeClass($level) {
    switch ($level) {
        case 'Freshman':
            return 'bg-danger';
        case 'Continuing (2)':
            return 'bg-warning';
        case 'Continuing (3)':
            return 'bg-info';
        case 'Final Year (3)':
            return 'bg-success';
        case 'Final Year (4)':
            return 'bg-primary';
        case 'Top Up':
            return 'bg-secondary';
        default:
            return 'bg-light text-dark';
    }
}
?> 