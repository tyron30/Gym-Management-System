<?php
include "config/db.php";

echo "=== Fixing QR Token Length for Existing Members ===\n\n";

// Get all members with 32-character QR tokens
$stmt = $conn->prepare("SELECT id, fullname, qr_token FROM members WHERE LENGTH(qr_token) = 32");
$stmt->execute();
$result = $stmt->get_result();

$updated_count = 0;
while ($member = $result->fetch_assoc()) {
    // Generate new 64-character token
    $new_token = bin2hex(random_bytes(32));

    // Check if new token already exists (unlikely but safe)
    $check_stmt = $conn->prepare("SELECT id FROM members WHERE qr_token = ?");
    $check_stmt->bind_param("s", $new_token);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows == 0) {
        // Update the member with new token
        $update_stmt = $conn->prepare("UPDATE members SET qr_token = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_token, $member['id']);
        if ($update_stmt->execute()) {
            echo "Updated member ID {$member['id']} ({$member['fullname']}) with new 64-char token\n";
            $updated_count++;
        } else {
            echo "Failed to update member ID {$member['id']}\n";
        }
        $update_stmt->close();
    } else {
        echo "Generated token already exists, skipping member ID {$member['id']}\n";
    }
    $check_stmt->close();
}

$stmt->close();

echo "\n=== Summary ===\n";
echo "Updated $updated_count members with new 64-character QR tokens\n";

// Verify no more 32-char tokens exist
$verify_stmt = $conn->prepare("SELECT COUNT(*) as count FROM members WHERE LENGTH(qr_token) = 32");
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();
$verify_count = $verify_result->fetch_assoc()['count'];

if ($verify_count == 0) {
    echo "✓ All QR tokens are now 64 characters long\n";
} else {
    echo "✗ Still $verify_count members with 32-character tokens\n";
}

$verify_stmt->close();
$conn->close();
?>
