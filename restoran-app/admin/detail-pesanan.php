<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id < 1) {
    header('Location: pesanan.php');
    exit;
}

$orderStmt = mysqli_prepare(
    $conn,
    "SELECT * FROM orders WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($orderStmt, 'i', $id);
mysqli_stmt_execute($orderStmt);

$order = mysqli_fetch_assoc(mysqli_stmt_get_result($orderStmt));

if (!$order) {
    header(
        'Location: pesanan.php?type=danger&message=' .
        urlencode('The order was not found.')
    );
    exit;
}

$detailStmt = mysqli_prepare(
    $conn,
    "SELECT
        order_details.*,
        menu.nama_menu
     FROM order_details
     INNER JOIN menu ON menu.id = order_details.menu_id
     WHERE order_details.order_id = ?
     ORDER BY order_details.id ASC"
);

mysqli_stmt_bind_param($detailStmt, 'i', $id);
mysqli_stmt_execute($detailStmt);

$details = mysqli_stmt_get_result($detailStmt);

$pageTitle = 'Order Details';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Order Details</h1>
        <p class="text-secondary mb-0">
            <?= htmlspecialchars($order['kode_pesanan']); ?>
        </p>
    </div>

    <a class="btn btn-outline-secondary" href="pesanan.php">
        Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <strong>Ordered Items</strong>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Menu Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php while ($detail = mysqli_fetch_assoc($details)): ?>
                        <tr>
                            <td><?= htmlspecialchars($detail['nama_menu']); ?></td>
                            <td>
                                Rp<?= number_format($detail['harga'], 0, ',', '.'); ?>
                            </td>
                            <td><?= (int) $detail['jumlah']; ?></td>
                            <td>
                                Rp<?= number_format($detail['subtotal'], 0, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>

                    <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th>
                            Rp<?= number_format($order['total_harga'], 0, ',', '.'); ?>
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <strong>Customer Information</strong>
            </div>

            <div class="card-body">
                <p>
                    <strong>Name:</strong><br>
                    <?= htmlspecialchars($order['nama_pelanggan']); ?>
                </p>

                <p>
                    <strong>Table Number:</strong><br>
                    <?= htmlspecialchars($order['nomor_meja']); ?>
                </p>

                <p>
                    <strong>Date:</strong><br>
                    <?= date('M d, Y H:i', strtotime($order['created_at'])); ?>
                </p>

                <p class="mb-0">
                    <strong>Notes:</strong><br>
                    <?= nl2br(htmlspecialchars($order['catatan'] ?: '-')); ?>
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <strong>Update Status</strong>
            </div>

            <div class="card-body">
                <form action="update-status.php" method="POST">
                    <input name="id" type="hidden" value="<?= $id; ?>">

                    <div class="mb-3">
                        <label class="form-label" for="status_pesanan">
                            Order Status
                        </label>

                        <select
                            class="form-select"
                            id="status_pesanan"
                            name="status_pesanan"
                        >
                            <?php
                            $orderStatuses = [
                                'menunggu',
                                'diproses',
                                'siap',
                                'selesai',
                                'dibatalkan',
                            ];
                            ?>

                            <?php foreach ($orderStatuses as $status): ?>
                                <option
                                    value="<?= $status; ?>"
                                    <?= $order['status_pesanan'] === $status ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars(orderStatusLabel($status)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="status_pembayaran">
                            Payment Status
                        </label>

                        <select
                            class="form-select"
                            id="status_pembayaran"
                            name="status_pembayaran"
                        >
                            <option
                                value="belum_bayar"
                                <?= $order['status_pembayaran'] === 'belum_bayar' ? 'selected' : ''; ?>
                            >
                                Unpaid
                            </option>

                            <option
                                value="sudah_bayar"
                                <?= $order['status_pembayaran'] === 'sudah_bayar' ? 'selected' : ''; ?>
                            >
                                Paid
                            </option>
                        </select>
                    </div>

                    <button class="btn btn-dark w-100" type="submit">
                        Save Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
