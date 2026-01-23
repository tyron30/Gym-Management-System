<?php
include "../config/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id'])) {
    $payment_id = intval($_POST['payment_id']);

    $stmt = $conn->prepare("DELETE FROM payments WHERE id = ?");
    $stmt->bind_param("i", $payment_id);

    if ($stmt->execute()) {
        header("Content-Type: application/json");
        echo json_encode(['success' => true]);
    } else {
        header("Content-Type: application/json");
        echo json_encode(['success' => false, 'message' => 'Failed to delete payment']);
    }

    $stmt->close();
} else {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
