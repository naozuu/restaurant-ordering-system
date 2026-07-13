<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/operasional-functions.php';

$activeSession = getActiveRestaurantSession($conn);
$pendingOrders = $activeSession
    ? getSessionPendingOrders($conn, (int) $activeSession['id'])
    : 0;

$todaySql = "
    SELECT
        COUNT(DISTINCT restaurant_sessions.id) AS total_sessions,
        COUNT(DISTINCT orders.id) AS total_orders,
        COALESCE(
            SUM(
                CASE
                    WHEN orders.status_pembayaran = 'sudah_bayar'
                     AND orders.status_pesanan <> 'dibatalkan'
                    THEN orders.total_harga
                    ELSE 0
                END
            ),
            0
        ) AS paid_revenue
    FROM restaurant_sessions
    LEFT JOIN orders ON orders.session_id = restaurant_sessions.id
    WHERE restaurant_sessions.business_date = CURDATE()
";

$todayResult = mysqli_query($conn, $todaySql);
$today = mysqli_fetch_assoc($todayResult) ?: [
    'total_sessions' => 0,
    'total_orders' => 0,
    'paid_revenue' => 0,
];

$lastSessionResult = mysqli_query(
    $conn,
    "SELECT
        restaurant_sessions.*,
        opener.nama AS opened_by_name,
        closer.nama AS closed_by_name
     FROM restaurant_sessions
     LEFT JOIN users opener ON opener.id = restaurant_sessions.opened_by
     LEFT JOIN users closer ON closer.id = restaurant_sessions.closed_by
     ORDER BY restaurant_sessions.opened_at DESC
     LIMIT 1"
);

$lastSession = mysqli_fetch_assoc($lastSessionResult);

$message = $_GET['message'] ?? '';
$type = $_GET['type'] ?? 'success';

$pageTitle = 'Restaurant Operations';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Restaurant Operations</h1>
        <p class="text-secondary mb-0">
            Open the restaurant before accepting orders and close it when operations are finished.
        </p>
    </div>

    <a class="btn btn-outline-dark" href="history-harian.php">
        View Daily History
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($type); ?>">
        <?= htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <?php if ($activeSession): ?>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span
                            class="d-inline-block rounded-circle bg-success"
                            style="width: 18px; height: 18px;"
                        ></span>

                        <div>
                            <p class="text-secondary mb-1">Current Status</p>
                            <h2 class="h3 text-success mb-0">Restaurant Open</h2>
                        </div>
                    </div>

                    <dl class="row mb-4">
                        <dt class="col-sm-4">Business Date</dt>
                        <dd class="col-sm-8">
                            <?= date('M d, Y', strtotime($activeSession['business_date'])); ?>
                        </dd>

                        <dt class="col-sm-4">Opened At</dt>
                        <dd class="col-sm-8">
                            <?= date('H:i:s', strtotime($activeSession['opened_at'])); ?>
                        </dd>

                        <dt class="col-sm-4">Opened By</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars($activeSession['opened_by_name'] ?: '-'); ?>
                        </dd>

                        <dt class="col-sm-4">Opening Note</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars($activeSession['opening_note'] ?: '-'); ?>
                        </dd>
                    </dl>

                    <?php if ($pendingOrders > 0): ?>
                        <div class="alert alert-warning">
                            There are still <strong><?= $pendingOrders; ?> active orders</strong>.
                            Complete or cancel those orders before closing the restaurant.
                        </div>
                    <?php endif; ?>

                    <form action="proses-operasional.php" method="POST">
                        <input name="action" type="hidden" value="close">

                        <div class="mb-3">
                            <label class="form-label" for="closing_note">
                                Closing Note
                            </label>

                            <textarea
                                class="form-control"
                                id="closing_note"
                                name="note"
                                rows="3"
                                maxlength="255"
                                placeholder="Example: operations completed normally"
                            ></textarea>
                        </div>

                        <button
                            class="btn btn-danger btn-lg w-100"
                            type="submit"
                            <?= $pendingOrders > 0 ? 'disabled' : ''; ?>
                            onclick="return confirm('Close the restaurant now? The kiosk will stop accepting new orders.');"
                        >
                            Close Restaurant
                        </button>
                    </form>
                <?php else: ?>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span
                            class="d-inline-block rounded-circle bg-danger"
                            style="width: 18px; height: 18px;"
                        ></span>

                        <div>
                            <p class="text-secondary mb-1">Current Status</p>
                            <h2 class="h3 text-danger mb-0">Restaurant Closed</h2>
                        </div>
                    </div>

                    <p class="text-secondary">
                        Customers cannot place kiosk orders until the restaurant is opened.
                    </p>

                    <form action="proses-operasional.php" method="POST">
                        <input name="action" type="hidden" value="open">

                        <div class="mb-3">
                            <label class="form-label" for="opening_note">
                                Opening Note
                            </label>

                            <textarea
                                class="form-control"
                                id="opening_note"
                                name="note"
                                rows="3"
                                maxlength="255"
                                placeholder="Example: opening cash is ready and all items are available"
                            ></textarea>
                        </div>

                        <button
                            class="btn btn-success btn-lg w-100"
                            type="submit"
                            onclick="return confirm('Open the restaurant and start accepting orders?');"
                        >
                            Open Restaurant
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="row g-3">
            <div class="col-sm-4 col-xl-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Today's Sessions</p>
                        <h3 class="mb-0"><?= (int) $today['total_sessions']; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-sm-4 col-xl-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Today's Orders</p>
                        <h3 class="mb-0"><?= (int) $today['total_orders']; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-sm-4 col-xl-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Today's Paid Revenue</p>
                        <h3 class="h4 mb-0">
                            <?= formatAdminRupiah((float) $today['paid_revenue']); ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($lastSession): ?>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <strong>Latest Session</strong>
                </div>

                <div class="card-body">
                    <p class="mb-2">
                        <strong>Opened:</strong>
                        <?= date('M d, Y H:i:s', strtotime($lastSession['opened_at'])); ?>
                    </p>

                    <p class="mb-2">
                        <strong>Closed:</strong>
                        <?= $lastSession['closed_at']
                            ? date('M d, Y H:i:s', strtotime($lastSession['closed_at']))
                            : 'Still Open'; ?>
                    </p>

                    <p class="mb-0">
                        <strong>Status:</strong>
                        <?= $lastSession['status'] === 'open' ? 'Open' : 'Closed'; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
