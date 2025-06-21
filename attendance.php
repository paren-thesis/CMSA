<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

// Handle meeting creation
if (isset($_POST['create_meeting'])) {
    $meeting_date = $_POST['meeting_date'];
    $meeting_type = sanitizeInput($_POST['meeting_type']);
    $topic = sanitizeInput($_POST['topic'] ?? '');
    
    if (empty($meeting_date) || empty($meeting_type)) {
        setFlashMessage('error', 'Meeting date and type are required.');
    } else {
        $sql = "INSERT INTO meetings (meeting_date, meeting_type, topic) VALUES (?, ?, ?)";
        if (executeNonQuery($sql, [$meeting_date, $meeting_type, $topic])) {
            setFlashMessage('success', 'Meeting created successfully.');
        } else {
            setFlashMessage('error', 'Error creating meeting.');
        }
    }
    header('Location: attendance.php');
    exit();
}

// Handle attendance recording
if (isset($_POST['record_attendance'])) {
    $meeting_id = (int)$_POST['meeting_id'];
    $attendance_data = $_POST['attendance'] ?? [];
    
    // Delete existing attendance for this meeting
    executeNonQuery("DELETE FROM attendance WHERE meeting_id = ?", [$meeting_id]);
    
    // Insert new attendance records
    $success = true;
    foreach ($attendance_data as $member_id => $attended) {
        $sql = "INSERT INTO attendance (member_id, meeting_id, attended) VALUES (?, ?, ?)";
        if (!executeNonQuery($sql, [$member_id, $meeting_id, $attended ? 1 : 0])) {
            $success = false;
        }
    }
    
    if ($success) {
        setFlashMessage('success', 'Attendance recorded successfully.');
    } else {
        setFlashMessage('error', 'Error recording attendance.');
    }
    header('Location: attendance.php');
    exit();
}

// Get search/filter parameters
$date_filter = $_GET['date'] ?? '';
$meeting_type_filter = sanitizeInput($_GET['meeting_type'] ?? '');
$member_filter = (int)($_GET['member_id'] ?? 0);

// Build query for meetings
$where_conditions = [];
$params = [];

if (!empty($date_filter)) {
    $where_conditions[] = "meeting_date = ?";
    $params[] = $date_filter;
}

if (!empty($meeting_type_filter)) {
    $where_conditions[] = "meeting_type = ?";
    $params[] = $meeting_type_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get meetings with attendance counts
$sql = "SELECT m.*, 
        COUNT(a.id) as total_attendance,
        SUM(CASE WHEN a.attended = 1 THEN 1 ELSE 0 END) as present_count
        FROM meetings m 
        LEFT JOIN attendance a ON m.id = a.meeting_id 
        $where_clause 
        GROUP BY m.id 
        ORDER BY m.meeting_date DESC, m.id DESC";

$meetings = fetchAll($sql, $params);

// Get all members for attendance recording
$members = fetchAll("SELECT id, first_name, last_name FROM members ORDER BY first_name, last_name");

// Get unique meeting types for filter
$meeting_types = fetchAll("SELECT DISTINCT meeting_type FROM meetings ORDER BY meeting_type");

// Get unique meeting dates for filter
$meeting_dates = fetchAll("SELECT DISTINCT meeting_date FROM meetings ORDER BY meeting_date DESC");

include __DIR__ . '/includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management | Church Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Attendance Management</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMeetingModal">
                Create New Meeting
            </button>
        </div>

        <?php displayFlashMessage(); ?>

        <!-- Search and Filter Section -->
        <div class="search-box">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="date" class="form-label">Filter by Date</label>
                    <select class="form-select" id="date" name="date">
                        <option value="">All Dates</option>
                        <?php foreach ($meeting_dates as $date): ?>
                            <option value="<?= $date['meeting_date'] ?>" 
                                    <?= $date_filter === $date['meeting_date'] ? 'selected' : '' ?>>
                                <?= formatDate($date['meeting_date'], 'M j, Y') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="meeting_type" class="form-label">Filter by Type</label>
                    <select class="form-select" id="meeting_type" name="meeting_type">
                        <option value="">All Types</option>
                        <?php foreach ($meeting_types as $type): ?>
                            <option value="<?= htmlspecialchars($type['meeting_type']) ?>" 
                                    <?= $meeting_type_filter === $type['meeting_type'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['meeting_type']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <a href="attendance.php" class="btn btn-secondary w-100">Clear</a>
                </div>
            </form>
        </div>

        <!-- Meetings Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Meetings and Attendance (<?= count($meetings) ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (count($meetings) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Topic</th>
                                    <th>Attendance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($meetings as $meeting): ?>
                                    <tr>
                                        <td>
                                            <strong><?= formatDate($meeting['meeting_date'], 'M j, Y') ?></strong>
                                            <br><small class="text-muted"><?= formatDate($meeting['meeting_date'], 'l') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($meeting['meeting_type']) ?></td>
                                        <td><?= htmlspecialchars($meeting['topic'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-success"><?= $meeting['present_count'] ?? 0 ?> Present</span>
                                            <span class="badge bg-secondary"><?= ($meeting['total_attendance'] ?? 0) - ($meeting['present_count'] ?? 0) ?> Absent</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="recordAttendance(<?= $meeting['id'] ?>, '<?= htmlspecialchars($meeting['meeting_type']) ?>', '<?= formatDate($meeting['meeting_date'], 'M j, Y') ?>')">
                                                    Record Attendance
                                                </button>
                                                <a href="view_attendance.php?meeting_id=<?= $meeting['id'] ?>" 
                                                   class="btn btn-outline-secondary">
                                                    View Details
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted">No meetings found.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMeetingModal">
                            Create First Meeting
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create Meeting Modal -->
    <div class="modal fade" id="createMeetingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="meeting_date" class="form-label">Meeting Date *</label>
                            <input type="date" class="form-control" id="meeting_date" name="meeting_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="meeting_type" class="form-label">Meeting Type *</label>
                            <select class="form-select" id="meeting_type" name="meeting_type" required>
                                <option value="">Select Type</option>
                                <?php foreach (getMeetingTypes() as $key => $value): ?>
                                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="topic" class="form-label">Topic/Notes</label>
                            <textarea class="form-control" id="topic" name="topic" rows="3" placeholder="Optional meeting topic or notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_meeting" class="btn btn-primary">Create Meeting</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Record Attendance Modal -->
    <div class="modal fade" id="recordAttendanceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="attendanceForm">
                    <input type="hidden" name="meeting_id" id="meetingId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6 id="meetingInfo"></h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceTable">
                                    <?php foreach ($members as $member): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></td>
                                            <td>
                                                <input type="radio" name="attendance[<?= $member['id'] ?>]" value="1" checked>
                                            </td>
                                            <td>
                                                <input type="radio" name="attendance[<?= $member['id'] ?>]" value="0">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="record_attendance" class="btn btn-primary">Save Attendance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function recordAttendance(meetingId, meetingType, meetingDate) {
            document.getElementById('meetingId').value = meetingId;
            document.getElementById('meetingInfo').textContent = meetingType + ' - ' + meetingDate;
            new bootstrap.Modal(document.getElementById('recordAttendanceModal')).show();
        }
    </script>
</body>
</html> 