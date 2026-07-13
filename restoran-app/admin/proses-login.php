<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: login.php?error=' . urlencode('Username and password are required.'));
    exit;
}

$sql = "
    SELECT id, nama, username, password, role
    FROM users
    WHERE username = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user || !password_verify($password, $user['password'])) {
    header('Location: login.php?error=' . urlencode('Incorrect username or password.'));
    exit;
}

session_regenerate_id(true);

$_SESSION['admin_id'] = (int) $user['id'];
$_SESSION['admin_nama'] = $user['nama'];
$_SESSION['admin_username'] = $user['username'];
$_SESSION['admin_role'] = $user['role'];

header('Location: index.php');
exit;
