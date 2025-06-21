<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$meeting_id = (int)($_GET['meeting_id'] ?? 0);
if (!$meeting_id) {
    setFlashMessage('error', 'Invalid meeting ID.');
    header('Location: attendance.php');
    exit();
}

// Get meeting details
$meeting = fetchOne("SELECT * FROM meetings WHERE id = ?", [$meeting_id]);
if (!$meeting) {
    setFlashMessage('error', 'Meeting not found.');
    header('Location: attendance.php');
    exit();
}

// Get attendance details for this meeting
$sql = "SELECT m.id, m.first_name, m.last_name, m.location, m.contact_number, m.email,
               a.attended, a.created_at
        FROM members m
        LEFT JOIN attendance a ON m.id = a.member_id AND a.meeting_id = ?
        ORDER BY m.first_name, m.last_name";

$attendance_details = fetchAll($sql, [$meeting_id]);

// Calculate statistics
$total_members = count($attendance_details);
$present_count = count(array_filter($attendance_details, function($a) { return $a['attended'] == 1; }));
$absent_count = count(array_filter($attendance_details, function($a) { return $a['attended'] == 0; }));
$not_recorded = $total_members - $present_count - $absent_count;
$attendance_rate = $total_members > 0 ? round(($present_count / $total_members) * 100, 1) : 0;

include __DIR__ . '/includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Attendance Details | Church Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Meeting Attendance Details</h1>
            <div>
                <button onclick="CMS.printPage()" class="btn btn-secondary me-2">Print</button>
                <button onclick="CMS.exportToCSV('attendanceTable', 'attendance_<?= $meeting_id ?>.csv')" class="btn btn-success me-2">Export CSV</button>
                <a href="attendance.php" class="btn btn-primary">Back to Attendance</a>
            </div>
        </div>

        <?php displayFlashMessage(); ?>

        <!-- Meeting Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Meeting Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Date:</strong><br>
                        <?= formatDate($meeting['meeting_date'], 'F j, Y') ?>
                        <br><small class="text-muted"><?= formatDate($meeting['meeting_date'], 'l') ?></small>
                    </div>
                    <div class="col-md-3">
                        <strong>Type:</strong><br>
                        <?= htmlspecialchars($meeting['meeting_type']) ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Topic:</strong><br>
                        <?= htmlspecialchars($meeting['topic'] ?? 'No topic specified') ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Created:</strong><br>
                        <?= formatDate($meeting['created_at'], 'M j, Y g:i A') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Statistics -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3>Total Members</h3>
                    <div class="number"><?= $total_members ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3>Present</h3>
                    <div class="number text-success"><?= $present_count ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3>Absent</h3>
                    <div class="number text-danger"><?= $absent_count ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3>Attendance Rate</h3>
                    <div class="number"><?= $attendance_rate ?>%</div>
                </div>
            </div>
        </div>

        <!-- Attendance Details Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Member Attendance (<?= $total_members ?> members)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="attendanceTable">
                        <thead>
                            <tr>
                                <th>Member Name</th>
                                <th>Location</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_details as $member): ?>
                                <tr class="<?= $member['attended'] == 1 ? 'table-success' : ($member['attended'] == 0 ? 'table-danger' : 'table-light') ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($member['location']) ?></td>
                                    <td>
                                        <?php if (!empty($member['contact_number'])): ?>
                                            <a href="tel:<?= htmlspecialchars($member['contact_number']) ?>">
                                                <?= htmlspecialchars($member['contact_number']) ?>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($member['attended'] == 1): ?>
                                            <span class="badge bg-success">Present</span>
                                        <?php elseif ($member['attended'] == 0): ?>
                                            <span class="badge bg-danger">Absent</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Recorded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="view_member.php?id=<?= $member['id'] ?>" 
                                               class="btn btn-outline-primary" title="View Member">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (!empty($member['email'])): ?>
                                                <a href="mailto:<?= htmlspecialchars($member['email']) ?>" 
                                                   class="btn btn-outline-success" title="Send Email">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <a href="attendance.php" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-arrow-left"></i> Back to Attendance
                        </a>
                    </div>
                    <div class="col-md-4">
                        <button onclick="CMS.printPage()" class="btn btn-secondary w-100 mb-2">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button onclick="CMS.exportToCSV('attendanceTable', 'attendance_<?= $meeting_id ?>.csv')" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 