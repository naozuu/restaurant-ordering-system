<?php

function getActiveRestaurantSession(mysqli $conn): ?array
{
    $sql = "
        SELECT
            restaurant_sessions.*,
            users.nama AS opened_by_name
        FROM restaurant_sessions
        LEFT JOIN users ON users.id = restaurant_sessions.opened_by
        WHERE restaurant_sessions.status = 'open'
        ORDER BY restaurant_sessions.opened_at DESC
        LIMIT 1
    ";

    $result = mysqli_query($conn, $sql);
    $session = mysqli_fetch_assoc($result);

    return $session ?: null;
}

function getSessionPendingOrders(mysqli $conn, int $sessionId): int
{
    $sql = "
        SELECT COUNT(*)
        FROM orders
        WHERE session_id = ?
          AND status_pesanan IN ('menunggu', 'diproses', 'siap')
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $sessionId);
    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_row(mysqli_stmt_get_result($stmt));

    return (int) ($row[0] ?? 0);
}

function formatAdminRupiah(float $amount): string
{
    return 'Rp' . number_format($amount, 0, ',', '.');
}
