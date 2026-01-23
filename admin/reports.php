<?php
include "../config/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];

// Fetch gym settings
$settings = $conn->query("SELECT * FROM gym_settings WHERE id = 1")->fetch_assoc();
if (!$settings) {
    // Insert default settings if not exists
    $conn->query("INSERT INTO gym_settings (gym_name, logo_path, background_path) VALUES ('Gym Management System', 'gym logo.jpg', 'gym background.jpg')");
    $settings = $conn->query("SELECT * FROM gym_settings WHERE id = 1")->fetch_assoc();
}

// Fetch report data
$total_members = $conn->query("SELECT COUNT(*) as count FROM members")->fetch_assoc()['count'];
$active_members = $conn->query("SELECT COUNT(*) as count FROM members WHERE status = 'ACTIVE'")->fetch_assoc()['count'];
$total_payments = $conn->query("SELECT SUM(amount) as total FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0;
$total_attendance = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE MONTH(checkin_time) = MONTH(CURDATE()) AND YEAR(checkin_time) = YEAR(CURDATE())")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Gym Management System</title>
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
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?> active" href="members.php">
                            <i class="fas fa-users me-2"></i>Members
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="attendance.php">
                            <i class="fas fa-calendar-check me-2"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="employees.php">
                            <i class="fas fa-user-tie me-2"></i>Employees
                        </a>
                    </li>
                      <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="../logout.php">
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
                    <span class="navbar-brand mb-0 h1">Reports - <?php echo htmlspecialchars($user['fullname']); ?> (Admin)</span>
                </div>
            </nav>

            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-12">
                        <h1 class="h3 mb-4">Monthly Reports</h1>
                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <div class="rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #007bff, #0056b3); display: flex; align-items: center; justify-content: center; color: white;">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                        <h5 class="card-title">Total Members</h5>
                                        <p class="card-text display-4"><?php echo $total_members; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <div class="rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #28a745, #1e7e34); display: flex; align-items: center; justify-content: center; color: white;">
                                            <i class="fas fa-user-check fa-2x"></i>
                                        </div>
                                        <h5 class="card-title">Active Members</h5>
                                        <p class="card-text display-4"><?php echo $active_members; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <div class="rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #ffc107, #e0a800); display: flex; align-items: center; justify-content: center; color: white;">
                                            <i class="fas fa-peso-sign fa-2x"></i>
                                        </div>
                                        <h5 class="card-title">Monthly Revenue</h5>
                                        <p class="card-text display-4">₱<?php echo number_format($total_payments, 2); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <div class="rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #dc3545, #bd2130); display: flex; align-items: center; justify-content: center; color: white;">
                                            <i class="fas fa-calendar-check fa-2x"></i>
                                        </div>
                                        <h5 class="card-title">Monthly Check-ins</h5>
                                        <p class="card-text display-4"><?php echo $total_attendance; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5 border-top">
        <div class="container">
            <small>Developed by Tyron Del Valle</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.flex-grow-1');

            function isMobile() {
                return window.innerWidth <= 768;
            }

            sidebarToggle.addEventListener('click', function() {
                if (isMobile()) {
                    sidebar.classList.toggle('sidebar-open');
                } else {
                    sidebar.classList.toggle('sidebar-collapsed');
                    mainContent.classList.toggle('main-expanded');
                }
                // Update toggle icon
                const icon = sidebarToggle.querySelector('i');
                const isOpen = isMobile() ? sidebar.classList.contains('sidebar-open') : !sidebar.classList.contains('sidebar-collapsed');
                if (isOpen) {
                    icon.className = 'fas fa-times'; // Close icon when open
                } else {
                    icon.className = 'fas fa-bars'; // Bars icon when closed
                }
            });
        });
    </script>
</body>
</html>
