<?php
include "config/db.php";

// Test 1: Check if qr_token column exists and has data
echo "=== QR Code Attendance System Testing ===\n\n";

echo "1. Database Structure Check:\n";
$result = $conn->query("DESCRIBE members");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

if (in_array('qr_token', $columns)) {
    echo "✓ qr_token column exists in members table\n";
} else {
    echo "✗ qr_token column missing from members table\n";
}

echo "\n2. Existing Members QR Token Check:\n";
$result = $conn->query("SELECT id, fullname, qr_token FROM members");
$members_with_tokens = 0;
$members_without_tokens = 0;

while ($member = $result->fetch_assoc()) {
    if (!empty($member['qr_token'])) {
        $members_with_tokens++;
        echo "✓ Member {$member['fullname']} (ID: {$member['id']}) has QR token: " . substr($member['qr_token'], 0, 16) . "...\n";
    } else {
        $members_without_tokens++;
        echo "✗ Member {$member['fullname']} (ID: {$member['id']}) missing QR token\n";
    }
}

echo "\nSummary: $members_with_tokens members with tokens, $members_without_tokens without tokens\n";

echo "\n3. QR Token Uniqueness Check:\n";
$result = $conn->query("SELECT qr_token, COUNT(*) as count FROM members WHERE qr_token IS NOT NULL GROUP BY qr_token HAVING count > 1");
if ($result->num_rows > 0) {
    echo "✗ Duplicate QR tokens found:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  Token: " . substr($row['qr_token'], 0, 16) . "... appears {$row['count']} times\n";
    }
} else {
    echo "✓ All QR tokens are unique\n";
}

echo "\n4. Attendance Table Structure Check:\n";
$result = $conn->query("DESCRIBE attendance");
$attendance_columns = [];
while ($row = $result->fetch_assoc()) {
    $attendance_columns[] = $row['Field'];
}

$required_columns = ['id', 'member_id', 'checkin_time'];
$missing_columns = array_diff($required_columns, $attendance_columns);

if (empty($missing_columns)) {
    echo "✓ Attendance table has all required columns\n";
} else {
    echo "✗ Missing columns in attendance table: " . implode(', ', $missing_columns) . "\n";
}

echo "\n=== Testing Complete ===\n";
?>
