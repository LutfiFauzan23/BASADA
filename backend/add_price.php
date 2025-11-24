<?php
session_start();
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_sampah = mysqli_real_escape_string($connect, $_POST['jenis_sampah']);
    $harga_per_kg = mysqli_real_escape_string($connect, $_POST['harga_per_kg']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    
    $query = "INSERT INTO harga_sampah (jenis_sampah, harga_per_kg, status) 
              VALUES ('$jenis_sampah', '$harga_per_kg', '$status')";
    
    if (mysqli_query($connect, $query)) {
        echo json_encode(['success' => true, 'message' => 'Harga berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($connect)]);
    }
}
?>