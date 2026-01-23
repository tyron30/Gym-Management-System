<?php
include "../config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
     exit();
}

$user = $_SESSION['user'];
if ($user['role'] !== 'cashier') {
    header("Location: ../index.php");
    exit();
}

// Fetch gym settings
$settings = $conn->query("SELECT * FROM gym_settings WHERE id = 1")->fetch_assoc();
if (!$settings) {
    // Insert default settings if not exists
    $conn->query("INSERT INTO gym_settings (gym_name, logo_path, background_path) VALUES ('Gym Management System', 'gym logo.jpg', 'gym background.jpg')");
    $settings = $conn->query("SELECT * FROM gym_settings WHERE id = 1")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard - Gym Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav id="sidebar" class="bg-<?php echo htmlspecialchars($settings['sidebar_theme']); ?> <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?> vh-100" style="width: 250px;">
            <div class="p-3">
                <div class="text-center mb-4">
                    <img src="../<?php echo htmlspecialchars($settings['logo_path']); ?>" alt="Gym Logo" class="rounded-circle mb-2" style="width: 80px; height: 80px;">
                    <h5 class="fw-bold"><?php echo htmlspecialchars($settings['gym_name']); ?></h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="members.php">
                            <i class="fas fa-users me-2"></i>Members
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="attendance.php">
                            <i class="fas fa-calendar-check me-2"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-white" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Top Bar -->
            <nav class="navbar navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-outline-secondary me-3" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="navbar-brand mb-0 h1">Welcome, <?php echo htmlspecialchars($user['fullname']); ?> (Cashier)</span>
                </div>
            </nav>

            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-12">
                        <h1 class="h3 mb-4">Cashier Dashboard</h1>
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-users me-2"></i>Total Members</h5>
                                        <p class="card-text display-4"><?php echo $conn->query("SELECT COUNT(*) as count FROM members")->fetch_assoc()['count']; ?></p>
                                        <a href="members.php" class="btn btn-primary">Manage Members</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-peso-sign me-2"></i>Today's Payments</h5>
                                        <p class="card-text display-4">₱<?php echo number_format($conn->query("SELECT SUM(amount) as total FROM payments WHERE DATE(payment_date) = CURDATE()")->fetch_assoc()['total'] ?? 0, 2); ?></p>
                                        <a href="payments.php" class="btn btn-primary">View Payments</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-calendar-check me-2"></i>Today's Check-ins</h5>
                                        <p class="card-text display-4"><?php echo $conn->query("SELECT COUNT(*) as count FROM attendance WHERE DATE(checkin_time) = CURDATE()")->fetch_assoc()['count']; ?></p>
                                        <a href="attendance.php" class="btn btn-primary">View Attendance</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-user-check me-2"></i>Active Members</h5>
                                        <p class="card-text display-4"><?php echo $conn->query("SELECT COUNT(*) as count FROM members WHERE status = 'ACTIVE'")->fetch_assoc()['count']; ?></p>
                                        <a href="members.php" class="btn btn-primary">View Members</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.flex-grow-1');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-collapsed');
                mainContent.classList.toggle('main-expanded');

                // Update toggle icon
                const icon = sidebarToggle.querySelector('i');
                if (sidebar.classList.contains('sidebar-collapsed')) {
                    icon.className = 'fas fa-times'; // Close icon when collapsed
                } else {
                    icon.className = 'fas fa-bars'; // Bars icon when expanded
                }
            });
        });
    </script>
</body>
</html>
