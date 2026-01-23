<?php
include "config/db.php";

echo "=== Latest Members with QR Tokens ===\n\n";

$result = $conn->query("SELECT id, fullname, qr_token, created_at, created_by FROM members WHERE qr_token IS NOT NULL AND qr_token != '' ORDER BY created_at DESC LIMIT 10");

while ($member = $result->fetch_assoc()) {
    echo "ID: {$member['id']}\n";
    echo "Name: {$member['fullname']}\n";
    echo "Token: " . substr($member['qr_token'], 0, 20) . "...\n";
    echo "Created: {$member['created_at']}\n";
    echo "Created By: {$member['created_by']}\n";
    echo "---\n";
}

$conn->close();
?>
