<?php
include "config/db.php";

// Add deleted_at column to members table if it doesn't exist
try {
    $result = $conn->query("SHOW COLUMNS FROM members LIKE 'deleted_at'");
    if ($result->num_rows == 0) {
        // Column doesn't exist, add it
        $conn->query("ALTER TABLE members ADD COLUMN deleted_at TIMESTAMP NULL");
        echo "Successfully added 'deleted_at' column to members table.\n";
    } else {
        echo "'deleted_at' column already exists in members table.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
