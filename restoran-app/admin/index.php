<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/operasional-functions.php';

function getSingleValue(mysqli $conn, string $sql): float
{
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($result);

    return (float) ($row[0] ?? 0);
}

$activeSession = getActiveRestaurantSession($conn);

$totalMenu = getSingleValue(
    $conn,
    "SELECT COUNT(*) FROM menu"
);

$pesananMenunggu = getSingleValue(
    $conn,
    "SELECT COUNT(*) FROM orders WHERE status_pesanan = 'menunggu'"
);

$pesananHariIni = getSingleValue(
    $conn,
    "SELECT COUNT(*)
     FROM orders
     INNER JOIN restaurant_sessions
        ON restaurant_sessions.id = orders.session_id
     WHERE restaurant_sessions.business_date = CURDATE()"
);

$totalPendapatan = getSingleValue(
    $conn,
    "SELECT COALESCE(SUM(orders.total_harga), 0)
     FROM orders
     INNER JOIN restaurant_sessions
        ON restaurant_sessions.id = orders.session_id
     WHERE restaurant_sessions.business_date = CURDATE()
       AND orders.status_pembayaran = 'sudah_bayar'
       AND orders.status_pesanan <> 'dibatalkan'"
);

$pesananTerbaru = mysqli_query(
    $conn,
    "SELECT id, kode_pesanan, nama_pelanggan, nomor_meja,
            total_harga, status_pesanan, created_at
     FROM orders
     ORDER BY id DESC
     LIMIT 5"
);

$pageTitle = 'Dashboard';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">Dashboard</h1>
        <p class="text-secondary mb-0">
            Restaurant activity overview.
        </p>
    </div>
</div>

<div class="alert <?= $activeSession ? 'alert-success' : 'alert-danger'; ?> d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <strong>
            The restaurant is <?= $activeSession ? 'open' : 'closed'; ?>
        </strong>

        <?php if ($activeSession): ?>
            <div>
                Opened at
                <?= date('H:i:s', strtotime($activeSession['opened_at'])); ?>
                by
                <?= htmlspecialchars($activeSession['opened_by_name'] ?: 'Admin'); ?>.
            </div>
        <?php else: ?>
            <div>The kiosk cannot accept new orders.</div>
        <?php endif; ?>
    </div>

    <a class="btn <?= $activeSession ? 'btn-outline-success' : 'btn-outline-danger'; ?>" href="operasional.php">
        Manage Operations
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-secondary mb-2">Total Menu Items</p>
                <h2 class="mb-0"><?= number_format($totalMenu); ?></h2>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-secondary mb-2">Waiting Orders</p>
                <h2 class="mb-0"><?= number_format($pesananMenunggu); ?></h2>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-secondary mb-2">Today's Orders</p>
                <h2 class="mb-0"><?= number_format($pesananHariIni); ?></h2>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-secondary mb-2">Today's Revenue</p>
                <h2 class="h4 mb-0">
                    <?= formatAdminRupiah($totalPendapatan); ?>
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Latest Orders</strong>
        <a class="btn btn-sm btn-dark" href="pesanan.php">
            View All
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Kode</th>
                <th>Customer</th>
                <th>Table</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($pesanan = mysqli_fetch_assoc($pesananTerbaru)): ?>
                <tr>
                    <td>
                        <a href="detail-pesanan.php?id=<?= (int) $pesanan['id']; ?>">
                            <?= htmlspecialchars($pesanan['kode_pesanan']); ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($pesanan['nama_pelanggan']); ?></td>
                    <td><?= htmlspecialchars($pesanan['nomor_meja']); ?></td>
                    <td>
                        <?= formatAdminRupiah((float) $pesanan['total_harga']); ?>
                    </td>
                    <td>
                        <?= htmlspecialchars(orderStatusLabel($pesanan['status_pesanan'])); ?>
                    </td>
                    <td><?= date('M d, Y H:i', strtotime($pesanan['created_at'])); ?></td>
                </tr>
            <?php endwhile; ?>

            <?php if (mysqli_num_rows($pesananTerbaru) === 0): ?>
                <tr>
                    <td class="text-center text-secondary py-4" colspan="6">
                        There are no orders yet.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
