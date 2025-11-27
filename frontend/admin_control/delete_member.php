<?php
session_start();

// Enable error reporting but don't output to screen
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set header pertama kali
header('Content-Type: application/json; charset=utf-8');

// Include koneksi database
include "../backend/connect.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

// Check if user is admin
$admin_emails = ['basada964@gmail.com', 'admin@basada.com', 'lutpifauzan23@gmail.com'];
if (!in_array($_SESSION['alamat_email'], $admin_emails)) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya admin yang bisa menghapus anggota.']);
    exit;
}

// Check if it's POST request and ID is provided
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Request tidak valid']);
    exit;
}

// Validate and sanitize input
$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
if ($id === false || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

try {
    // Check database connection
    if (!$connect) {
        throw new Exception('Koneksi database gagal');
    }

    // Start transaction
    mysqli_begin_transaction($connect);

    // 1. First check if user exists
    $check_query = "SELECT id, nama FROM user WHERE id = ?";
    $check_stmt = mysqli_prepare($connect, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) == 0) {
        mysqli_stmt_close($check_stmt);
        mysqli_rollback($connect);
        echo json_encode(['success' => false, 'message' => 'Anggota tidak ditemukan']);
        exit;
    }
    
    $user_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($check_stmt);

    // 2. Delete related waste transactions
    $delete_waste_query = "DELETE FROM transaksi_sampah WHERE id_anggota = ?";
    $waste_stmt = mysqli_prepare($connect, $delete_waste_query);
    mysqli_stmt_bind_param($waste_stmt, "i", $id);
    
    if (!mysqli_stmt_execute($waste_stmt)) {
        throw new Exception('Gagal menghapus data transaksi sampah: ' . mysqli_error($connect));
    }
    mysqli_stmt_close($waste_stmt);

    // 3. Delete the user
    $delete_user_query = "DELETE FROM user WHERE id = ?";
    $user_stmt = mysqli_prepare($connect, $delete_user_query);
    mysqli_stmt_bind_param($user_stmt, "i", $id);
    
    if (!mysqli_stmt_execute($user_stmt)) {
        throw new Exception('Gagal menghapus anggota: ' . mysqli_error($connect));
    }
    
    $affected_rows = mysqli_affected_rows($connect);
    mysqli_stmt_close($user_stmt);

    if ($affected_rows > 0) {
        // Commit transaction
        mysqli_commit($connect);
        echo json_encode([
            'success' => true, 
            'message' => 'Anggota ' . $user_data['nama'] . ' berhasil dihapus'
        ]);
    } else {
        mysqli_rollback($connect);
        echo json_encode(['success' => false, 'message' => 'Tidak ada anggota yang terhapus']);
    }

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($connect)) {
        mysqli_rollback($connect);
    }
    
    error_log("Delete member error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
    ]);
}

// Close connection
if (isset($connect)) {
    mysqli_close($connect);
}
?>