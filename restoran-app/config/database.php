<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_restoran';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die('Database Connection Failure: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
