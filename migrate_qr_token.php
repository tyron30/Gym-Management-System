<?php
include "config/db.php";

// Function to generate unique QR token
function generateQRToken() {
    global $conn;
    do {
        $token = bin2hex(random_bytes(32)); // 64 character hex string
        $stmt = $conn->prepare("SELECT id FROM members WHERE qr_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
    } while ($exists);

    return $token;
}

// Get all members without QR tokens
$stmt = $conn->prepare("SELECT id FROM members WHERE qr_token IS NULL OR qr_token = ''");
$stmt->execute();
$result = $stmt->get_result();

$updated = 0;
while ($member = $result->fetch_assoc()) {
    $qr_token = generateQRToken();

    $update_stmt = $conn->prepare("UPDATE members SET qr_token = ? WHERE id = ?");
    $update_stmt->bind_param("si", $qr_token, $member['id']);
    if ($update_stmt->execute()) {
        $updated++;
    }
    $update_stmt->close();
}

$stmt->close();

echo "Migration completed. Updated $updated members with QR tokens.\n";
?>
