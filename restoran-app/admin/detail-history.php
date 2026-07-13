<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/operasional-functions.php';

$date = $_GET['date'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    header('Location: history-harian.php');
    exit;
}

$sessionStmt = mysqli_prepare(
    $conn,
    "SELECT
        restaurant_sessions.*,
        opener.nama AS opened_by_name,
        closer.nama AS closed_by_name
     FROM restaurant_sessions
     LEFT JOIN users opener ON opener.id = restaurant_sessions.opened_by
     LEFT JOIN users closer ON closer.id = restaurant_sessions.closed_by
     WHERE restaurant_sessions.business_date = ?
     ORDER BY restaurant_sessions.opened_at ASC"
);

mysqli_stmt_bind_param($sessionStmt, 's', $date);
mysqli_stmt_execute($sessionStmt);
$sessions = mysqli_stmt_get_result($sessionStmt);

$orderStmt = mysqli_prepare(
    $conn,
    "SELECT
        orders.*,
        restaurant_sessions.opened_at AS session_opened_at
     FROM orders
     INNER JOIN restaurant_sessions
        ON restaurant_sessions.id = orders.session_id
     WHERE restaurant_sessions.business_date = ?
     ORDER BY orders.created_at ASC"
);

mysqli_stmt_bind_param($orderStmt, 's', $date);
mysqli_stmt_execute($orderStmt);
$orders = mysqli_stmt_get_result($orderStmt);

$summaryStmt = mysqli_prepare(
    $conn,
    "SELECT
        COUNT(DISTINCT orders.id) AS total_orders,
        COUNT(
            DISTINCT CASE
                WHEN orders.status_pembayaran = 'sudah_bayar'
                 AND orders.status_pesanan <> 'dibatalkan'
                THEN orders.id
            END
        ) AS paid_orders,
        COUNT(
            DISTINCT CASE
                WHEN orders.status_pesanan = 'dibatalkan'
                THEN orders.id
            END
        ) AS cancelled_orders,
        COALESCE(
            SUM(
                CASE
                    WHEN orders.status_pembayaran = 'sudah_bayar'
                     AND orders.status_pesanan <> 'dibatalkan'
                    THEN orders.total_harga
                    ELSE 0
                END
            ),
            0
        ) AS paid_revenue
     FROM restaurant_sessions
     LEFT JOIN orders ON orders.session_id = restaurant_sessions.id
     WHERE restaurant_sessions.business_date = ?"
);

mysqli_stmt_bind_param($summaryStmt, 's', $date);
mysqli_stmt_execute($summaryStmt);

$summary = mysqli_fetch_assoc(mysqli_stmt_get_result($summaryStmt)) ?: [
    'total_orders' => 0,
    'paid_orders' => 0,
    'cancelled_orders' => 0,
    'paid_revenue' => 0,
];

$pageTitle = 'History Detailss';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">History Detailss</h1>
        <p class="text-secondary mb-0">
            Date <?= date('M d, Y', strtotime($date)); ?>
        </p>
    </div>

    <a class="btn btn-outline-secondary" href="history-harian.php">
        Back
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-secondary mb-1">Total Orders</p>
                <h3 class="mb-0"><?= (int) $summary['total_orders']; ?></h3>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-secondary mb-1">Paid Orders</p>
                <h3 class="mb-0"><?= (int) $summary['paid_orders']; ?></h3>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-secondary mb-1">Cancelled Orders</p>
                <h3 class="mb-0"><?= (int) $summary['cancelled_orders']; ?></h3>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-secondary mb-1">Paid Revenue</p>
                <h3 class="h4 mb-0">
                    <?= formatAdminRupiah((float) $summary['paid_revenue']); ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <strong>Opening / Closing Sessions</strong>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>Opening Time</th>
                <th>Opened By</th>
                <th>Closing Time</th>
                <th>Closed By</th>
                <th>Notes</th>
                <th>Status</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($session = mysqli_fetch_assoc($sessions)): ?>
                <tr>
                    <td><?= date('H:i:s', strtotime($session['opened_at'])); ?></td>
                    <td><?= htmlspecialchars($session['opened_by_name'] ?: '-'); ?></td>
                    <td>
                        <?= $session['closed_at']
                            ? date('H:i:s', strtotime($session['closed_at']))
                            : '-'; ?>
                    </td>
                    <td><?= htmlspecialchars($session['closed_by_name'] ?: '-'); ?></td>
                    <td>
                        <div>
                            <strong>Opening:</strong>
                            <?= htmlspecialchars($session['opening_note'] ?: '-'); ?>
                        </div>
                        <div>
                            <strong>Closing:</strong>
                            <?= htmlspecialchars($session['closing_note'] ?: '-'); ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?= $session['status'] === 'open' ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                            <?= $session['status'] === 'open' ? 'Open' : 'Closed'; ?>
                        </span>
                    </td>
                </tr>
            <?php endwhile; ?>

            <?php if (mysqli_num_rows($sessions) === 0): ?>
                <tr>
                    <td class="text-center text-secondary py-4" colspan="6">
                        There are no operating sessions.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <strong>Orders for This Day</strong>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Time</th>
                <th>Code</th>
                <th>Customer</th>
                <th>Table</th>
                <th>Total</th>
                <th>Status</th>
                <th>Payment</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td><?= date('H:i:s', strtotime($order['created_at'])); ?></td>
                    <td><?= htmlspecialchars($order['kode_pesanan']); ?></td>
                    <td><?= htmlspecialchars($order['nama_pelanggan']); ?></td>
                    <td><?= htmlspecialchars($order['nomor_meja']); ?></td>
                    <td><?= formatAdminRupiah((float) $order['total_harga']); ?></td>
                    <td>
                        <?= htmlspecialchars(orderStatusLabel($order['status_pesanan'])); ?>
                    </td>
                    <td>
                        <?= htmlspecialchars(paymentStatusLabel($order['status_pembayaran'])); ?>
                    </td>
                    <td class="text-end">
                        <a
                            class="btn btn-sm btn-dark"
                            href="detail-pesanan.php?id=<?= (int) $order['id']; ?>"
                        >
                            Details
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

            <?php if (mysqli_num_rows($orders) === 0): ?>
                <tr>
                    <td class="text-center text-secondary py-4" colspan="8">
                        There are no orders for this date.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
