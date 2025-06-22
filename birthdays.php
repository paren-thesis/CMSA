<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

// Get filter parameters
$month_filter = (int)($_GET['month'] ?? date('n')); // Current month by default
$year_filter = (int)($_GET['year'] ?? date('Y')); // Current year by default
$view_type = $_GET['view'] ?? 'upcoming'; // upcoming, monthly, all

// Get birthday data based on view type
if ($view_type === 'upcoming') {
    $birthdays = getUpcomingBirthdays();
    $title = "Upcoming Birthdays (Next 30 Days)";
} elseif ($view_type === 'monthly') {
    $birthdays = getMonthlyBirthdays($month_filter);
    $title = "Birthdays in " . date('F', mktime(0, 0, 0, $month_filter, 1)) . " " . $year_filter;
} else {
    // All birthdays
    $sql = "SELECT id, first_name, last_name, date_of_birth, 
            MONTH(date_of_birth) as birth_month, DAY(date_of_birth) as birth_day
            FROM members 
            WHERE date_of_birth IS NOT NULL
            ORDER BY MONTH(date_of_birth), DAY(date_of_birth)";
    $birthdays = fetchAll($sql);
    $title = "All Birthdays";
}

// Get statistics
$total_members = getTotalMembers();
$members_with_birthdays = fetchOne("SELECT COUNT(*) as count FROM members WHERE date_of_birth IS NOT NULL")['count'];
$upcoming_count = count(getUpcomingBirthdays());
$current_month_birthdays = count(getMonthlyBirthdays(date('n')));

