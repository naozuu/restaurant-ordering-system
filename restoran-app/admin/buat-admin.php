<?php

require_once __DIR__ . '/../config/database.php';

$message = '';
$type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nama === '' || $username === '' || strlen($password) < 6) {
        $message = 'Name and username are required, and the password must contain at least 6 characters.';
        $type = 'danger';
    } else {
        $checkStmt = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE username = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($checkStmt, 's', $username);
        mysqli_stmt_execute($checkStmt);

        $existingUser = mysqli_fetch_assoc(
            mysqli_stmt_get_result($checkStmt)
        );

        if ($existingUser) {
            $message = 'The username is already in use.';
            $type = 'danger';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin';

            $insertStmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (nama, username, password, role)
                 VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $insertStmt,
                'ssss',
                $nama,
                $username,
                $passwordHash,
                $role
            );

            mysqli_stmt_execute($insertStmt);

            $message = 'The administrator account was created successfully. Delete buat-admin.php when finished.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Administrator</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-sm-10 col-md-6 col-lg-4">
            <div class="card border-0 shadow">
                <div class="card-body p-4">
                    <h1 class="h3 mb-3">Create Administrator Account</h1>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $type; ?>">
                            <?= htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label" for="nama">Name</label>
                            <input class="form-control" id="nama" name="nama" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="username">
                                Username
                            </label>
                            <input class="form-control" id="username" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">
                                Password
                            </label>
                            <input
                                class="form-control"
                                id="password"
                                name="password"
                                type="password"
                                minlength="6"
                                required
                            >
                        </div>

                        <button class="btn btn-dark w-100" type="submit">
                            Create Administrator
                        </button>
                    </form>

                    <a class="btn btn-link w-100 mt-2" href="login.php">
                        Go to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
