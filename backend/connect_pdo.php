<?php
// ../backend/connect_pdo.php

// TIDAK ADA spasi atau karakter sebelum <?php
// TIDAK ADA echo, print, atau output apapun di file ini

$host = "localhost";
$username = "root";
$password = "";
$database = "bank_sampah_db";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch (PDOException $e) {
    // JANGAN echo atau print error di sini
    // Hanya simpan di error_log
    error_log("PDO Connection Error: " . $e->getMessage());
    // Biarkan error ditangani oleh file yang memanggil
    throw new Exception("Database connection failed");
}
?>