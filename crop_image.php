<?php
// crop_image.php

$filePath = __DIR__ . '/public/medflow-logo.png';
if (!file_exists($filePath)) {
    die("File not found: $filePath\n");
}

$img = imagecreatefrompng($filePath);
if (!$img) {
    die("Failed to create image resource.\n");
}

// Get image dimensions
$width = imagesx($img);
$height = imagesy($img);

// Define what is "white" or background.
// We'll scan the image to find the bounding box of non-white pixels.
// A pixel is considered white if r, g, b are all > 240
$minX = $width;
$maxX = 0;
$minY = $height;
$maxY = 0;

for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
        $rgb = imagecolorat($img, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        if (!($r > 240 && $g > 240 && $b > 240)) {
            if ($x < $minX) $minX = $x;
            if ($x > $maxX) $maxX = $x;
            if ($y < $minY) $minY = $y;
            if ($y > $maxY) $maxY = $y;
        }
    }
}

// Check if we found anything
if ($minX > $maxX || $minY > $maxY) {
    die("Image seems to be entirely white/blank.\n");
}

// Add a little padding (e.g. 10px)
$padding = 10;
$minX = max(0, $minX - $padding);
$minY = max(0, $minY - $padding);
$maxX = min($width - 1, $maxX + $padding);
$maxY = min($height - 1, $maxY + $padding);

$croppedWidth = $maxX - $minX + 1;
$croppedHeight = $maxY - $minY + 1;

$croppedImg = imagecreatetruecolor($croppedWidth, $croppedHeight);

// Keep transparency if any (though we assume white)
imagealphablending($croppedImg, false);
imagesavealpha($croppedImg, true);
$transparent = imagecolorallocatealpha($croppedImg, 255, 255, 255, 127);
imagefill($croppedImg, 0, 0, $transparent);

// Copy the cropped region
imagecopy($croppedImg, $img, 0, 0, $minX, $minY, $croppedWidth, $croppedHeight);

// Save the cropped image over the original
if (imagepng($croppedImg, $filePath)) {
    echo "Successfully cropped the image. New dimensions: {$croppedWidth}x{$croppedHeight}\n";
} else {
    echo "Failed to save cropped image.\n";
}

imagedestroy($img);
imagedestroy($croppedImg);

