<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$member_id = (int)($_GET['id'] ?? 0);
if (!$member_id) {
    setFlashMessage('error', 'Invalid member ID.');
    header('Location: members.php');
    exit();
}

// Get member data
$member = fetchOne("SELECT * FROM members WHERE id = ?", [$member_id]);
if (!$member) {
    setFlashMessage('error', 'Member not found.');
    header('Location: members.php');
    exit();
}

// Get attendance history
$attendance_sql = "SELECT m.meeting_date, m.meeting_type, m.topic, a.attended
                   FROM meetings m
                   LEFT JOIN attendance a ON m.id = a.meeting_id AND a.member_id = ?
                   ORDER BY m.meeting_date DESC
                   LIMIT 20";
$attendance_history = fetchAll($attendance_sql, [$member_id]);

// Calculate attendance statistics
$total_meetings = count($attendance_history);
$attended_meetings = count(array_filter($attendance_history, function($a) { return $a['attended'] == 1; }));
$attendance_rate = $total_meetings > 0 ? round(($attended_meetings / $total_meetings) * 100, 1) : 0;

// Calculate age and next birthday
$age = $member['date_of_birth'] ? calculateAge($member['date_of_birth']) : null;
$next_birthday = null;
$days_until_birthday = null;

if ($member['date_of_birth']) {
    $next_birthday = new DateTime($member['date_of_birth']);
    $next_birthday->setDate(date('Y'), $next_birthday->format('m'), $next_birthday->format('d'));
    
    // If birthday has passed this year, set to next year
    if ($next_birthday < new DateTime()) {
        $next_birthday->setDate(date('Y') + 1, $next_birthday->format('m'), $next_birthday->format('d'));
    }
    
    $days_until_birthday = $next_birthday->diff(new DateTime())->days;
}

include __DIR__ . '/includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Member | Church Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Member Details</h1>
            <div>
                <a href="edit_member.php?id=<?= $member_id ?>" class="btn btn-primary">Edit Member</a>
                <a href="members.php" class="btn btn-secondary">Back to Members</a>
            </div>
        </div>

        <?php displayFlashMessage(); ?>

        <div class="row">
            <!-- Member Information -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Full Name:</strong></div>
                            <div class="col-sm-8"><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Location:</strong></div>
                            <div class="col-sm-8"><?= htmlspecialchars($member['location']) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Program of Study:</strong></div>
                            <div class="col-sm-8"><?= htmlspecialchars($member['program_of_study'] ?? 'Not specified') ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Contact Number:</strong></div>
                            <div class="col-sm-8">
                                <?php if (!empty($member['contact_number'])): ?>
                                    <a href="tel:<?= htmlspecialchars($member['contact_number']) ?>">
                                        <?= htmlspecialchars($member['contact_number']) ?>
                                    </a>
                                <?php else: ?>
                                    Not provided
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Email:</strong></div>
                            <div class="col-sm-8">
                                <?php if (!empty($member['email'])): ?>
                                    <a href="mailto:<?= htmlspecialchars($member['email']) ?>">
                                        <?= htmlspecialchars($member['email']) ?>
                                    </a>
                                <?php else: ?>
                                    Not provided
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Date of Birth:</strong></div>
                            <div class="col-sm-8">
                                <?php if ($member['date_of_birth']): ?>
                                    <?= formatDate($member['date_of_birth'], 'F j, Y') ?>
                                    <br><small class="text-muted">(<?= formatDate($member['date_of_birth'], 'l') ?>)</small>
                                <?php else: ?>
                                    Not provided
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Join Date:</strong></div>
                            <div class="col-sm-8"><?= formatDate($member['join_date'], 'F j, Y') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Birthday Information -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Birthday Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($member['date_of_birth']): ?>
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Age:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge bg-info fs-6"><?= $age ?> years old</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Next Birthday:</strong></div>
                                <div class="col-sm-8">
                                    <?php if ($days_until_birthday === 0): ?>
                                        <span class="text-success fw-bold fs-6">🎉 Today!</span>
                                    <?php else: ?>
                                        <?= $next_birthday->format('F j, Y') ?>
                                        <br><small class="text-muted">(<?= $days_until_birthday ?> days from now)</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Birthday Status:</strong></div>
                                <div class="col-sm-8">
                                    <?php if ($days_until_birthday === 0): ?>
                                        <span class="badge bg-success">Today!</span>
                                    <?php elseif ($days_until_birthday <= 7): ?>
                                        <span class="badge bg-warning">Coming Soon</span>
                                    <?php elseif ($days_until_birthday <= 30): ?>
                                        <span class="badge bg-info">This Month</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Later</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted">No birthday information available.</p>
                                <a href="edit_member.php?id=<?= $member_id ?>" class="btn btn-primary">Add Birthday</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="dashboard-card text-center">
                    <h3>Total Meetings</h3>
                    <div class="number"><?= $total_meetings ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card text-center">
                    <h3>Meetings Attended</h3>
                    <div class="number"><?= $attended_meetings ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card text-center">
                    <h3>Attendance Rate</h3>
                    <div class="number"><?= $attendance_rate ?>%</div>
                </div>
            </div>
        </div>

        <!-- Recent Attendance History -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Attendance History</h5>
            </div>
            <div class="card-body">
                <?php if (count($attendance_history) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Meeting Type</th>
                                    <th>Topic</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_history as $attendance): ?>
                                    <tr>
                                        <td><?= formatDate($attendance['meeting_date'], 'M j, Y') ?></td>
                                        <td><?= htmlspecialchars($attendance['meeting_type']) ?></td>
                                        <td><?= htmlspecialchars($attendance['topic'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($attendance['attended'] == 1): ?>
                                                <span class="badge bg-success">Present</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Absent</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted">No attendance records found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 