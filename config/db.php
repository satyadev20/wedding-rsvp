<?php
$host = "localhost";
$dbname = "YOUR_DB_NAME";
$username = "YOUR_DB_USER";
$password = "YOUR_DB_PASSWORD";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
