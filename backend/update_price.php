<?php
session_start();
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($connect, $_POST['id']);
    $jenis_sampah = mysqli_real_escape_string($connect, $_POST['jenis_sampah']);
    $harga_per_kg = mysqli_real_escape_string($connect, $_POST['harga_per_kg']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    
    $query = "UPDATE harga_sampah SET 
              jenis_sampah = '$jenis_sampah', 
              harga_per_kg = '$harga_per_kg', 
              status = '$status' 
              WHERE id = '$id'";
    
    if (mysqli_query($connect, $query)) {
        echo json_encode(['success' => true, 'message' => 'Harga berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($connect)]);
    }
}
?>