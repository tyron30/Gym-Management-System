<?php
include "config/db.php";

$token = '91eaadcc4a6b8451964eb665a902daffcb3c0d65ff5cdd23a531a2cbcc9662f0';
$stmt = $conn->prepare("UPDATE members SET qr_token = ? WHERE id = 172");
$stmt->bind_param("s", $token);
$result = $stmt->execute();

echo "Update result: " . ($result ? "success" : "failed") . "\n";
if (!$result) {
    echo "Error: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
?>
