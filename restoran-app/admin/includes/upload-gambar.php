<?php

function uploadGambar(array $file, ?string $gambarLama = null): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $gambarLama;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The image upload failed.');
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('The maximum image size is 2 MB.');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!isset($allowedMimeTypes[$mimeType])) {
        throw new RuntimeException('The image must be in JPG, PNG, or WEBP format.');
    }

    $uploadDirectory = __DIR__ . '/../../assets/images/';

    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0775, true);
    }

    $filename = bin2hex(random_bytes(12)) . '.' . $allowedMimeTypes[$mimeType];
    $destination = $uploadDirectory . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('The image could not be saved.');
    }

    if ($gambarLama) {
        $oldPath = $uploadDirectory . basename($gambarLama);

        if (is_file($oldPath)) {
            unlink($oldPath);
        }
    }

    return $filename;
}
