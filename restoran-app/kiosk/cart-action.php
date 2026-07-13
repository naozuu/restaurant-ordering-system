<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (!getActiveRestaurantSession($conn)) {
    $_SESSION['kiosk_cart'] = [];
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action = $_POST['action'] ?? '';
$menuId = (int) ($_POST['menu_id'] ?? 0);

if (!isset($_SESSION['kiosk_cart'])) {
    $_SESSION['kiosk_cart'] = [];
}

$cart = &$_SESSION['kiosk_cart'];

if ($action === 'clear') {
    $cart = [];
    header('Location: index.php');
    exit;
}

if ($menuId < 1) {
    header('Location: index.php');
    exit;
}

$menuStmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM menu
     WHERE id = ?
       AND status = 'tersedia'
     LIMIT 1"
);

mysqli_stmt_bind_param($menuStmt, 'i', $menuId);
mysqli_stmt_execute($menuStmt);

$menuExists = mysqli_fetch_assoc(mysqli_stmt_get_result($menuStmt));

if (!$menuExists && in_array($action, ['add', 'increase'], true)) {
    header('Location: index.php');
    exit;
}

$currentQuantity = max(0, (int) ($cart[$menuId] ?? 0));

switch ($action) {
    case 'add':
    case 'increase':
        $cart[$menuId] = min(99, $currentQuantity + 1);
        break;

    case 'decrease':
        if ($currentQuantity <= 1) {
            unset($cart[$menuId]);
        } else {
            $cart[$menuId] = $currentQuantity - 1;
        }
        break;

    case 'remove':
        unset($cart[$menuId]);
        break;
}

header('Location: index.php');
exit;
