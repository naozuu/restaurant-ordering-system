<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$sql = "
    SELECT menu.*, categories.nama_kategori
    FROM menu
    INNER JOIN categories ON categories.id = menu.category_id
    ORDER BY menu.id DESC
";

$result = mysqli_query($conn, $sql);

$message = $_GET['message'] ?? '';
$type = $_GET['type'] ?? 'success';

$pageTitle = 'Manage Menu';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Manage Menu</h1>
        <p class="text-secondary mb-0">
            Add, edit, and remove restaurant menu items.
        </p>
    </div>

    <a class="btn btn-dark" href="tambah-menu.php">
        Add Menu Item
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($type); ?>">
        <?= htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Image</th>
                <th>Menu Item</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($menu = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>
                        <?php if ($menu['gambar']): ?>
                            <img
                                class="menu-image"
                                src="../assets/images/<?= htmlspecialchars($menu['gambar']); ?>"
                                alt="<?= htmlspecialchars($menu['nama_menu']); ?>"
                            >
                        <?php else: ?>
                            <div class="menu-image bg-secondary-subtle d-flex align-items-center justify-content-center">
                                -
                            </div>
                        <?php endif; ?>
                    </td>

                    <td>
                        <strong><?= htmlspecialchars($menu['nama_menu']); ?></strong>

                        <?php if ($menu['deskripsi']): ?>
                            <div class="small text-secondary">
                                <?= htmlspecialchars($menu['deskripsi']); ?>
                            </div>
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars(categoryLabel($menu['nama_kategori'])); ?></td>

                    <td>
                        Rp<?= number_format($menu['harga'], 0, ',', '.'); ?>
                    </td>

                    <td>
                        <span class="badge <?= $menu['status'] === 'tersedia' ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                            <?= htmlspecialchars(menuStatusLabel($menu['status'])); ?>
                        </span>
                    </td>

                    <td class="text-end">
                        <a
                            class="btn btn-sm btn-warning"
                            href="edit-menu.php?id=<?= (int) $menu['id']; ?>"
                        >
                            Edit
                        </a>

                        <a
                            class="btn btn-sm btn-danger"
                            href="hapus-menu.php?id=<?= (int) $menu['id']; ?>"
                            onclick="return confirm('Delete this menu item?')"
                        >
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

            <?php if (mysqli_num_rows($result) === 0): ?>
                <tr>
                    <td class="text-center text-secondary py-4" colspan="6">
                        There are no menu items yet.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
