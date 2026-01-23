<?php
include "config/db.php";

echo "Fixing corrupted QR tokens...\n";

$result = $conn->query("SELECT id, qr_token FROM members WHERE LENGTH(qr_token) < 64 AND qr_token IS NOT NULL AND qr_token != ''");

$fixed = 0;
while ($member = $result->fetch_assoc()) {
    // Generate new unique token
    do {
        $new_token = bin2hex(random_bytes(32)); // 64 character hex string
        $check_stmt = $conn->prepare("SELECT id FROM members WHERE qr_token = ?");
        $check_stmt->bind_param("s", $new_token);
        $check_stmt->execute();
        $check_stmt->store_result();
        $exists = $check_stmt->num_rows > 0;
        $check_stmt->close();
    } while ($exists);

    // Update the member
    $update_stmt = $conn->prepare("UPDATE members SET qr_token = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_token, $member['id']);
    if ($update_stmt->execute()) {
        echo "Fixed member ID {$member['id']}: '{$member['qr_token']}' -> '$new_token'\n";
        $fixed++;
    }
    $update_stmt->close();
}

echo "Fixed $fixed corrupted tokens.\n";
$conn->close();
?>
