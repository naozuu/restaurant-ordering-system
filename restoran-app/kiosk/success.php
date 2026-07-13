<?php

$pageTitle = 'Order Successful';

require_once __DIR__ . '/includes/header.php';

$order = $_SESSION['last_kiosk_order'] ?? null;

if (!$order) {
    header('Location: index.php');
    exit;
}
?>

<div class="success-page">
    <div class="success-card">
        <div class="success-icon">✓</div>

        <p class="eyebrow">Order Sent Successfully</p>
        <h1>Thank you, <?= htmlspecialchars($order['nama_pelanggan']); ?>!</h1>

        <p class="success-message">
            Your order has reached the cashier and will be processed shortly.
        </p>

        <div class="order-code">
            <span>Order Code</span>
            <strong><?= htmlspecialchars($order['kode_pesanan']); ?></strong>
        </div>

        <div class="success-details">
            <div>
                <span>Order Type</span>
                <strong>
                    <?= $order['jenis_pesanan'] === 'takeaway'
                        ? 'Takeaway'
                        : 'Dine In'; ?>
                </strong>
            </div>

            <div>
                <span>Table</span>
                <strong><?= htmlspecialchars($order['nomor_meja']); ?></strong>
            </div>

            <div>
                <span>Total</span>
                <strong>
                    <?= formatRupiah((float) $order['total_harga']); ?>
                </strong>
            </div>
        </div>

        <p class="payment-note">
            Please make your payment at the cashier.
        </p>

        <a class="new-order-button" href="reset.php">
            Place Another Order
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
