<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

// Handle member deletion
if (isset($_POST['delete_member'])) {
    $member_id = (int)$_POST['member_id'];
    $sql = "DELETE FROM members WHERE id = ?";
    if (executeNonQuery($sql, [$member_id])) {
        setFlashMessage('success', 'Member deleted successfully.');
    } else {
        setFlashMessage('error', 'Error deleting member.');
    }
    header('Location: members.php');
    exit();
}

// Get search parameters
$search = sanitizeInput($_GET['search'] ?? '');
$location_filter = sanitizeInput($_GET['location'] ?? '');
$role_filter = sanitizeInput($_GET['role'] ?? '');
$level_filter = sanitizeInput($_GET['level'] ?? '');

// Build query with search and filters
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR contact_number LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if (!empty($location_filter)) {
    $where_conditions[] = "location = ?";
    $params[] = $location_filter;
}

if (!empty($role_filter)) {
    $where_conditions[] = "member_role = ?";
    $params[] = $role_filter;
}

if (!empty($level_filter)) {
    $where_conditions[] = "program_level = ?";
    $params[] = $level_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get members
$sql = "SELECT * FROM members $where_clause ORDER BY first_name, last_name";
$members = fetchAll($sql, $params);

// Get unique locations for filter
$locations = fetchAll("SELECT DISTINCT location FROM members ORDER BY location");

// Get unique roles for filter
$roles = fetchAll("SELECT DISTINCT member_role FROM members WHERE member_role IS NOT NULL ORDER BY member_role");

// Get unique levels for filter
$levels = fetchAll("SELECT DISTINCT program_level FROM members WHERE program_level IS NOT NULL ORDER BY program_level");

include __DIR__ . '/includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Management | Church Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Members Management</h1>
            <a href="add_member.php" class="btn btn-primary">Add New Member</a>
        </div>

        <?php displayFlashMessage(); ?>

        <!-- Search and Filter Section -->
        <div class="search-box">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search Members</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Search by name, email, or phone">
                </div>
                <div class="col-md-2">
                    <label for="location" class="form-label">Filter by Location</label>
                    <select class="form-select" id="location" name="location">
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?= htmlspecialchars($loc['location']) ?>" 
                                    <?= $location_filter === $loc['location'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($loc['location']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="role" class="form-label">Filter by Role</label>
                    <select class="form-select" id="role" name="role">
                        <option value="">All Roles</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= htmlspecialchars($role['member_role']) ?>" 
                                    <?= $role_filter === $role['member_role'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['member_role']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="level" class="form-label">Filter by Level</label>
                    <select class="form-select" id="level" name="level">
                        <option value="">All Levels</option>
                        <?php foreach ($levels as $level): ?>
                            <option value="<?= htmlspecialchars($level['program_level']) ?>" 
                                    <?= $level_filter === $level['program_level'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($level['program_level']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <a href="members.php" class="btn btn-secondary w-100">Clear</a>
                </div>
            </form>
        </div>

        <!-- Members Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Members (<?= count($members) ?>)</h5>
                <button onclick="CMS.exportToCSV('membersTable', 'members.csv')" class="btn btn-sm btn-success">
                    Export CSV
                </button>
            </div>
            <div class="card-body">
                <?php if (count($members) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="membersTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Location</th>
                                    <th>Program & Level</th>
                                    <th>Contact</th>
                                    <th>Email</th>
                                    <th>Date of Birth</th>
                                    <th>Join Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge <?= getRoleBadgeClass($member['member_role']) ?>">
                                                <?= htmlspecialchars($member['member_role']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($member['location']) ?></td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($member['program_of_study'] ?? '-') ?></strong>
                                                <?php if ($member['program_level']): ?>
                                                    <br>
                                                    <span class="badge <?= getLevelBadgeClass($member['program_level']) ?>">
                                                        <?= htmlspecialchars($member['program_level']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($member['contact_number'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($member['email'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($member['date_of_birth']): ?>
                                                <?= formatDate($member['date_of_birth'], 'M j, Y') ?>
                                                <br><small class="text-muted">(<?= calculateAge($member['date_of_birth']) ?> years)</small>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDate($member['join_date'], 'M j, Y') ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="view_member.php?id=<?= $member['id'] ?>" 
                                                   class="btn btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit_member.php?id=<?= $member['id'] ?>" 
                                                   class="btn btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger delete-btn" 
                                                        data-confirm="Are you sure you want to delete <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>?"
                                                        onclick="deleteMember(<?= $member['id'] ?>, '<?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>')"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
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
                        <p class="text-muted">No members found.</p>
                        <a href="add_member.php" class="btn btn-primary">Add First Member</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="memberName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="member_id" id="memberId">
                        <button type="submit" name="delete_member" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function deleteMember(memberId, memberName) {
            document.getElementById('memberId').value = memberId;
            document.getElementById('memberName').textContent = memberName;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html> 