<?php
include "../config/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'cashier') {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];

$payment_id = $_GET['id'] ?? null;
if (!$payment_id) {
    header("Location: payments.php");
    exit();
}

// Fetch gym settings
$settings = $conn->query("SELECT * FROM gym_settings WHERE id = 1")->fetch_assoc();
if (!$settings) {
    // Insert default settings if not exists
    $conn->query("INSERT INTO gym_settings (gym_name, logo_path, background_path) VALUES ('Gym Management System', 'gym logo.jpg', 'gym background.jpg')");
    $settings = $conn->query("SELECT * FROM gym_settings WHERE id = 1")->fetch_assoc();
}

// Fetch payment details
$payment = $conn->query("SELECT p.*, m.fullname, m.member_code FROM payments p JOIN members m ON p.member_id = m.id WHERE p.id = $payment_id")->fetch_assoc();

if (!$payment) {
    header("Location: payments.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - Gym Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            .receipt { border: none; box-shadow: none; }
        }
        .receipt {
            max-width: 400px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="receipt card">
                    <div class="card-body text-center">
                        <img src="../<?php echo htmlspecialchars($settings['logo_path']); ?>" alt="Gym Logo" class="mb-3" style="width: 80px; height: 80px;">
                        <h4 class="card-title"><?php echo htmlspecialchars($settings['gym_name']); ?></h4>
                        <p class="text-muted mb-4">Payment Receipt</p>

                        <div class="row mb-3">
                            <div class="col-6 text-start">
                                <strong>Receipt No:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <?php echo htmlspecialchars($payment['receipt_no']); ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6 text-start">
                                <strong>Member Code:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <?php echo htmlspecialchars($payment['member_code']); ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6 text-start">
                                <strong>Member Name:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <?php echo htmlspecialchars($payment['fullname']); ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6 text-start">
                                <strong>Payment Date:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <?php echo date('M j, Y', strtotime($payment['payment_date'])); ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6 text-start">
                                <strong>Payment Method:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <?php echo htmlspecialchars($payment['payment_method'] ?? 'Cash'); ?>
                            </div>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-6 text-start">
                                <strong>Total Amount:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <h4 class="text-primary">₱<?php echo number_format($payment['amount'] - $payment['discount_amount'], 2); ?></h4>
                            </div>
                        </div>

                        <?php if ($payment['notes']): ?>
                        <div class="mb-3">
                            <strong>Notes:</strong><br>
                            <?php echo htmlspecialchars($payment['notes']); ?>
                        </div>
                        <?php endif; ?>

                        <hr>

                        <p class="text-muted small">Thank you for your payment!</p>
                        <p class="text-muted small">Generated on <?php echo date('M j, Y H:i'); ?> by <?php echo htmlspecialchars($user['fullname']); ?></p>
                    </div>
                </div>

                <div class="text-center mt-4 no-print">
                    <button onclick="window.print()" class="btn btn-primary me-2">
                        <i class="fas fa-print me-1"></i>Print Receipt
                    </button>
                    <?php if (isset($_GET['from']) && $_GET['from'] === 'members'): ?>
                    <a href="members.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Members
                    </a>
                    <?php else: ?>
                    <a href="payments.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Payments
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
