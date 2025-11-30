<?php
session_start();
include "../connect.php";

// Cek apakah user adalah admin
$admin_emails = ['basada964@gmail.com', 'admin@basada.com', 'lutpifauzan23@gmail.com'];
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !in_array($_SESSION['alamat_email'], $admin_emails)) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $action = $_POST['action']; // 'approve' atau 'reject'
    
    // Ambil data transaksi sampah
    $query = "SELECT ts.*, u.id as user_id, u.saldo, ts.total as total_harga 
              FROM transaksi_sampah ts 
              JOIN user u ON ts.id_anggota = u.id 
              WHERE ts.id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $transaction = mysqli_fetch_assoc($result);
    
    if(!$transaction) {
        echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan']);
        exit;
    }
    
    if($action == 'approve') {
        // Update status transaksi menjadi approved
        $updateQuery = "UPDATE transaksi_sampah SET status = 'approved' WHERE id = ?";
        $updateStmt = mysqli_prepare($connect, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, 'i', $id);
        
        if(mysqli_stmt_execute($updateStmt)) {
            // Tambahkan saldo ke user
            $newBalance = $transaction['saldo'] + $transaction['total_harga'];
            $balanceQuery = "UPDATE user SET saldo = ? WHERE id = ?";
            $balanceStmt = mysqli_prepare($connect, $balanceQuery);
            mysqli_stmt_bind_param($balanceStmt, 'di', $newBalance, $transaction['user_id']);
            
            if(mysqli_stmt_execute($balanceStmt)) {
                // Catat dalam riwayat transaksi (opsional)
                $historyQuery = "INSERT INTO riwayat_transaksi (id_user, jenis, jumlah, keterangan, tanggal) 
                                VALUES (?, 'penambahan_saldo', ?, 'Penjualan sampah disetujui', NOW())";
                $historyStmt = mysqli_prepare($connect, $historyQuery);
                mysqli_stmt_bind_param($historyStmt, 'id', $transaction['user_id'], $transaction['total_harga']);
                mysqli_stmt_execute($historyStmt);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Penjualan sampah disetujui dan saldo user berhasil ditambahkan',
                    'saldo_baru' => $newBalance
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menambah saldo user']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyetujui transaksi']);
        }
    } 
    elseif($action == 'reject') {
        // Update status transaksi menjadi rejected
        $updateQuery = "UPDATE transaksi_sampah SET status = 'rejected' WHERE id = ?";
        $updateStmt = mysqli_prepare($connect, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, 'i', $id);
        
        if(mysqli_stmt_execute($updateStmt)) {
            echo json_encode(['success' => true, 'message' => 'Penjualan sampah ditolak']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menolak transaksi']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>