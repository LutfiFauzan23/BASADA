<?php
session_start();
include "connect.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($connect, $_POST['id']);
    
    // Debug: Log the ID being deleted
    error_log("Attempting to delete user with ID: " . $id);
    
    // Start transaction
    mysqli_begin_transaction($connect);
    
    try {
        // 1. First, delete related waste transactions
        $deleteWasteQuery = "DELETE FROM transaksi_sampah WHERE id_anggota = '$id'";
        error_log("Deleting waste transactions: " . $deleteWasteQuery);
        
        $wasteResult = mysqli_query($connect, $deleteWasteQuery);
        if (!$wasteResult) {
            throw new Exception('Gagal menghapus data transaksi sampah: ' . mysqli_error($connect));
        }
        
        error_log("Deleted waste transactions: " . mysqli_affected_rows($connect) . " rows affected");
        
        // 2. Then delete the user
        $deleteMemberQuery = "DELETE FROM user WHERE id = '$id'";
        error_log("Deleting user: " . $deleteMemberQuery);
        
        $memberResult = mysqli_query($connect, $deleteMemberQuery);
        if (!$memberResult) {
            throw new Exception('Gagal menghapus anggota: ' . mysqli_error($connect));
        }
        
        $affectedRows = mysqli_affected_rows($connect);
        error_log("Deleted user: " . $affectedRows . " rows affected");
        
        if ($affectedRows > 0) {
            // Commit transaction
            mysqli_commit($connect);
            echo json_encode(['success' => true, 'message' => 'Anggota berhasil dihapus']);
            error_log("User deletion successful");
        } else {
            echo json_encode(['success' => false, 'message' => 'Anggota tidak ditemukan']);
            error_log("User not found with ID: " . $id);
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($connect);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        error_log("Error deleting user: " . $e->getMessage());
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>