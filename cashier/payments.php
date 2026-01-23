    <?php
include "../config/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'cashier') {
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

// Ensure settings is an array to prevent warnings
if (!$settings) {
    $settings = [
        'gym_name' => 'Gym Management System',
        'logo_path' => 'gym logo.jpg',
        'background_path' => 'gym background.jpg',
        'sidebar_theme' => 'primary'
    ];
}

// Function to generate unique receipt number
function generateReceiptNo() {
    return 'R' . date('Ymd') . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
}

// Handle payment addition
if (isset($_POST['add_payment'])) {
    $member_id = $_POST['member_id'];
    $amount = $_POST['amount'];
    $receipt_no = $_POST['receipt_no'];
    $payment_method = $_POST['payment_method'];
    $notes = $_POST['notes'];

    // Check if receipt_no already exists
    $check_stmt = $conn->prepare("SELECT id FROM payments WHERE receipt_no = ?");
    $check_stmt->bind_param("s", $receipt_no);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $error = "Receipt No '$receipt_no' already exists. Please use a unique Receipt No.";
        $check_stmt->close();
    } else {
        $check_stmt->close();

        // Check if member is a student
        $member_query = $conn->prepare("SELECT is_student, student_id FROM members WHERE id = ?");
        $member_query->bind_param("i", $member_id);
        $member_query->execute();
        $member_result = $member_query->get_result();
        $member = $member_result->fetch_assoc();

        $is_student_discount = $member['is_student'] ? 1 : 0;
        $student_id = $member['is_student'] ? $member['student_id'] : null;
        $discount_amount = $member['is_student'] ? ($amount * 0.20) : 0.00; // 20% discount for students
        $final_amount = $amount - $discount_amount; // Final amount after discount

        $stmt = $conn->prepare("INSERT INTO payments (member_id, amount, receipt_no, payment_method, notes, is_student_discount, student_id, discount_amount, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idsssisdd", $member_id, $amount, $receipt_no, $payment_method, $notes, $is_student_discount, $student_id, $discount_amount, $user['id']);
        $stmt->execute();
        $stmt->close();
        $member_query->close();

        // Auto check-in the member to attendance
        $attendance_stmt = $conn->prepare("INSERT INTO attendance (member_id, checkin_time) VALUES (?, NOW())");
        $attendance_stmt->bind_param("i", $member_id);
        $attendance_stmt->execute();
        $attendance_stmt->close();

        // Redirect or show success message
        header("Location: payments.php?success=1");
        exit();
    }
}

// Fetch payments
$payments = $conn->query("SELECT p.*, m.fullname, m.is_student, u.fullname as created_by_name FROM payments p JOIN members m ON p.member_id = m.id LEFT JOIN users u ON p.created_by = u.id ORDER BY p.payment_date DESC");

// Fetch members for dropdown (exclude members who already have payments)
$members = $conn->query("SELECT id, fullname, is_student FROM members WHERE id NOT IN (SELECT member_id FROM payments)");

