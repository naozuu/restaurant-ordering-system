<?php

$pageTitle = 'Choose Your Menu';

require_once __DIR__ . '/includes/header.php';

$activeSession = getActiveRestaurantSession($conn);

if (!$activeSession):
    $_SESSION['kiosk_cart'] = [];
?>
<div class="restaurant-closed-page">
    <div class="restaurant-closed-card">
        <div class="restaurant-closed-icon">🔒</div>
        <p class="eyebrow">Orders Are Not Available Yet</p>
        <h1>The Restaurant Is Closed</h1>
        <p>
            Please wait until the cashier opens the restaurant.
            This page will check the status automatically.
        </p>
        <button class="new-order-button" type="button" onclick="window.location.reload();">
            Check Again
        </button>
    </div>
</div>
<script>
    window.setTimeout(function () {
        window.location.reload();
    }, 10000);
</script>
<?php
require_once __DIR__ . '/includes/footer.php';
exit;
endif;

$selectedCategory = (int) ($_GET['category'] ?? 0);

$categories = mysqli_query(
    $conn,
    "SELECT
        categories.id,
        categories.nama_kategori,
        COUNT(menu.id) AS total_menu
     FROM categories
     LEFT JOIN menu
        ON menu.category_id = categories.id
       AND menu.status = 'tersedia'
     GROUP BY categories.id, categories.nama_kategori
     ORDER BY categories.nama_kategori ASC"
);

if ($selectedCategory > 0) {
    $menuStmt = mysqli_prepare(
        $conn,
        "SELECT
            menu.*,
            categories.nama_kategori
         FROM menu
         INNER JOIN categories ON categories.id = menu.category_id
         WHERE menu.status = 'tersedia'
           AND menu.category_id = ?
         ORDER BY menu.nama_menu ASC"
    );

    mysqli_stmt_bind_param($menuStmt, 'i', $selectedCategory);
    mysqli_stmt_execute($menuStmt);
    $menuResult = mysqli_stmt_get_result($menuStmt);
} else {
    $menuResult = mysqli_query(
        $conn,
        "SELECT
            menu.*,
            categories.nama_kategori
         FROM menu
         INNER JOIN categories ON categories.id = menu.category_id
         WHERE menu.status = 'tersedia'
         ORDER BY categories.nama_kategori ASC, menu.nama_menu ASC"
    );
}

$cartItems = getCartDetails($conn);
$cartTotal = getCartTotal($cartItems);
$cartQuantity = getCartQuantity(getCart());
?>

<div class="kiosk-layout">
    <aside class="category-panel">
        <h2>Categories</h2>

        <nav class="category-list">
            <a
                class="category-button <?= $selectedCategory === 0 ? 'active' : ''; ?>"
                href="index.php"
            >
                <span>All Menu Items</span>
            </a>

            <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                <a
                    class="category-button <?= $selectedCategory === (int) $category['id'] ? 'active' : ''; ?>"
                    href="index.php?category=<?= (int) $category['id']; ?>"
                >
                    <span>
                        <?= htmlspecialchars(categoryLabel($category['nama_kategori'])); ?>
                    </span>

                    <small>
                        <?= (int) $category['total_menu']; ?>
                    </small>
                </a>
            <?php endwhile; ?>
        </nav>
    </aside>

    <section class="menu-panel">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Choose your favorite food</p>
                <h1>Menu</h1>
            </div>

            <div class="cart-count-mobile">
                Cart: <?= $cartQuantity; ?>
            </div>
        </div>

        <div class="menu-grid">
            <?php while ($menu = mysqli_fetch_assoc($menuResult)): ?>
                <article class="menu-card">
                    <div class="menu-image-wrapper">
                        <?php if (!empty($menu['gambar'])): ?>
                            <img
                                src="../assets/images/<?= htmlspecialchars($menu['gambar']); ?>"
                                alt="<?= htmlspecialchars($menu['nama_menu']); ?>"
                            >
                        <?php else: ?>
                            <div class="image-placeholder">🍜</div>
                        <?php endif; ?>

                        <span class="category-badge">
                            <?= htmlspecialchars(categoryLabel($menu['nama_kategori'])); ?>
                        </span>
                    </div>

                    <div class="menu-card-body">
                        <h3><?= htmlspecialchars($menu['nama_menu']); ?></h3>

                        <p>
                            <?= htmlspecialchars($menu['deskripsi'] ?: 'A delicious restaurant favorite.'); ?>
                        </p>

                        <div class="menu-card-footer">
                            <strong>
                                <?= formatRupiah((float) $menu['harga']); ?>
                            </strong>

                            <form action="cart-action.php" method="POST">
                                <input name="action" type="hidden" value="add">
                                <input
                                    name="menu_id"
                                    type="hidden"
                                    value="<?= (int) $menu['id']; ?>"
                                >

                                <button class="add-button" type="submit">
                                    + Add
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>

            <?php if (mysqli_num_rows($menuResult) === 0): ?>
                <div class="empty-menu">
                    <div>🍽️</div>
                    <h3>No Menu Items Available</h3>
                    <p>Please choose another category.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <aside class="cart-panel">
        <div class="cart-header">
            <div>
                <p class="eyebrow">Your Order</p>
                <h2>Cart</h2>
            </div>

            <span class="cart-badge"><?= $cartQuantity; ?></span>
        </div>

        <div class="cart-items">
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-info">
                        <strong>
                            <?= htmlspecialchars($item['nama_menu']); ?>
                        </strong>

                        <small>
                            <?= formatRupiah((float) $item['harga']); ?>
                        </small>
                    </div>

                    <div class="quantity-control">
                        <form action="cart-action.php" method="POST">
                            <input name="action" type="hidden" value="decrease">
                            <input
                                name="menu_id"
                                type="hidden"
                                value="<?= (int) $item['id']; ?>"
                            >
                            <button type="submit">−</button>
                        </form>

                        <span><?= (int) $item['jumlah']; ?></span>

                        <form action="cart-action.php" method="POST">
                            <input name="action" type="hidden" value="increase">
                            <input
                                name="menu_id"
                                type="hidden"
                                value="<?= (int) $item['id']; ?>"
                            >
                            <button type="submit">+</button>
                        </form>
                    </div>

                    <strong class="subtotal">
                        <?= formatRupiah((float) $item['subtotal']); ?>
                    </strong>

                    <form action="cart-action.php" method="POST">
                        <input name="action" type="hidden" value="remove">
                        <input
                            name="menu_id"
                            type="hidden"
                            value="<?= (int) $item['id']; ?>"
                        >

                        <button
                            class="remove-button"
                            type="submit"
                            aria-label="Remove menu item"
                        >
                            ×
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>

            <?php if (!$cartItems): ?>
                <div class="empty-cart">
                    <div>🛒</div>
                    <h3>Your Cart Is Empty</h3>
                    <p>Tap Add on a menu item.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="cart-footer">
            <div class="total-row">
                <span>Total</span>
                <strong><?= formatRupiah($cartTotal); ?></strong>
            </div>

            <?php if ($cartItems): ?>
                <a class="checkout-button" href="checkout.php">
                    Continue to Checkout
                </a>

                <form action="cart-action.php" method="POST">
                    <input name="action" type="hidden" value="clear">

                    <button class="clear-button" type="submit">
                        Clear Cart
                    </button>
                </form>
            <?php else: ?>
                <button class="checkout-button disabled" type="button">
                    Choose an Item First
                </button>
            <?php endif; ?>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
