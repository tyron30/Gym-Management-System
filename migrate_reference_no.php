<?php
include "config/db.php";

echo "Starting database migration to add reference_no column...<br>";

// Add reference_no column to payments table
try {
    $conn->query("ALTER TABLE payments ADD COLUMN reference_no VARCHAR(100) NULL AFTER discount_amount");
    echo "✓ Added reference_no column to payments table<br>";
} catch (Exception $e) {
    echo "Note: reference_no column might already exist in payments table<br>";
}

echo "<br>Database migration completed successfully!<br>";
echo "You can now use the reference number field for digital payments.<br>";
?>
