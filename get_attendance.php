<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

// Set JSON content type
header('Content-Type: application/json');

// Get meeting ID from request
$meeting_id = (int)($_GET['meeting_id'] ?? 0);

if (!$meeting_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Meeting ID is required']);
    exit();
}

try {
    // Get existing attendance data for this meeting
    $sql = "SELECT member_id, attended FROM attendance WHERE meeting_id = ?";
    $attendance_records = fetchAll($sql, [$meeting_id]);
    
    // Convert to associative array for easy lookup
    $attendance_data = [];
    foreach ($attendance_records as $record) {
        $attendance_data[$record['member_id']] = (bool)$record['attended'];
    }
    
    // Return the attendance data
    echo json_encode([
        'success' => true,
        'attendance' => $attendance_data
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 