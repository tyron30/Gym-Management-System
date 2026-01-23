l <?php
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

// Handle check-in
if (isset($_POST['checkin'])) {
    $member_id = $_POST['member_id'];
    $notes = $_POST['notes'];

    // Check if member has already checked in today
    $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE member_id = ? AND DATE(checkin_time) = CURDATE()");
    $check_stmt->bind_param("i", $member_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
        $check_stmt->close();
        header("Location: attendance.php?duplicate_checkin=1");
        exit();
    }
    $check_stmt->close();

    $stmt = $conn->prepare("INSERT INTO attendance (member_id, notes) VALUES (?, ?)");
    $stmt->bind_param("is", $member_id, $notes);
    $stmt->execute();
    $stmt->close();
}

// Handle check-out
if (isset($_POST['checkout'])) {
    $attendance_id = $_POST['attendance_id'];

    $conn->query("UPDATE attendance SET checkout_time = NOW() WHERE id = $attendance_id");
}

// Handle date filtering
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

// Build query based on filters
$query = "SELECT a.*, m.fullname FROM attendance a JOIN members m ON a.member_id = m.id";
$conditions = [];

if (!empty($from_date) && !empty($to_date)) {
    $conditions[] = "DATE(a.checkin_time) BETWEEN '$from_date' AND '$to_date'";
} elseif (!empty($from_date)) {
    $conditions[] = "DATE(a.checkin_time) >= '$from_date'";
} elseif (!empty($to_date)) {
    $conditions[] = "DATE(a.checkin_time) <= '$to_date'";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY a.checkin_time DESC";
$attendance = $conn->query($query);

// Fetch members for check-in (only those who haven't checked in today)
$members = $conn->query("SELECT id, fullname FROM members WHERE status = 'ACTIVE' AND id NOT IN (SELECT member_id FROM attendance WHERE DATE(checkin_time) = CURDATE())");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Attendance Management - Gym Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
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
                    <span class="navbar-brand mb-0 h1">Attendance Management - <?php echo htmlspecialchars($user['fullname']); ?> (Admin)</span>
                </div>
            </nav>

            <!-- Notification Messages -->
            <?php if (isset($_GET['duplicate_checkin'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                This member has already checked in today. Cannot check in again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Attendance Management - <?php echo date('F j, Y'); ?></h1>
                    <div>
                        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#checkinModal">
                            <i class="fas fa-sign-in-alt me-1"></i>Check In Member
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
                            <i class="fas fa-qrcode me-1"></i>Scan QR Code
                        </button>
                    </div>
                </div>

                <!-- Date Filter Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="from_date" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="from_date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="to_date" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="to_date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                                <a href="attendance.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </form>
                        <?php if (!empty($from_date) || !empty($to_date)): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                Showing attendance records
                                <?php if (!empty($from_date) && !empty($to_date)): ?>
                                    from <?php echo date('M j, Y', strtotime($from_date)); ?> to <?php echo date('M j, Y', strtotime($to_date)); ?>
                                <?php elseif (!empty($from_date)): ?>
                                    from <?php echo date('M j, Y', strtotime($from_date)); ?> onwards
                                <?php elseif (!empty($to_date)): ?>
                                    up to <?php echo date('M j, Y', strtotime($to_date)); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Check-in Time</th>
                                        <th>Check-out Time</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($record = $attendance->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['fullname']); ?></td>
                                        <td><?php echo date('h:i:s A', strtotime($record['checkin_time'])); ?></td>
                                        <td>
                                            <?php if ($record['checkout_time']): ?>
                                                <?php echo date('h:i:s A', strtotime($record['checkout_time'])); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($record['checkout_time']): ?>
                                                <?php
                                                $checkin = strtotime($record['checkin_time']);
                                                $checkout = strtotime($record['checkout_time']);
                                                $duration = $checkout - $checkin;
                                                echo gmdate('H:i:s', $duration);
                                                ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($record['checkout_time']): ?>
                                                <span class="badge bg-success">Completed</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!$record['checkout_time']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="attendance_id" value="<?php echo $record['id']; ?>">
                                                <button type="submit" name="checkout" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-sign-out-alt"></i> Check Out
                                                </button>
                                            </form>
                                            <?php endif; ?>
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
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5 border-top">
        <div class="container">
            <small>Developed by Tyron Del Valle</small>
        </div>
    </footer>

    <!-- Check-in Modal -->
    <div class="modal fade" id="checkinModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Member Check-in</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Member</label>
                            <select class="form-control" name="member_id" required>
                                <option value="">Choose Member</option>
                                <?php while ($member = $members->fetch_assoc()): ?>
                                <option value="<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['fullname']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Any additional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="checkin" class="btn btn-success">Check In</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- QR Scanner Modal -->
    <div class="modal fade" id="qrScannerModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">QR Code Scanner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <div id="qr-reader" style="width: 100%; height: 400px;"></div>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted">Position the QR code within the camera view to scan and check in the member.</p>
                    </div>
                    <div id="qrResult" class="alert" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

        // QR Scanner functionality
        let html5QrcodeScanner = null;

        document.getElementById('qrScannerModal').addEventListener('shown.bs.modal', function () {
            const resultDiv = document.getElementById('qrResult');

            console.log('html5-qrcode library loaded');
            console.log('Scanner modal opened');

            if (typeof Html5QrcodeScanner === 'undefined') {
                resultDiv.className = 'alert alert-danger';
                resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>QR scanner library not loaded. Please refresh the page.';
                resultDiv.style.display = 'block';
                return;
            }

            // Clear any existing scanner
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(error => {
                    console.error('Failed to clear existing scanner:', error);
                });
            }

            // Create new scanner
            html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader", {
                    fps: 10,
                    qrbox: {width: 250, height: 250},
                    aspectRatio: 1.0,
                    showTorchButtonIfSupported: false,
                    showZoomSliderIfSupported: false,
                    defaultZoomValueIfSupported: 2,
                });

            html5QrcodeScanner.render(function (decodedText, decodedResult) {
                console.log('QR code scanned:', decodedText);

                // Stop scanning
                html5QrcodeScanner.clear().catch(error => {
                    console.error('Failed to clear scanner:', error);
                });

                // Process the QR code content (token)
                fetch('../qr_attendance.php?token=' + encodeURIComponent(decodedText), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            resultDiv.className = 'alert alert-success';
                            resultDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + data.success;
                            resultDiv.style.display = 'block';

                            // Reload the page after 2 seconds to show updated attendance
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            resultDiv.className = 'alert alert-danger';
                            resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + data.error;
                            resultDiv.style.display = 'block';

                            // Restart scanner after showing error
                            setTimeout(function() {
                                startScanner();
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        resultDiv.className = 'alert alert-danger';
                        resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Failed to process QR code. Please try again.';
                        resultDiv.style.display = 'block';

                        // Restart scanner after showing error
                        setTimeout(function() {
                            startScanner();
                        }, 3000);
                    });
            }, function (errorMessage) {
                // Ignore scan errors, only handle successful scans
                console.log('Scan error:', errorMessage);
            });
        });

        function startScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.render(function (decodedText, decodedResult) {
                    console.log('QR code scanned:', decodedText);

                    // Stop scanning
                    html5QrcodeScanner.clear().catch(error => {
                        console.error('Failed to clear scanner:', error);
                    });

                    // Process the QR code content (token)
                    fetch('../qr_attendance.php?token=' + encodeURIComponent(decodedText), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            const resultDiv = document.getElementById('qrResult');
                            console.log('Response data:', data);
                            if (data.success) {
                                resultDiv.className = 'alert alert-success';
                                resultDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + data.success;
                                resultDiv.style.display = 'block';
                                setTimeout(() => location.reload(), 2000);
                            } else {
                                resultDiv.className = 'alert alert-danger';
                                resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + data.error;
                                resultDiv.style.display = 'block';
                                setTimeout(() => startScanner(), 3000);
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error:', error);
                            const resultDiv = document.getElementById('qrResult');
                            resultDiv.className = 'alert alert-danger';
                            resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Failed to process QR code. Please try again.';
                            resultDiv.style.display = 'block';
                            setTimeout(() => startScanner(), 3000);
                        });
                }, function (errorMessage) {
                    console.log('Scan error:', errorMessage);
                });
            }
        }

        document.getElementById('qrScannerModal').addEventListener('hidden.bs.modal', function () {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(error => {
                    console.error('Failed to clear scanner on modal close:', error);
                });
            }
        });
    </script>
</body>
</html>
