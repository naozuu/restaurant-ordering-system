<?php

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="col-md-3 col-lg-2 d-md-block bg-white sidebar border-end">
    <div class="position-sticky p-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a
                    class="nav-link <?= $currentPage === 'index.php' ? 'active' : ''; ?>"
                    href="index.php"
                >
                    Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a
                    class="nav-link <?= in_array($currentPage, ['operasional.php', 'proses-operasional.php'], true) ? 'active' : ''; ?>"
                    href="operasional.php"
                >
                    Restaurant Operations
                </a>
            </li>

            <li class="nav-item">
                <a
                    class="nav-link <?= in_array($currentPage, ['history-harian.php', 'detail-history.php'], true) ? 'active' : ''; ?>"
                    href="history-harian.php"
                >
                    History
                </a>
            </li>

            <li class="nav-item">
                <a
                    class="nav-link <?= in_array($currentPage, ['menu.php', 'tambah-menu.php', 'edit-menu.php'], true) ? 'active' : ''; ?>"
                    href="menu.php"
                >
                    Manage Menu
                </a>
            </li>

            <li class="nav-item">
                <a
                    class="nav-link <?= in_array($currentPage, ['pesanan.php', 'detail-pesanan.php'], true) ? 'active' : ''; ?>"
                    href="pesanan.php"
                >
                    Orders
                </a>
            </li>
        </ul>
    </div>
</nav>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
