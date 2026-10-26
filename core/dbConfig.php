<?php
// PDO connection (XAMPP defaults)
$host = "localhost";
$dbname = "gym_db";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbMessage = "DB Connection to $dbname successful!";
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
