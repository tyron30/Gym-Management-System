<?php
include "config/db.php";

$result = $conn->query("SELECT id, qr_token, LENGTH(qr_token) as token_length FROM members WHERE id = 168");
$row = $result->fetch_assoc();

echo "ID: " . $row['id'] . "\n";
echo "Token: " . $row['qr_token'] . "\n";
echo "Length: " . $row['token_length'] . "\n";

$conn->close();
?>
