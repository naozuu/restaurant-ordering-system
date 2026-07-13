<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Restaurant Ordering';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1"
    >

    <title><?= htmlspecialchars($pageTitle); ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/kiosk.css">
</head>

<body>
<header class="kiosk-header">
    <a class="brand" href="index.php">
        <span class="brand-icon">🍽️</span>
        <span>
            <strong>Gwehj Restaurant</strong>
            <small>Order directly from this screen</small>
        </span>
    </a>

    <div class="header-actions">

        <a class="btn btn-danger btn-lg" href="reset.php">
            Cancel Order
        </a>
    </div>
</header>

<main>
