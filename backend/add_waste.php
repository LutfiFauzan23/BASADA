<?php
session_start();
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = mysqli_real_escape_string($connect, $_POST['user_id']);
    $tanggal = mysqli_real_escape_string($connect, $_POST['tanggal']);
    $jenis_sampah = mysqli_real_escape_string($connect, $_POST['jenis_sampah']);
    $berat = mysqli_real_escape_string($connect, $_POST['berat']);
    $harga_per_kg = mysqli_real_escape_string($connect, $_POST['harga_per_kg']);
    $total_harga = mysqli_real_escape_string($connect, $_POST['total_harga']);
    
    $query = "INSERT INTO transaksi_sampah (user_id, tanggal, jenis_sampah, berat, harga_per_kg, total_harga) 
              VALUES ('$user_id', '$tanggal', '$jenis_sampah', '$berat', '$harga_per_kg', '$total_harga')";
    
    if (mysqli_query($connect, $query)) {
        echo json_encode(['success' => true, 'message' => 'Data sampah berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($connect)]);
    }
}
?>