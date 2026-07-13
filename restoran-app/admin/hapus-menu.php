<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id < 1) {
    header('Location: menu.php?type=danger&message=' . urlencode('The menu item was not found.'));
    exit;
}

$selectStmt = mysqli_prepare(
    $conn,
    "SELECT gambar FROM menu WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($selectStmt, 'i', $id);
mysqli_stmt_execute($selectStmt);

$menu = mysqli_fetch_assoc(mysqli_stmt_get_result($selectStmt));

if (!$menu) {
    header('Location: menu.php?type=danger&message=' . urlencode('The menu item was not found.'));
    exit;
}

$deleteStmt = mysqli_prepare(
    $conn,
    "DELETE FROM menu WHERE id = ?"
);

mysqli_stmt_bind_param($deleteStmt, 'i', $id);

if (!mysqli_stmt_execute($deleteStmt)) {
    header(
        'Location: menu.php?type=danger&message=' .
        urlencode('The menu item cannot be deleted because it has already been used in an order.')
    );
    exit;
}

if ($menu['gambar']) {
    $imagePath = __DIR__ . '/../assets/images/' . basename($menu['gambar']);

    if (is_file($imagePath)) {
        unlink($imagePath);
    }
}

header(
    'Location: menu.php?type=success&message=' .
    urlencode('The menu item was deleted successfully.')
);
exit;
