<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/operasional-functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: operasional.php');
    exit;
}

$action = $_POST['action'] ?? '';
$note = trim($_POST['note'] ?? '');
$note = mb_substr($note, 0, 255);
$adminId = (int) $_SESSION['admin_id'];

$activeSession = getActiveRestaurantSession($conn);

if ($action === 'open') {
    if ($activeSession) {
        header(
            'Location: operasional.php?type=warning&message=' .
            urlencode('The restaurant is already open.')
        );
        exit;
    }

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO restaurant_sessions (
            business_date,
            opened_at,
            status,
            opened_by,
            opening_note
         )
         VALUES (CURDATE(), NOW(), 'open', ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, 'is', $adminId, $note);

    if (!mysqli_stmt_execute($stmt)) {
        header(
            'Location: operasional.php?type=danger&message=' .
            urlencode('The restaurant could not be opened: ' . mysqli_stmt_error($stmt))
        );
        exit;
    }

    header(
        'Location: operasional.php?type=success&message=' .
        urlencode('The restaurant is now open. The kiosk is accepting orders.')
    );
    exit;
}

if ($action === 'close') {
    if (!$activeSession) {
        header(
            'Location: operasional.php?type=warning&message=' .
            urlencode('The restaurant is already closed.')
        );
        exit;
    }

    $sessionId = (int) $activeSession['id'];
    $pendingOrders = getSessionPendingOrders($conn, $sessionId);

    if ($pendingOrders > 0) {
        header(
            'Location: operasional.php?type=warning&message=' .
            urlencode(
                "The restaurant cannot be closed because there are still {$pendingOrders} active orders."
            )
        );
        exit;
    }

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE restaurant_sessions
         SET status = 'closed',
             closed_at = NOW(),
             closed_by = ?,
             closing_note = ?
         WHERE id = ?
           AND status = 'open'"
    );

    mysqli_stmt_bind_param($stmt, 'isi', $adminId, $note, $sessionId);

    if (!mysqli_stmt_execute($stmt)) {
        header(
            'Location: operasional.php?type=danger&message=' .
            urlencode('The restaurant could not be closed: ' . mysqli_stmt_error($stmt))
        );
        exit;
    }

    header(
        'Location: operasional.php?type=success&message=' .
        urlencode('The restaurant was closed successfully. The kiosk has stopped accepting orders.')
    );
    exit;
}

header(
    'Location: operasional.php?type=danger&message=' .
    urlencode('The restaurant operation action is invalid.')
);
exit;
