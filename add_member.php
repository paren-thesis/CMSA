<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$errors = [];
$member = [
    'first_name' => '',
    'last_name' => '',
    'location' => '',
    'program_of_study' => '',
    'program_level' => '',
    'member_role' => 'Member',
    'contact_number' => '',
    'email' => '',
    'date_of_birth' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $member = [
        'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
        'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
        'location' => sanitizeInput($_POST['location'] ?? ''),
        'program_of_study' => sanitizeInput($_POST['program_of_study'] ?? ''),
        'program_level' => sanitizeInput($_POST['program_level'] ?? ''),
        'member_role' => sanitizeInput($_POST['member_role'] ?? 'Member'),
        'contact_number' => sanitizeInput($_POST['contact_number'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'date_of_birth' => $_POST['date_of_birth'] ?? ''
    ];

    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'location'];
    $errors = validateRequired($member, $required_fields);

    // Validate email if provided
    if (!empty($member['email']) && !isValidEmail($member['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Validate date of birth
    if (!empty($member['date_of_birth'])) {
        $dob = new DateTime($member['date_of_birth']);
        $today = new DateTime();
        if ($dob > $today) {
            $errors[] = 'Date of birth cannot be in the future.';
        }
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $sql = "INSERT INTO members (first_name, last_name, location, program_of_study, program_level, member_role, contact_number, email, date_of_birth) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $member['first_name'],
            $member['last_name'],
            $member['location'],
            $member['program_of_study'],
            $member['program_level'],
            $member['member_role'],
            $member['contact_number'],
            $member['email'],
            $member['date_of_birth'] ?: null
        ];

        if (executeNonQuery($sql, $params)) {
            setFlashMessage('success', 'Member added successfully.');
            header('Location: members.php');
            exit();
        } else {
            $errors[] = 'Error adding member. Please try again.';
        }
    }
}

include __DIR__ . '/includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Member | Church Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Add New Member</h1>
            <a href="members.php" class="btn btn-secondary">Back to Members</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Member Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= htmlspecialchars($member['first_name']) ?>" required>
                            <div class="invalid-feedback">Please enter the first name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= htmlspecialchars($member['last_name']) ?>" required>
                            <div class="invalid-feedback">Please enter the last name.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?= htmlspecialchars($member['location']) ?>" required>
                            <div class="invalid-feedback">Please enter the location.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="member_role" class="form-label">Member Role *</label>
                            <select class="form-select" id="member_role" name="member_role" required>
                                <?php foreach (getMemberRoles() as $key => $value): ?>
                                    <option value="<?= htmlspecialchars($key) ?>" 
                                            <?= $member['member_role'] === $key ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($value) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="program_of_study" class="form-label">Program of Study</label>
                            <input type="text" class="form-control" id="program_of_study" name="program_of_study" 
                                   value="<?= htmlspecialchars($member['program_of_study']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="program_level" class="form-label">Program Level</label>
                            <select class="form-select" id="program_level" name="program_level">
                                <option value="">Select Level</option>
                                <?php foreach (getProgramLevels() as $key => $value): ?>
                                    <option value="<?= htmlspecialchars($key) ?>" 
                                            <?= $member['program_level'] === $key ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($value) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="contact_number" name="contact_number" 
                                   value="<?= htmlspecialchars($member['contact_number']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($member['email']) ?>">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                   value="<?= htmlspecialchars($member['date_of_birth']) ?>">
                            <div id="age_display" class="form-text" style="display: none;"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="members.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Add Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 