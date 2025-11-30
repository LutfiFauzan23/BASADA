<?php
session_start();
include "connect.php";

// Cek apakah user adalah admin
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Definisikan email admin
$admin_emails = ['basada964@gmail.com', 'admin@basada.com', 'lutpifauzan23@gmail.com'];

// Cek apakah user adalah admin berdasarkan email
if(!in_array($_SESSION['alamat_email'], $admin_emails)) {
    echo json_encode(['success' => false, 'message' => 'Anda bukan admin']);
    exit;
}

// Cek metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Ambil data dari POST
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID anggota tidak valid']);
    exit;
}

try {
    // Mulai transaction
    mysqli_begin_transaction($connect);
    
    // 1. Hapus data transaksi sampah yang terkait dengan anggota ini
    $delete_transactions = mysqli_prepare($connect, "DELETE FROM transaksi_sampah WHERE id_anggota = ?");
    mysqli_stmt_bind_param($delete_transactions, "i", $id);
    mysqli_stmt_execute($delete_transactions);
    
    // 2. Hapus anggota
    $delete_member = mysqli_prepare($connect, "DELETE FROM user WHERE id = ?");
    mysqli_stmt_bind_param($delete_member, "i", $id);
    $result = mysqli_stmt_execute($delete_member);
    
    if ($result) {
        // Commit transaction jika semua berhasil
        mysqli_commit($connect);
        echo json_encode(['success' => true, 'message' => 'Anggota berhasil dihapus']);
    } else {
        // Rollback jika ada error
        mysqli_rollback($connect);
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus anggota: ' . mysqli_error($connect)]);
    }
    
    mysqli_stmt_close($delete_transactions);
    mysqli_stmt_close($delete_member);
    
} catch (Exception $e) {
    // Rollback jika ada exception
    mysqli_rollback($connect);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

mysqli_close($connect);
?>