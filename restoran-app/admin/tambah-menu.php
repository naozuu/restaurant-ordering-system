<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/upload-gambar.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $namaMenu = trim($_POST['nama_menu'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga = (float) ($_POST['harga'] ?? 0);
    $status = $_POST['status'] ?? 'tersedia';

    $statusTersedia = ['tersedia', 'habis'];

    if (
        $categoryId < 1 ||
        $namaMenu === '' ||
        $harga <= 0 ||
        !in_array($status, $statusTersedia, true)
    ) {
        $error = 'The menu information is incomplete or invalid.';
    } else {
        try {
            $gambar = uploadImage($_FILES['gambar'] ?? []);

            $sql = "
                INSERT INTO menu (
                    category_id,
                    nama_menu,
                    deskripsi,
                    harga,
                    gambar,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param(
                $stmt,
                'issdss',
                $categoryId,
                $namaMenu,
                $deskripsi,
                $harga,
                $gambar,
                $status
            );

            mysqli_stmt_execute($stmt);

            header(
                'Location: menu.php?type=success&message=' .
                urlencode('The menu item was added successfully.')
            );
            exit;
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
}

$categories = mysqli_query(
    $conn,
    "SELECT id, nama_kategori FROM categories ORDER BY nama_kategori ASC"
);

$pageTitle = 'Add Menu Item';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<div class="mb-4">
    <h1 class="h2 mb-1">Add Menu Item</h1>
    <p class="text-secondary mb-0">
        Enter the new menu item information.
    </p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="nama_menu">
                        Menu Item Name
                    </label>

                    <input
                        class="form-control"
                        id="nama_menu"
                        name="nama_menu"
                        type="text"
                        value="<?= htmlspecialchars($_POST['nama_menu'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="category_id">
                        Category
                    </label>

                    <select
                        class="form-select"
                        id="category_id"
                        name="category_id"
                        required
                    >
                        <option value="">Select a category</option>

                        <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                            <option
                                value="<?= (int) $category['id']; ?>"
                                <?= (int) ($_POST['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : ''; ?>
                            >
                                <?= htmlspecialchars(categoryLabel($category['nama_kategori'])); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="harga">
                        Price
                    </label>

                    <input
                        class="form-control"
                        id="harga"
                        name="harga"
                        type="number"
                        min="1"
                        step="1"
                        value="<?= htmlspecialchars($_POST['harga'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="status">
                        Status
                    </label>

                    <select class="form-select" id="status" name="status">
                        <option value="tersedia">Available</option>
                        <option value="habis">Sold Out</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label" for="deskripsi">
                        Description
                    </label>

                    <textarea
                        class="form-control"
                        id="deskripsi"
                        name="deskripsi"
                        rows="4"
                    ><?= htmlspecialchars($_POST['deskripsi'] ?? ''); ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label" for="gambar">
                        Image
                    </label>

                    <input
                        class="form-control"
                        id="gambar"
                        name="gambar"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <div class="form-text">
                        JPG, PNG, or WEBP format. Maximum size: 2 MB.
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-dark" type="submit">
                    Save
                </button>

                <a class="btn btn-outline-secondary" href="menu.php">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
