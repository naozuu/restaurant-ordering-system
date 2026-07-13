<?php

$pageTitle = 'Confirm Your Order';

require_once __DIR__ . '/includes/header.php';

if (!getActiveRestaurantSession($conn)) {
    $_SESSION['kiosk_cart'] = [];
    header('Location: index.php');
    exit;
}

$cartItems = getCartDetails($conn);
$cartTotal = getCartTotal($cartItems);

if (!$cartItems) {
    header('Location: index.php');
    exit;
}

$error = $_GET['error'] ?? '';
?>

<div class="checkout-page">
    <section class="checkout-form-section">
        <a class="back-link" href="index.php">← Back to the menu</a>

        <div class="checkout-heading">
            <p class="eyebrow">Final Step</p>
            <h1>Confirm Your Order</h1>
            <p>Enter the customer information before sending the order to the cashier.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form
            class="checkout-form"
            action="process-order.php"
            method="POST"
            id="checkoutForm"
        >
            <div class="form-group">
                <label for="nama_pelanggan">Customer Name</label>

                <input
                    id="nama_pelanggan"
                    name="nama_pelanggan"
                    type="text"
                    maxlength="100"
                    placeholder="Example: Alex"
                    required
                >
            </div>

            <div class="form-group">
                <label>Order Type</label>

                <div class="order-type-grid">
                    <label class="order-type-card">
                        <input
                            name="jenis_pesanan"
                            type="radio"
                            value="dine_in"
                            checked
                        >
                        <span>
                            <strong>🍽️ Dine In</strong>
                            <small>The order will be delivered to your table</small>
                        </span>
                    </label>

                    <label class="order-type-card">
                        <input
                            name="jenis_pesanan"
                            type="radio"
                            value="takeaway"
                        >
                        <span>
                            <strong>🥡 Takeaway</strong>
                            <small>Collect the order at the cashier</small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="form-group" id="tableNumberGroup">
                <label for="nomor_meja">Table Number</label>

                <input
                    id="nomor_meja"
                    name="nomor_meja"
                    type="text"
                    maxlength="20"
                    placeholder="Example: 05"
                    required
                >
            </div>

            <div class="form-group">
                <label for="catatan">Order Notes</label>

                <textarea
                    id="catatan"
                    name="catatan"
                    rows="4"
                    maxlength="500"
                    placeholder="Example: not spicy, no onions"
                ></textarea>
            </div>

            <button class="submit-order-button" type="submit">
                Send Order to Cashier
            </button>
        </form>
    </section>

    <aside class="order-summary">
        <h2>Order Summary</h2>

        <div class="summary-items">
            <?php foreach ($cartItems as $item): ?>
                <div class="summary-item">
                    <div>
                        <strong>
                            <?= htmlspecialchars($item['nama_menu']); ?>
                        </strong>

                        <small>
                            <?= (int) $item['jumlah']; ?> ×
                            <?= formatRupiah((float) $item['harga']); ?>
                        </small>
                    </div>

                    <strong>
                        <?= formatRupiah((float) $item['subtotal']); ?>
                    </strong>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="summary-total">
            <span>Total Payment</span>
            <strong><?= formatRupiah($cartTotal); ?></strong>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
