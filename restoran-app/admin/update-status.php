<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pesanan.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$statusPesanan = $_POST['status_pesanan'] ?? '';
$statusPembayaran = $_POST['status_pembayaran'] ?? '';

$allowedOrderStatuses = [
    'menunggu',
    'diproses',
    'siap',
    'selesai',
    'dibatalkan',
];

$allowedPaymentStatuses = [
    'belum_bayar',
    'sudah_bayar',
];

if (
    $id < 1 ||
    !in_array($statusPesanan, $allowedOrderStatuses, true) ||
    !in_array($statusPembayaran, $allowedPaymentStatuses, true)
) {
    header(
        'Location: pesanan.php?type=danger&message=' .
        urlencode('The status information is invalid.')
    );
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE orders
     SET status_pesanan = ?,
         status_pembayaran = ?
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    'ssi',
    $statusPesanan,
    $statusPembayaran,
    $id
);

mysqli_stmt_execute($stmt);

header(
    'Location: detail-pesanan.php?id=' . $id
);
exit;
