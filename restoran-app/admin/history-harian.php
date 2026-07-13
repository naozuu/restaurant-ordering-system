<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/operasional-functions.php';

$month = $_GET['month'] ?? date('Y-m');

if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $month = date('Y-m');
}

$startDate = $month . '-01';
$endDate = date('Y-m-t', strtotime($startDate));

$sql = "
    SELECT
        restaurant_sessions.business_date,
        MIN(restaurant_sessions.opened_at) AS first_opened_at,
        MAX(restaurant_sessions.closed_at) AS last_closed_at,
        COUNT(DISTINCT restaurant_sessions.id) AS total_sessions,
        COUNT(
            DISTINCT CASE
                WHEN restaurant_sessions.status = 'open'
                THEN restaurant_sessions.id
            END
        ) AS open_sessions,
        COUNT(DISTINCT orders.id) AS total_orders,
        COUNT(
            DISTINCT CASE
                WHEN orders.status_pesanan = 'dibatalkan'
                THEN orders.id
            END
        ) AS cancelled_orders,
        COUNT(
            DISTINCT CASE
                WHEN orders.status_pembayaran = 'sudah_bayar'
                 AND orders.status_pesanan <> 'dibatalkan'
                THEN orders.id
            END
        ) AS paid_orders,
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
    WHERE restaurant_sessions.business_date BETWEEN ? AND ?
    GROUP BY restaurant_sessions.business_date
    ORDER BY restaurant_sessions.business_date DESC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$pageTitle = 'Daily History';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Daily History</h1>
        <p class="text-secondary mb-0">
            Daily overview of operating hours, orders, and revenue.
        </p>
    </div>

    <form class="d-flex gap-2" method="GET">
        <input
            class="form-control"
            name="month"
            type="month"
            value="<?= htmlspecialchars($month); ?>"
        >

        <button class="btn btn-dark" type="submit">
            Show
        </button>
    </form>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Date</th>
                <th>Opening Time</th>
                <th>Closing Time</th>
                <th>Sessions</th>
                <th>Orders</th>
                <th>Paid</th>
                <th>Cancelled</th>
                <th>Revenue</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($day = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>
                        <strong>
                            <?= date('M d, Y', strtotime($day['business_date'])); ?>
                        </strong>
                    </td>

                    <td>
                        <?= date('H:i:s', strtotime($day['first_opened_at'])); ?>
                    </td>

                    <td>
                        <?= $day['last_closed_at']
                            ? date('H:i:s', strtotime($day['last_closed_at']))
                            : '-'; ?>
                    </td>

                    <td><?= (int) $day['total_sessions']; ?></td>
                    <td><?= (int) $day['total_orders']; ?></td>
                    <td><?= (int) $day['paid_orders']; ?></td>
                    <td><?= (int) $day['cancelled_orders']; ?></td>

                    <td>
                        <strong>
                            <?= formatAdminRupiah((float) $day['paid_revenue']); ?>
                        </strong>
                    </td>

                    <td>
                        <?php if ((int) $day['open_sessions'] > 0): ?>
                            <span class="badge text-bg-success">Still Open</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Closed</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-end">
                        <a
                            class="btn btn-sm btn-outline-dark"
                            href="detail-history.php?date=<?= urlencode($day['business_date']); ?>"
                        >
                            Details
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

            <?php if (mysqli_num_rows($result) === 0): ?>
                <tr>
                    <td class="text-center text-secondary py-5" colspan="10">
                        There is no operating history for this month.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>



<?php require __DIR__ . '/includes/footer.php'; ?>
