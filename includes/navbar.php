<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Church CMS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="members.php">Members</a></li>
                <li class="nav-item"><a class="nav-link" href="attendance.php">Attendance</a></li>
                <li class="nav-item"><a class="nav-link" href="birthdays.php">Birthdays</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php" id="logout-btn">Logout</a></li>
            </ul>
        </div>
    </div>
</nav> 