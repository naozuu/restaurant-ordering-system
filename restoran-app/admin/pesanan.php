<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$allowedStatuses = [
    'menunggu',
    'diproses',
    'siap',
    'selesai',
    'dibatalkan',
];

$statusFilter = $_GET['status'] ?? '';

if ($statusFilter && in_array($statusFilter, $allowedStatuses, true)) {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT *
         FROM orders
         WHERE status_pesanan = ?
         ORDER BY id DESC"
    );

    mysqli_stmt_bind_param($stmt, 's', $statusFilter);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
} else {
    $statusFilter = '';

    $result = mysqli_query(
        $conn,
        "SELECT * FROM orders ORDER BY id DESC"
    );
}

$message = $_GET['message'] ?? '';
$type = $_GET['type'] ?? 'success';

$pageTitle = 'Orders';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Orders</h1>
        <p class="text-secondary mb-0">
            Monitor and update customer orders.
        </p>
    </div>

    <form class="d-flex gap-2" method="GET">
        <select class="form-select" name="status">
            <option value="">All Statuses</option>

            <?php foreach ($allowedStatuses as $status): ?>
                <option
                    value="<?= htmlspecialchars($status); ?>"
                    <?= $statusFilter === $status ? 'selected' : ''; ?>
                >
                    <?= htmlspecialchars(orderStatusLabel($status)); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="btn btn-dark" type="submit">
            Filter
        </button>
    </form>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($type); ?>">
        <?= htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Order Code</th>
                <th>Customer</th>
                <th>Table</th>
                <th>Total</th>
                <th>Order Status</th>
                <th>Payment</th>
                <th>Date</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($pesanan = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($pesanan['kode_pesanan']); ?></td>
                    <td><?= htmlspecialchars($pesanan['nama_pelanggan']); ?></td>
                    <td><?= htmlspecialchars($pesanan['nomor_meja']); ?></td>

                    <td>
                        Rp<?= number_format($pesanan['total_harga'], 0, ',', '.'); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(orderStatusLabel($pesanan['status_pesanan'])); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(paymentStatusLabel($pesanan['status_pembayaran'])); ?>
                    </td>

                    <td>
                        <?= date('M d, Y H:i', strtotime($pesanan['created_at'])); ?>
                    </td>

                    <td class="text-end">
                        <a
                            class="btn btn-sm btn-dark"
                            href="detail-pesanan.php?id=<?= (int) $pesanan['id']; ?>"
                        >
                            Details
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

            <?php if (mysqli_num_rows($result) === 0): ?>
                <tr>
                    <td class="text-center text-secondary py-4" colspan="8">
                        No orders were found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
