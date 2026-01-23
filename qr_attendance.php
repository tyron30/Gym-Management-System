<?php
include "config/db.php";

// Check if this is an AJAX request for JSON response
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!isset($_GET['token'])) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No token provided']);
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
}

$token = $_GET['token'];

// Validate token and get member info
$stmt = $conn->prepare("SELECT id, fullname, status FROM members WHERE qr_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = ['error' => "Invalid QR code. Member not found."];
} else {
    $member = $result->fetch_assoc();

    if ($member['status'] !== 'ACTIVE') {
        $response = ['error' => "Member account is Expired."];
    } else {
        // Check if member has already checked in today
        $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE member_id = ? AND DATE(checkin_time) = CURDATE()");
        $check_stmt->bind_param("i", $member['id']);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $response = ['error' => "Already checked in today."];
        } else {
            // Record attendance
            $attendance_stmt = $conn->prepare("INSERT INTO attendance (member_id, checkin_time) VALUES (?, NOW())");
            $attendance_stmt->bind_param("i", $member['id']);
            if ($attendance_stmt->execute()) {
                $response = ['success' => "Check-in successful! Welcome, " . htmlspecialchars($member['fullname']) . "."];
            } else {
                $response = ['error' => "Failed to record attendance. Please try again."];
            }
            $attendance_stmt->close();
        }
        $check_stmt->close();
    }
}
$stmt->close();

// Return JSON for AJAX requests
if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// For non-AJAX requests, show HTML page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Attendance - Gym Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <img src="gym logo.jpg" alt="Gym Logo" class="rounded-circle mb-3" style="width: 80px; height: 80px;">
                            <h2 class="card-title">QR Attendance</h2>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php elseif (isset($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
