<?php

session_start();

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login</title>

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
                    <h1 class="h3 text-center mb-2">Admin Login</h1>

                    <p class="text-secondary text-center mb-4">
                        Sign in to manage the restaurant.
                    </p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="proses-login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label" for="username">
                                Username
                            </label>

                            <input
                                class="form-control"
                                id="username"
                                name="username"
                                type="text"
                                required
                                autofocus
                            >
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
                                required
                            >
                        </div>

                        <button class="btn btn-dark w-100" type="submit">
                            Sign In
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
