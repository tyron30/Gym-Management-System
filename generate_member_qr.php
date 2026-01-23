<?php
include "config/db.php";
include "phpqrcode/qrlib.php";

// Ensure qr_codes directory exists
$qrDir = 'qr_codes';
if (!is_dir($qrDir)) {
    mkdir($qrDir, 0755, true);
}

// Generate secure random token (NOT readable) and ensure uniqueness
do {
    $qr_token = bin2hex(random_bytes(32)); // 64-character token
    $stmt = $conn->prepare("SELECT id FROM members WHERE qr_token = ?");
    $stmt->bind_param("s", $qr_token);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
} while ($exists || $qr_token === '0' || empty($qr_token));

$filename = $qr_token . ".png";
$filepath = __DIR__ . '/' . $qrDir . "/" . $filename;

// Generate standard Model 1 QR code (plain black-and-white, no logo)
QRcode::png($qr_token, $filepath, QR_ECLEVEL_L, 10, 4);

// Return token + path to JS
header('Content-Type: application/json');
echo json_encode([
    "qr_token" => $qr_token,
    "path" => "qr_codes/" . $filename
]);
exit;
?>