include __DIR__ . '/includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birthday Tracker | Church Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Birthday Tracker</h1>
            <div>
                <button onclick="CMS.printPage()" class="btn btn-secondary me-2">Print</button>
                <button onclick="CMS.exportToCSV('birthdaysTable', 'birthdays.csv')" class="btn btn-success">Export CSV</button>
            </div>
        </div>

        <?php displayFlashMessage(); ?>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3>Total Members</h3>
                    <div class="number"><?= $total_members ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3>With Birthdays</h3>
                    <div class="number"><?= $members_with_birthdays ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3>Upcoming (30 days)</h3>
                    <div class="number"><?= $upcoming_count ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <h3>This Month</h3>
                    <div class="number"><?= $current_month_birthdays ?></div>
                </div>
            </div>
        </div>

        <!-- Filter and View Options -->
        <div class="search-box">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">View Type</label>
                    <div class="btn-group w-100" role="group">
                        <a href="?view=upcoming" class="btn btn-outline-primary <?= $view_type === 'upcoming' ? 'active' : '' ?>">Upcoming</a>
                        <a href="?view=monthly" class="btn btn-outline-primary <?= $view_type === 'monthly' ? 'active' : '' ?>">Monthly</a>
                        <a href="?view=all" class="btn btn-outline-primary <?= $view_type === 'all' ? 'active' : '' ?>">All</a>
                    </div>
                </div>
                <?php if ($view_type === 'monthly'): ?>
                    <div class="col-md-3">
                        <label for="month" class="form-label">Month</label>
                        <select class="form-select" id="month" onchange="changeMonth()">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $month_filter == $m ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="year" class="form-label">Year</label>
                        <select class="form-select" id="year" onchange="changeYear()">
                            <?php for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++): ?>
                                <option value="<?= $y ?>" <?= $year_filter == $y ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Birthdays Display -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?= $title ?> (<?= count($birthdays) ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (count($birthdays) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="birthdaysTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Date of Birth</th>
                                    <th>Age</th>
                                    <th>Days Until Birthday</th>
                                    <th>Location</th>
                                    <th>Contact</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($birthdays as $birthday): ?>
                                    <?php 
                                    $age = calculateAge($birthday['date_of_birth']);
                                    
                                    // Get today's date
                                    $today = new DateTime();
                                    $today->setTime(0, 0, 0); // Set to start of day
                                    
                                    // Create birthday date for this year
                                    $birthday_date = new DateTime($birthday['date_of_birth']);
                                    $this_year_birthday = new DateTime();
                                    $this_year_birthday->setDate($today->format('Y'), $birthday_date->format('m'), $birthday_date->format('d'));
                                    $this_year_birthday->setTime(0, 0, 0);
                                    
                                    // Check if today is the birthday
                                    $is_today = ($today->format('m-d') === $birthday_date->format('m-d'));
                                    
                                    if ($is_today) {
                                        $days_until = 0;
                                    } else {
                                        // Calculate next birthday
                                        if ($this_year_birthday < $today) {
                                            // Birthday has passed this year, set to next year
                                            $next_birthday = new DateTime();
                                            $next_birthday->setDate($today->format('Y') + 1, $birthday_date->format('m'), $birthday_date->format('d'));
                                            $next_birthday->setTime(0, 0, 0);
                                        } else {
                                            // Birthday is still coming this year
                                            $next_birthday = $this_year_birthday;
                                        }
                                        
                                        $days_until = $today->diff($next_birthday)->days;
                                    }
                                    
                                    $is_soon = $days_until <= 7 && $days_until > 0;
                                    ?>
                                    <tr class="<?= $is_today ? 'table-success' : ($is_soon ? 'table-warning' : '') ?>">
                                        <td>
                                            <strong><?= htmlspecialchars($birthday['first_name'] . ' ' . $birthday['last_name']) ?></strong>
                                            <?php if ($is_today): ?>
                                                <span class="badge bg-success ms-2">Today!</span>
                                            <?php elseif ($is_soon): ?>
                                                <span class="badge bg-warning ms-2">Soon</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= formatDate($birthday['date_of_birth'], 'F j, Y') ?>
                                            <br><small class="text-muted"><?= formatDate($birthday['date_of_birth'], 'l') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= $age ?> years</span>
                                        </td>
                                        <td>
                                            <?php if ($is_today): ?>
                                                <span class="text-success fw-bold">🎉 Today!</span>
                                            <?php else: ?>
                                                <span class="<?= $is_soon ? 'text-warning fw-bold' : 'text-muted' ?>">
                                                    <?= $days_until ?> day(s)
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($birthday['location'] ?? '-') ?></td>
                                        <td>
                                            <?php if (!empty($birthday['contact_number'])): ?>
                                                <a href="tel:<?= htmlspecialchars($birthday['contact_number']) ?>">
                                                    <?= htmlspecialchars($birthday['contact_number']) ?>
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="view_member.php?id=<?= $birthday['id'] ?>" 
                                                   class="btn btn-outline-primary" title="View Member">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (!empty($birthday['email'])): ?>
                                                    <a href="mailto:<?= htmlspecialchars($birthday['email']) ?>" 
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
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted">No birthdays found for the selected criteria.</p>
                        <?php if ($view_type === 'monthly'): ?>
                            <p>Try selecting a different month or year.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Birthday Calendar View (Optional) -->
        <?php if ($view_type === 'monthly'): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Birthday Calendar - <?= date('F Y', mktime(0, 0, 0, $month_filter, 1, $year_filter)) ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php 
                        $days_in_month = date('t', mktime(0, 0, 0, $month_filter, 1, $year_filter));
                        $month_birthdays = getMonthlyBirthdays($month_filter);
                        
                        // Create a lookup array for birthdays
                        $birthday_lookup = [];
                        foreach ($month_birthdays as $birthday) {
                            $day = (int)$birthday['day'];
                            if (!isset($birthday_lookup[$day])) {
                                $birthday_lookup[$day] = [];
                            }
                            $birthday_lookup[$day][] = $birthday;
                        }
                        ?>
                        
                        <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                            <div class="col-md-2 col-sm-3 col-4 mb-3">
                                <div class="card <?= isset($birthday_lookup[$day]) ? 'border-success' : 'border-light' ?>">
                                    <div class="card-body text-center p-2">
                                        <h6 class="card-title mb-1"><?= $day ?></h6>
                                        <?php if (isset($birthday_lookup[$day])): ?>
                                            <?php foreach ($birthday_lookup[$day] as $birthday): ?>
                                                <small class="d-block text-success">
                                                    <?= htmlspecialchars($birthday['first_name']) ?>
                                                </small>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function changeMonth() {
            const month = document.getElementById('month').value;
            const year = document.getElementById('year').value;
            window.location.href = `birthdays.php?view=monthly&month=${month}&year=${year}`;
        }
        
        function changeYear() {
            const month = document.getElementById('month').value;
            const year = document.getElementById('year').value;
            window.location.href = `birthdays.php?view=monthly&month=${month}&year=${year}`;
        }
    </script>
</body>
</html> 