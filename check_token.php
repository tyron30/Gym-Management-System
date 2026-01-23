<?php
include "config/db.php";

$token = '9c9ef22ebe51dda53e51b37fe15f9eb4';

echo "Checking token: $token\n";

$stmt = $conn->prepare("SELECT id, fullname, qr_token FROM members WHERE qr_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $member = $result->fetch_assoc();
    echo "Member found: ID=" . $member['id'] . ", Name=" . $member['fullname'] . ", Token=" . $member['qr_token'] . "\n";
} else {
    echo "Token not found in database\n";

    // Check if there are any members with QR tokens
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM members WHERE qr_token IS NOT NULL AND qr_token != ''");
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count = $count_result->fetch_assoc();
    echo "Total members with QR tokens: " . $count['count'] . "\n";

    // Show some recent members
    $recent_stmt = $conn->prepare("SELECT id, fullname, qr_token FROM members WHERE qr_token IS NOT NULL AND qr_token != '' ORDER BY created_at DESC LIMIT 5");
    $recent_stmt->execute();
    $recent_result = $recent_stmt->get_result();
    echo "Recent members with QR tokens:\n";
    while ($member = $recent_result->fetch_assoc()) {
        echo "  ID=" . $member['id'] . ", Name=" . $member['fullname'] . ", Token=" . substr($member['qr_token'], 0, 20) . "...\n";
    }
}
?>
