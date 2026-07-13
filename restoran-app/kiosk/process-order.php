<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$activeSession = getActiveRestaurantSession($conn);

if (!$activeSession) {
    $_SESSION['kiosk_cart'] = [];
    header('Location: index.php');
    exit;
}

$sessionId = (int) $activeSession['id'];
$namaPelanggan = trim($_POST['nama_pelanggan'] ?? '');
$jenisPesanan = $_POST['jenis_pesanan'] ?? 'dine_in';
$nomorMeja = trim($_POST['nomor_meja'] ?? '');
$catatan = trim($_POST['catatan'] ?? '');

if ($namaPelanggan === '') {
    header(
        'Location: checkout.php?error=' .
        urlencode('Customer name is required.')
    );
    exit;
}

if (!in_array($jenisPesanan, ['dine_in', 'takeaway'], true)) {
    $jenisPesanan = 'dine_in';
}

if ($jenisPesanan === 'dine_in' && $nomorMeja === '') {
    header(
        'Location: checkout.php?error=' .
        urlencode('Table number is required for dine-in orders.')
    );
    exit;
}

if ($jenisPesanan === 'takeaway') {
    $nomorMeja = 'TAKEAWAY';
}

$cartItems = getCartDetails($conn);

if (!$cartItems) {
    header('Location: index.php');
    exit;
}

$totalHarga = getCartTotal($cartItems);
$kodePesanan = sprintf(
    'ORD-%s-%04d',
    date('YmdHis'),
    random_int(0, 9999)
);

mysqli_begin_transaction($conn);

try {
    $sessionLockStmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM restaurant_sessions
         WHERE id = ?
           AND status = 'open'
         FOR UPDATE"
    );

    mysqli_stmt_bind_param($sessionLockStmt, 'i', $sessionId);
    mysqli_stmt_execute($sessionLockStmt);

    $lockedSession = mysqli_fetch_assoc(
        mysqli_stmt_get_result($sessionLockStmt)
    );

    if (!$lockedSession) {
        throw new RuntimeException('The restaurant has been closed.');
    }

    $orderSql = "
        INSERT INTO orders (
            session_id,
            kode_pesanan,
            nama_pelanggan,
            nomor_meja,
            total_harga,
            status_pesanan,
            status_pembayaran,
            catatan
        )
        VALUES (?, ?, ?, ?, ?, 'menunggu', 'belum_bayar', ?)
    ";

    $orderStmt = mysqli_prepare($conn, $orderSql);

    mysqli_stmt_bind_param(
        $orderStmt,
        'isssds',
        $sessionId,
        $kodePesanan,
        $namaPelanggan,
        $nomorMeja,
        $totalHarga,
        $catatan
    );

    if (!mysqli_stmt_execute($orderStmt)) {
        throw new RuntimeException(mysqli_stmt_error($orderStmt));
    }

    $orderId = mysqli_insert_id($conn);

    $detailSql = "
        INSERT INTO order_details (
            order_id,
            menu_id,
            jumlah,
            harga,
            subtotal
        )
        VALUES (?, ?, ?, ?, ?)
    ";

    $detailStmt = mysqli_prepare($conn, $detailSql);

    foreach ($cartItems as $item) {
        $menuId = (int) $item['id'];
        $jumlah = (int) $item['jumlah'];
        $harga = (float) $item['harga'];
        $subtotal = (float) $item['subtotal'];

        mysqli_stmt_bind_param(
            $detailStmt,
            'iiidd',
            $orderId,
            $menuId,
            $jumlah,
            $harga,
            $subtotal
        );

        if (!mysqli_stmt_execute($detailStmt)) {
            throw new RuntimeException(mysqli_stmt_error($detailStmt));
        }
    }

    mysqli_commit($conn);

    $_SESSION['last_kiosk_order'] = [
        'id' => $orderId,
        'kode_pesanan' => $kodePesanan,
        'nama_pelanggan' => $namaPelanggan,
        'nomor_meja' => $nomorMeja,
        'jenis_pesanan' => $jenisPesanan,
        'total_harga' => $totalHarga,
    ];

    $_SESSION['kiosk_cart'] = [];

    header('Location: success.php');
    exit;
} catch (Throwable $exception) {
    mysqli_rollback($conn);

    if ($exception->getMessage() === 'The restaurant has been closed.') {
        $_SESSION['kiosk_cart'] = [];
        header('Location: index.php');
        exit;
    }

    header(
        'Location: checkout.php?error=' .
        urlencode('The order could not be saved. Please try again.')
    );
    exit;
}
