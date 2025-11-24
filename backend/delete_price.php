<?php
session_start();
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($connect, $_POST['id']);
    
    $query = "DELETE FROM harga_sampah WHERE id = '$id'";
    
    if (mysqli_query($connect, $query)) {
        echo json_encode(['success' => true, 'message' => 'Harga berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($connect)]);
    }
}
?>