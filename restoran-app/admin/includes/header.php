<?php

require_once __DIR__ . '/labels.php';

$pageTitle = $pageTitle ?? 'Restaurant Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle); ?> | Our Restaurant</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        body {
            background: #f6f7fb;
        }

        .sidebar {
            min-height: calc(100vh - 56px);
        }

        .sidebar .nav-link {
            color: #333;
            border-radius: 8px;
            margin-bottom: 4px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: #212529;
        }

        .menu-image {
            width: 72px;
            height: 56px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>

<body>
<nav class="navbar navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Restaurant Admin</a>

        <div class="d-flex align-items-center gap-3 text-white">
            <span>
                <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Administrator'); ?>
            </span>

            <a class="btn btn-outline-light btn-sm" href="logout.php">
                Log Out
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
