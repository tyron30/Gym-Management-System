<?php
// Generate QR code for attendance system
// Model 1 QR Code: Plain black modules, white background, no logos, standard ISO appearance

// Get the text parameter
$text = isset($_GET['text']) ? trim($_GET['text']) : '';

if (empty($text)) {
    // Return a blank QR code if no text provided
    $text = 'INVALID';
}

// Include the QR code library
include_once 'phpqrcode/qrlib.php';

// Set headers for PNG image
header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Generate QR code with Model 1 specifications:
// - QR_ECLEVEL_L: Low error correction (good for clean codes)
// - Size 4: Standard module size
// - Margin 4: Standard quiet zone
QRcode::png($text, false, QR_ECLEVEL_L, 4, 4);
exit;
?>
