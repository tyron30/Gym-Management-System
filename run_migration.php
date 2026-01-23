<?php
include "config/db.php";

// Read the migration SQL file
$sql = file_get_contents('migrate_user_tracking.sql');

// Split into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if (!empty($statement)) {
        if ($conn->query($statement) === TRUE) {
            echo "Migration statement executed successfully: " . substr($statement, 0, 50) . "...<br>";
        } else {
            echo "Error executing statement: " . $conn->error . "<br>";
        }
    }
}

echo "Migration completed.";
$conn->close();
?>