// Handle member_id from URL parameter
$selected_member_id = isset($_GET['member_id']) ? (int)$_GET['member_id'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - Gym Management System</title>
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
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="members.php">
                            <i class="fas fa-users me-2"></i>Members
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="payments.php">
                            <i class="fas fa-credit-card me-2"></i>Payments
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="attendance.php">
                            <i class="fas fa-calendar-check me-2"></i>Attendance
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
                    <span class="navbar-brand mb-0 h1">Payment Management - <?php echo htmlspecialchars($user['fullname']); ?> (Cashier)</span>
                </div>
            </nav>

            <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Payment added successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Payment Management</h1>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                        <i class="fas fa-plus me-1"></i>Add Payment
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Receipt No</th>
                                        <th>Member</th>
                                        <th>Student</th>
                                        <th>Total Amount</th>
                                        <th>Payment Method</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($payment = $payments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['receipt_no']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['fullname']); ?></td>
                                        <td><?php if ($payment['is_student']): ?><span class="badge bg-info">Student</span><?php else: ?>No<?php endif; ?></td>
                                        <td>₱<?php echo number_format($payment['amount'] - $payment['discount_amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_method'] ?? 'Cash'); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($payment['payment_date'])); ?></td>
                                        <td>
                                            <a href="print_receipt.php?id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fas fa-print"></i> Print
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deletePayment(<?php echo $payment['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
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

      <nav class="navbar navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <a href="members.php" class="btn btn-outline-secondary me-3">
                        <i class ="fas fa-arrow-left me-1"></i>Back to Members 
                    </a>

            </nav>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5 border-top">
        <div class="container">
            <small>Developed by Tyron Del Valle</small>
        </div>
    </footer>

    <!-- Add Payment Modal -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Member</label>
                            <select class="form-control" name="member_id" id="member_select" required>
                                <option value="">Select Member</option>
                                <?php
                                $members->data_seek(0); // Reset pointer
                                while ($member = $members->fetch_assoc()): ?>
                                <option value="<?php echo $member['id']; ?>" data-is-student="<?php echo $member['is_student']; ?>">
                                    <?php echo htmlspecialchars($member['fullname']); ?>
                                    <?php if ($member['is_student']): ?>
                                        <span class="badge bg-info">Student (20% discount)</span>
                                    <?php endif; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" class="form-control" name="amount" id="amount_input" required>
                            <div id="discount_info" class="mt-2" style="display: none;">
                                <small class="text-success">Student discount applied: <span id="discount_amount">₱0.00</span> (20% off)</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Receipt No</label>
                            <input type="text" class="form-control" name="receipt_no" id="receipt_no" readonly required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-control" name="payment_method">
                                <option value="Cash">Cash</option>
                                <option value="Card">Card</option>
                                <option value="Online">Online</option>
                                <option value="GCash">GCash</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_payment" class="btn btn-success">Add Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle member selection and student discount
        document.getElementById('member_select').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const isStudent = selectedOption.getAttribute('data-is-student') === '1';
            const discountInfo = document.getElementById('discount_info');
            const discountAmountSpan = document.getElementById('discount_amount');
            const amountInput = document.getElementById('amount_input');

            if (isStudent) {
                discountInfo.style.display = 'block';
                // Calculate discount when amount changes
                amountInput.addEventListener('input', updateDiscountDisplay);
                updateDiscountDisplay();
            } else {
                discountInfo.style.display = 'none';
                amountInput.removeEventListener('input', updateDiscountDisplay);
            }
        });

        function updateDiscountDisplay() {
            const amountInput = document.getElementById('amount_input');
            const discountAmountSpan = document.getElementById('discount_amount');
            const amount = parseFloat(amountInput.value) || 0;
            const discount = amount * 0.20; // 20% discount
            discountAmountSpan.textContent = '₱' + discount.toFixed(2);
        }

        // Handle student checkbox in members.php
        const studentCheckbox = document.getElementById('is_student');
        if (studentCheckbox) {
            studentCheckbox.addEventListener('change', function() {
                const studentIdField = document.querySelector('.student-id-field');
                if (this.checked) {
                    studentIdField.style.display = 'block';
                } else {
                    studentIdField.style.display = 'none';
                }
            });
        }

        // Handle payment deletion
        function deletePayment(paymentId) {
            if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
                fetch('delete_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'payment_id=' + paymentId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Payment deleted successfully!');
                        location.reload();
                    } else {
                        alert('Failed to delete payment: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the payment.');
                });
            }
        }

        // Function to generate receipt number
        function generateReceiptNumber() {
            const today = new Date();
            const dateStr = today.getFullYear().toString() +
                           (today.getMonth() + 1).toString().padStart(2, '0') +
                           today.getDate().toString().padStart(2, '0');
            const randomStr = Math.random().toString(36).substring(2, 8).toUpperCase();
            return 'R' + dateStr + randomStr;
        }

        // Function to populate receipt number
        function populateReceiptNumber() {
            const receiptInput = document.getElementById('receipt_no');
            receiptInput.value = generateReceiptNumber();
        }

        // Auto-populate receipt number when modal is shown
        document.getElementById('addPaymentModal').addEventListener('show.bs.modal', function() {
            populateReceiptNumber();
        });

        // Auto-open modal and pre-select member if member_id is in URL
        <?php if ($selected_member_id): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const memberSelect = document.getElementById('member_select');/*  */
            memberSelect.value = '<?php echo $selected_member_id; ?>';

            // Trigger change event to show discount if applicable
            const event = new Event('change');
            memberSelect.dispatchEvent(event);

            // Open the modal
            const modal = new bootstrap.Modal(document.getElementById('addPaymentModal'));
            modal.show();
        });
        <?php endif; ?>

        // Sidebar toggle functionality
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
