<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

// Handle meeting deletion
if (isset($_POST['delete_meeting'])) {
    $meeting_id = (int)$_POST['meeting_id'];

    // Delete attendance records first (foreign key constraint)
    $delete_attendance = "DELETE FROM attendance WHERE meeting_id = ?";
    $attendance_deleted = executeNonQuery($delete_attendance, [$meeting_id]);

    // Delete the meeting
    $delete_meeting = "DELETE FROM meetings WHERE id = ?";
    $meeting_deleted = executeNonQuery($delete_meeting, [$meeting_id]);

    if ($meeting_deleted) {
        setFlashMessage('success', 'Meeting and all attendance records deleted successfully.');
    } else {
        setFlashMessage('error', 'Error deleting meeting. Please try again.');
    }

    header('Location: attendance.php');
    exit();
}

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
    $updated_members = [];
    foreach ($attendance_data as $member_id => $attended) {
        $sql = "INSERT INTO attendance (member_id, meeting_id, attended) VALUES (?, ?, ?)";
        if (executeNonQuery($sql, [$member_id, $meeting_id, $attended ? 1 : 0])) {
            $updated_members[] = $member_id;
        } else {
            $success = false;
        }
    }

    if ($success) {
        // Update activity status for all members who had attendance recorded
        foreach ($updated_members as $member_id) {
            updateMemberActivityStatus($member_id, 90, 60); // 90 days, 60% threshold
        }

        setFlashMessage('success', 'Attendance recorded successfully. Member activity status updated.');
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
                                                <button class="btn btn-outline-danger"
                                                    onclick="confirmDelete(<?= $meeting['id'] ?>, '<?= htmlspecialchars($meeting['meeting_type']) ?>', '<?= formatDate($meeting['meeting_date'], 'M j, Y') ?>')">
                                                    Delete
                                                </button>
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
                            <input type="date" class="form-control" id="meeting_date" name="meeting_date"
                                min="<?= date('Y-m-d', strtotime('-5 years')) ?>"
                                max="<?= date('Y-m-d', strtotime('+1 year')) ?>" required>
                            <div class="form-text">You can create meetings for any date, including previous days.</div>
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
                        
                        <!-- Search Box for Members -->
                        <div class="mb-3">
                            <label for="memberSearch" class="form-label">Search Members</label>
                            <input type="text" class="form-control" id="memberSearch" placeholder="Type to search members...">
                        </div>
                        
                        <div class="table-responsive">
                            <div id="attendanceLoading" class="attendance-loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading attendance data...</p>
                            </div>
                            <table class="table table-sm" id="attendanceTableContainer" style="display: none;">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceTable">
                                    <!-- Attendance rows will be loaded dynamically -->
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

    <!-- Delete Meeting Confirmation Modal -->
    <div class="modal fade" id="deleteMeetingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Confirm Delete Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="meeting_id" id="deleteMeetingId">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This action cannot be undone.
                        </div>
                        <p>Are you sure you want to delete the following meeting?</p>
                        <div class="card">
                            <div class="card-body">
                                <h6 id="deleteMeetingInfo"></h6>
                                <p class="text-muted mb-0">This will also delete all attendance records for this meeting.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_meeting" class="btn btn-danger">Delete Meeting</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Store all members data globally
        const allMembers = <?= json_encode($members) ?>;
        let currentAttendance = {};

        function recordAttendance(meetingId, meetingType, meetingDate) {
            document.getElementById('meetingId').value = meetingId;
            document.getElementById('meetingInfo').textContent = meetingType + ' - ' + meetingDate;
            
            // Load existing attendance data for this meeting
            loadAttendanceData(meetingId);
            
            new bootstrap.Modal(document.getElementById('recordAttendanceModal')).show();
        }

        function loadAttendanceData(meetingId) {
            // Show loading indicator
            document.getElementById('attendanceLoading').style.display = 'block';
            document.getElementById('attendanceTableContainer').style.display = 'none';
            
            // Fetch existing attendance data via AJAX
            fetch(`get_attendance.php?meeting_id=${meetingId}`)
                .then(response => response.json())
                .then(data => {
                    currentAttendance = data.attendance || {};
                    renderAttendanceTable(allMembers);
                    
                    // Hide loading and show table
                    document.getElementById('attendanceLoading').style.display = 'none';
                    document.getElementById('attendanceTableContainer').style.display = 'table';
                })
                .catch(error => {
                    console.error('Error loading attendance:', error);
                    currentAttendance = {};
                    renderAttendanceTable(allMembers);
                    
                    // Hide loading and show table
                    document.getElementById('attendanceLoading').style.display = 'none';
                    document.getElementById('attendanceTableContainer').style.display = 'table';
                });
        }

        function renderAttendanceTable(members) {
            const tbody = document.getElementById('attendanceTable');
            tbody.innerHTML = '';
            
            if (members.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="3" class="text-center text-muted">No members found matching your search.</td>';
                tbody.appendChild(row);
                return;
            }
            
            members.forEach(member => {
                const memberId = member.id;
                const memberName = member.first_name + ' ' + member.last_name;
                const isPresent = currentAttendance[memberId] !== undefined ? currentAttendance[memberId] : false; // Default to absent
                
                const row = document.createElement('tr');
                row.className = 'member-row';
                row.innerHTML = `
                    <td>${escapeHtml(memberName)}</td>
                    <td>
                        <input type="radio" name="attendance[${memberId}]" value="1" ${isPresent ? 'checked' : ''}>
                    </td>
                    <td>
                        <input type="radio" name="attendance[${memberId}]" value="0" ${!isPresent ? 'checked' : ''}>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function confirmDelete(meetingId, meetingType, meetingDate) {
            document.getElementById('deleteMeetingId').value = meetingId;
            document.getElementById('deleteMeetingInfo').textContent = meetingType + ' - ' + meetingDate;
            new bootstrap.Modal(document.getElementById('deleteMeetingModal')).show();
        }

        // Handle date input for past dates
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('meeting_date');
            if (dateInput) {
                // Set default value to today if empty
                if (!dateInput.value) {
                    dateInput.value = new Date().toISOString().split('T')[0];
                }

                // Remove any browser-enforced min attribute that might prevent past dates
                dateInput.addEventListener('input', function() {
                    // Allow any date input
                });

                // Override any browser validation that might prevent form submission
                const form = dateInput.closest('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const dateValue = dateInput.value;
                        if (dateValue) {
                            // Allow the form to submit with any valid date
                            return true;
                        }
                    });
                }
            }

            // Handle member search
            const memberSearch = document.getElementById('memberSearch');
            if (memberSearch) {
                memberSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const filteredMembers = allMembers.filter(member => {
                        const fullName = (member.first_name + ' ' + member.last_name).toLowerCase();
                        return fullName.includes(searchTerm);
                    });
                    renderAttendanceTable(filteredMembers);
                });
            }
        });
    </script>
</body>

</html>