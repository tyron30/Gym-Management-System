<?php
include "config/db.php";

// Add qr_token column to members table if it doesn't exist
$alter_query = "ALTER TABLE members ADD COLUMN qr_token VARCHAR(64) DEFAULT NULL UNIQUE AFTER qr_code";

if ($conn->query($alter_query) === TRUE) {
    echo "Successfully added qr_token column to members table.\n";
} else {
    echo "Error adding column: " . $conn->error . "\n";
}

$conn->close();
?>
