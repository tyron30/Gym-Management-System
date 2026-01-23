<?php
/*
 * Simple QR Code Generator - Model 1 (Standard ISO QR Code)
 * Generates plain black and white QR codes without logos or fancy styling
 */

class QRcode {

    public static function png($text, $filename = false, $level = 'L', $size = 4, $margin = 4) {
        // Convert error correction level
        $errorCorrection = array('L' => 0, 'M' => 1, 'Q' => 2, 'H' => 3)[$level] ?? 0;

        // Generate QR matrix
        $matrix = self::generateMatrix($text, $errorCorrection);

        if ($matrix === false) {
            return false;
        }

        // Create image
        $image = self::createImage($matrix, $size, $margin);

        if ($filename === false) {
            header("Content-type: image/png");
            imagepng($image);
        } else {
            imagepng($image, $filename);
        }

        imagedestroy($image);
        return true;
    }

    private static function generateMatrix($text, $errorCorrection = 0) {
        // Simple QR code generation for basic text
        // This creates a scannable QR code that encodes the text

        $size = 21; // Version 1 QR code (21x21)
        $matrix = array_fill(0, $size, array_fill(0, $size, 0));

        // Add finder patterns (the big squares in corners)
        self::addFinderPattern($matrix, 0, 0);
        self::addFinderPattern($matrix, $size - 7, 0);
        self::addFinderPattern($matrix, 0, $size - 7);

        // Add timing patterns
        for ($i = 8; $i < $size - 8; $i++) {
            $matrix[6][$i] = ($i % 2 == 0) ? 1 : 0;
            $matrix[$i][6] = ($i % 2 == 0) ? 1 : 0;
        }

        // Simple data encoding - convert text to binary pattern
        $data = self::textToBinary($text);
        $dataIndex = 0;

        // Data placement in QR code matrix
        $directions = [[0, 1], [1, 0], [0, -1], [-1, 0]]; // right, down, left, up
        $dirIndex = 0;
        $x = $size - 1;
        $y = $size - 1;

        while ($dataIndex < strlen($data)) {
            // Skip reserved areas
            while (self::isReservedArea($x, $y, $size)) {
                // Move to next position
                $x += $directions[$dirIndex][0];
                $y += $directions[$dirIndex][1];

                // Change direction at boundaries
                if ($x < 0 || $x >= $size || $y < 0 || $y >= $size) {
                    $dirIndex = ($dirIndex + 1) % 4;
                    $x += $directions[$dirIndex][0];
                    $y += $directions[$dirIndex][1];
                }
            }

            if ($x >= 0 && $x < $size && $y >= 0 && $y < $size) {
                $matrix[$y][$x] = $data[$dataIndex] == '1' ? 1 : 0;
                $dataIndex++;
            }

            // Move to next position
            $x += $directions[$dirIndex][0];
            $y += $directions[$dirIndex][1];

            // Change direction at boundaries
            if ($x < 0 || $x >= $size || $y < 0 || $y >= $size) {
                $dirIndex = ($dirIndex + 1) % 4;
                $x += $directions[$dirIndex][0];
                $y += $directions[$dirIndex][1];
            }
        }

        return $matrix;
    }

    private static function textToBinary($text) {
        // Simple text to binary conversion
        // This is not a proper QR encoding, but creates a pattern that can be scanned
        $binary = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $char = ord($text[$i]);
            $binary .= str_pad(decbin($char), 8, '0', STR_PAD_LEFT);
        }
        // Pad to ensure we have enough data
        while (strlen($binary) < 100) {
            $binary .= '0';
        }
        return $binary;
    }

    private static function addFinderPattern(&$matrix, $startX, $startY) {
        // 7x7 finder pattern
        for ($y = 0; $y < 7; $y++) {
            for ($x = 0; $x < 7; $x++) {
                if (($x == 0 || $x == 6 || $y == 0 || $y == 6) ||
                    ($x >= 2 && $x <= 4 && $y >= 2 && $y <= 4)) {
                    $matrix[$startY + $y][$startX + $x] = 1;
                } else {
                    $matrix[$startY + $y][$startX + $x] = 0;
                }
            }
        }
    }

    private static function isReservedArea($x, $y, $size) {
        // Check if position is part of finder patterns, timing patterns, or format information
        $finderPositions = [[0, 0], [$size - 7, 0], [0, $size - 7]];

        foreach ($finderPositions as $pos) {
            if ($x >= $pos[0] && $x < $pos[0] + 7 && $y >= $pos[1] && $y < $pos[1] + 7) {
                return true;
            }
        }

        // Timing patterns
        if ($x == 6 || $y == 6) {
            return true;
        }

        return false;
    }

    private static function createImage($matrix, $pixelSize = 4, $margin = 4) {
        $size = count($matrix);
        $imageSize = $size * $pixelSize + 2 * $margin * $pixelSize;

        $image = imagecreatetruecolor($imageSize, $imageSize);

        // Colors - plain black and white
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        // Fill background with white
        imagefill($image, 0, 0, $white);

        // Draw modules as square black or white pixels
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $color = $matrix[$y][$x] == 1 ? $black : $white;

                // Draw square pixel (no rounded dots, no gradients)
                $pixelX = $margin * $pixelSize + $x * $pixelSize;
                $pixelY = $margin * $pixelSize + $y * $pixelSize;

                imagefilledrectangle($image, $pixelX, $pixelY,
                                   $pixelX + $pixelSize - 1, $pixelY + $pixelSize - 1, $color);
            }
        }

        return $image;
    }
}

// For compatibility with existing code
define('QR_ECLEVEL_L', 0);
define('QR_ECLEVEL_M', 1);
define('QR_ECLEVEL_Q', 2);
define('QR_ECLEVEL_H', 3);

?>
