<?php
include "config/db.php";

$result = $conn->query("SELECT id, fullname, qr_token, LENGTH(qr_token) as token_length FROM members WHERE id = 172");
$row = $result->fetch_assoc();

echo "ID: " . $row['id'] . ", Name: " . $row['fullname'] . ", Token: " . $row['qr_token'] . ", Length: " . $row['token_length'] . "\n";

$conn->close();
?>
