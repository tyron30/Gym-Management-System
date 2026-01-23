<?php
include "config/db.php";

echo "=== Checking for Duplicate QR Tokens ===\n\n";

$result = $conn->query("SELECT qr_token, COUNT(*) as count FROM members WHERE qr_token IS NOT NULL AND qr_token != '' GROUP BY qr_token HAVING count > 1");

if ($result->num_rows > 0) {
    echo "Found duplicate QR tokens:\n";
    while ($row = $result->fetch_assoc()) {
        echo "Token: " . substr($row['qr_token'], 0, 20) . "... appears {$row['count']} times\n";

        // Show which members have this token
        $members_stmt = $conn->prepare("SELECT id, fullname, created_at FROM members WHERE qr_token = ?");
        $members_stmt->bind_param("s", $row['qr_token']);
        $members_stmt->execute();
        $members_result = $members_stmt->get_result();

        while ($member = $members_result->fetch_assoc()) {
            echo "  - ID: {$member['id']}, Name: {$member['fullname']}, Created: {$member['created_at']}\n";
        }
        $members_stmt->close();
        echo "\n";
    }
} else {
    echo "✓ No duplicate QR tokens found\n";
}

echo "\n=== Total Members with QR Tokens ===\n";
$count_result = $conn->query("SELECT COUNT(*) as total FROM members WHERE qr_token IS NOT NULL AND qr_token != ''");
$total = $count_result->fetch_assoc()['total'];
echo "Total members with QR tokens: $total\n";

$conn->close();
?>
