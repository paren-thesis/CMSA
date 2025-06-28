<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$totalMembers = getTotalMembers();
$recentAttendance = getRecentAttendance(7); // Attendance in last 7 days
$upcomingBirthdays = getUpcomingBirthdays();

// Get member statistics by role
$roleStats = fetchAll("SELECT member_role, COUNT(*) as count FROM members GROUP BY member_role ORDER BY count DESC");

// Get member statistics by program level
$levelStats = fetchAll("SELECT program_level, COUNT(*) as count FROM members WHERE program_level IS NOT NULL GROUP BY program_level ORDER BY count DESC");

include __DIR__ . '/includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Church Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid mt-4">
        <h1 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</h1>
        <?php displayFlashMessage(); ?>
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3>Total Members</h3>
                    <div class="number"><?= $totalMembers ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3>Recent Attendance (7 days)</h3>
                    <div class="number"><?= $recentAttendance ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3>Upcoming Birthdays</h3>
                    <div class="number"><?= count($upcomingBirthdays) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3>Executive Members</h3>
                    <div class="number">
                        <?php 
                        $executiveCount = 0;
                        foreach ($roleStats as $stat) {
                            if ($stat['member_role'] === 'Executive') {
                                $executiveCount = $stat['count'];
                                break;
                            }
                        }
                        echo $executiveCount;
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">Upcoming Birthdays (Next 30 Days)</div>
                    <div class="card-body">
                        <?php if (count($upcomingBirthdays) > 0): ?>
                            <ul class="list-group">
                                <?php foreach ($upcomingBirthdays as $b): ?>
                                    <li class="list-group-item birthday-item d-flex justify-content-between align-items-center">
                                        <span><?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?></span>
                                        <span class="birthday-date">
                                            <?= formatDate($b['date_of_birth'], 'M j') ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-muted">No upcoming birthdays in the next 30 days.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">Members by Role</div>
                    <div class="card-body">
                        <?php if (count($roleStats) > 0): ?>
                            <ul class="list-group">
                                <?php foreach ($roleStats as $stat): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="badge <?= getRoleBadgeClass($stat['member_role']) ?> me-2">
                                            <?= htmlspecialchars($stat['member_role']) ?>
                                        </span>
                                        <span class="fw-bold"><?= $stat['count'] ?> members</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-muted">No role data available.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">Members by Program Level</div>
                    <div class="card-body">
                        <?php if (count($levelStats) > 0): ?>
                            <ul class="list-group">
                                <?php foreach ($levelStats as $stat): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="badge <?= getLevelBadgeClass($stat['program_level']) ?> me-2">
                                            <?= htmlspecialchars($stat['program_level']) ?>
                                        </span>
                                        <span class="fw-bold"><?= $stat['count'] ?> members</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-muted">No program level data available.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">Quick Links</div>
                    <div class="card-body">
                        <a href="members.php" class="btn btn-primary mb-2 w-100">Manage Members</a>
                        <a href="attendance.php" class="btn btn-primary mb-2 w-100">Record Attendance</a>
                        <a href="birthdays.php" class="btn btn-primary mb-2 w-100">View Birthdays</a>
                        <a href="logout.php" class="btn btn-danger w-100">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 