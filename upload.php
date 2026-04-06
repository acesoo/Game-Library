<?php
/**
 * upload.php - Local image upload for game covers
 * Saves to /uploads/ folder inside the project.
 */

function uploadImage(array $file, string $uploadDir = null): string|false
{
    if ($uploadDir === null) {
        $uploadDir = __DIR__ . '/uploads/';
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if ($file['error'] !== UPLOAD_ERR_OK) return false;

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mimeType     = mime_content_type($file['tmp_name']);

    if (!in_array($mimeType, $allowedTypes, true)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;

    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        return 'uploads/' . $filename;
    }

    return false;
}
