<?php

$host = 'localhost';
$username = 'YOUR_USERNAME';
$password = 'YOUR_PASSWORD';
$database = 'YOUR_DATABASE';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die('Database Connection Failure: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
