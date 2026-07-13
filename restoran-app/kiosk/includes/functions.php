<?php

function formatRupiah(float $amount): string
{
    return 'Rp' . number_format($amount, 0, ',', '.');
}

function getActiveRestaurantSession(mysqli $conn): ?array
{
    $result = mysqli_query(
        $conn,
        "SELECT *
         FROM restaurant_sessions
         WHERE status = 'open'
         ORDER BY opened_at DESC
         LIMIT 1"
    );

    $session = mysqli_fetch_assoc($result);

    return $session ?: null;
}

function getCart(): array
{
    return $_SESSION['kiosk_cart'] ?? [];
}

function getCartDetails(mysqli $conn): array
{
    $cart = getCart();

    if (!$cart) {
        return [];
    }

    $ids = array_values(
        array_filter(
            array_map('intval', array_keys($cart)),
            static fn (int $id): bool => $id > 0
        )
    );

    if (!$ids) {
        return [];
    }

    $idList = implode(',', $ids);

    $sql = "
        SELECT
            menu.id,
            menu.nama_menu,
            menu.harga,
            menu.gambar,
            menu.status,
            categories.nama_kategori
        FROM menu
        INNER JOIN categories ON categories.id = menu.category_id
        WHERE menu.id IN ($idList)
          AND menu.status = 'tersedia'
        ORDER BY menu.nama_menu ASC
    ";

    $result = mysqli_query($conn, $sql);
    $items = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $id = (int) $row['id'];
        $quantity = max(1, (int) ($cart[$id] ?? 1));
        $price = (float) $row['harga'];

        $row['jumlah'] = $quantity;
        $row['subtotal'] = $price * $quantity;

        $items[] = $row;
    }

    return $items;
}

function getCartTotal(array $items): float
{
    return array_reduce(
        $items,
        static fn (float $total, array $item): float =>
            $total + (float) $item['subtotal'],
        0.0
    );
}

function getCartQuantity(array $cart): int
{
    return array_sum(array_map('intval', $cart));
}


function categoryLabel(string $category): string
{
    return [
        'Makanan' => 'Food',
        'Minuman' => 'Drinks',
        'Dessert' => 'Dessert',
    ][$category] ?? $category;
}
