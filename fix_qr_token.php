<?php
include "config/db.php";

try {
    // Update any qr_token that is '0' or empty to a new generated token
    $stmt = $conn->prepare("SELECT id FROM members WHERE qr_token = '0' OR qr_token = '' OR qr_token IS NULL");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $new_token = bin2hex(random_bytes(32));
        $update_stmt = $conn->prepare("UPDATE members SET qr_token = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_token, $row['id']);
        $update_stmt->execute();
        $update_stmt->close();
        echo "Updated member ID {$row['id']} with new qr_token: $new_token\n";
    }
    
    $stmt->close();
    echo "QR token fix completed.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
