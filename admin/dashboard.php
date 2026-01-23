<?php
include "../config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
if ($user['role'] !== 'admin') {
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

// Fetch data for modals
$members = $conn->query("SELECT m.*, CASE WHEN EXISTS (SELECT 1 FROM payments p WHERE p.member_id = m.id AND p.is_student_discount = 1) THEN 1 ELSE 0 END as has_discount FROM members m ORDER BY m.id DESC");
$payments = $conn->query("SELECT p.*, m.fullname FROM payments p JOIN members m ON p.member_id = m.id ORDER BY p.payment_date DESC");
$active_members = $conn->query("SELECT * FROM members WHERE status = 'ACTIVE' ORDER BY id DESC");

// Fetch attendance counts for different periods
$today = date('Y-m-d');
$current_month = date('Y-m');
$current_year = date('Y');

// Per Session (Daily)
$daily_attendance = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE DATE(checkin_time) = '$today'")->fetch_assoc()['count'];

// Monthly
$monthly_attendance = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE DATE_FORMAT(checkin_time, '%Y-%m') = '$current_month'")->fetch_assoc()['count'];

// Half Month (Last 15 days)
$half_month_start = date('Y-m-d', strtotime('-15 days'));
$half_month_attendance = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE DATE(checkin_time) >= '$half_month_start'")->fetch_assoc()['count'];

// Annual
$annual_attendance = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE YEAR(checkin_time) = '$current_year'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gym Management System</title>
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
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?> active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="members.php">
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
                    <span class="navbar-brand mb-0 h1">Admin Dashboard - <?php echo htmlspecialchars($user['fullname']); ?> (Admin)</span>
                </div>
            </nav>

            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-12">
                        <h1 class="h3 mb-4">Admin Dashboard</h1>
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="card dashboard-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-users me-2"></i>Total Members</h5>
                                        <p class="card-text display-4"><?php echo $conn->query("SELECT COUNT(*) as count FROM members")->fetch_assoc()['count']; ?></p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#membersModal">View Details</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card dashboard-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-dollar-sign me-2"></i>Monthly Revenue</h5>
                                        <p class="card-text display-4">₱<?php echo number_format($conn->query("SELECT SUM(amount) as total FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0, 2); ?></p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentsModal">View Details</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card dashboard-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-calendar-check me-2"></i>Active Plans</h5>
                                        <p class="card-text display-4"><?php echo $conn->query("SELECT COUNT(*) as count FROM members WHERE status = 'ACTIVE'")->fetch_assoc()['count']; ?></p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#activeMembersModal">View Details</button>
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

    <!-- Members Modal -->
    <div class="modal fade" id="membersModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">All Members Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Plan</th>
                                    <th>Paid</th>
                                    <th>Student</th>
                                    <th>Expiry</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($member = $members->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($member['plan']); ?></td>
                                    <td>
                                        <?php
                                        if ($member['plan'] == 'Per Session') {
                                            echo '1 session';
                                        } elseif ($member['plan'] == 'Half Month') {
                                            echo 'Half month';
                                        } elseif ($member['plan'] == 'Monthly') {
                                            echo '1 month';
                                        } else {
                                            echo htmlspecialchars($member['plan']);
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $member['is_student'] ? '<span class="badge bg-info">Student</span>' : '<span class="badge bg-secondary">Regular</span>'; ?></td>
                                    <td>
                                        <?php
                                        $start_date = strtotime($member['start_date']);
                                        $end_date = strtotime($member['end_date']);
                                        $today = strtotime(date('Y-m-d'));

                                        if ($member['plan'] == 'Per Session') {
                                            echo '1 day';
                                        } elseif ($today < $start_date) {
                                            $days_to_start = ceil(($start_date - $today) / (60 * 60 * 24));
                                            echo '<span class="badge bg-info">Starts in ' . $days_to_start . ' days</span>';
                                        } elseif ($today > $end_date) {
                                            echo '<span class="badge bg-danger">Expired</span>';
                                        } else {
                                            $days_diff = ($end_date - $today) / (60 * 60 * 24);
                                            if ($days_diff <= 7) {
                                                echo '<span class="badge bg-warning">' . ceil($days_diff) . ' days left</span>';
                                            } else {
                                                echo '<span class="badge bg-success">' . ceil($days_diff) . ' days left</span>';
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Modal -->
    <div class="modal fade" id="paymentsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">All Payments Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Member</th>
                                    <th>Amount</th>
                                    <th>Receipt No</th>
                                    <th>Payment Method</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($payment = $payments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['id']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['fullname']); ?></td>
                                    <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($payment['receipt_no']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['payment_method'] ?? 'Cash'); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($payment['payment_date'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Members Modal -->
    <div class="modal fade" id="activeMembersModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Active Members Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Join Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($active_member = $active_members->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($active_member['id']); ?></td>
                                    <td><?php echo htmlspecialchars($active_member['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($active_member['email']); ?></td>
                                    <td><?php echo htmlspecialchars($active_member['phone']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($active_member['start_date'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
