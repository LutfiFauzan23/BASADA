<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

session_start();

// Cek session
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Silakan login kembali']);
    exit;
}

// Cek apakah admin
$admin_emails = ['basada964@gmail.com', 'admin@basada.com', 'lutpifauzan23@gmail.com'];
if(!isset($_SESSION['alamat_email']) || !in_array($_SESSION['alamat_email'], $admin_emails)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Bukan admin']);
    exit;
}

// Cek parameter
if(!isset($_POST['id']) || !isset($_POST['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
    exit;
}

$id = intval($_POST['id']);
$action = $_POST['action']; // 'approve' atau 'reject'

if($id <= 0 || !in_array($action, ['approve', 'reject'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter tidak valid']);
    exit;
}

// Include koneksi database
$connect = null;
try {
    // Coba beberapa path untuk connect.php
    $possiblePaths = [
        __DIR__ . '/../connect.php',
        __DIR__ . '/../../backend/connect.php',
        __DIR__ . '/../../../backend/connect.php'
    ];
    
    foreach($possiblePaths as $path) {
        if(file_exists($path)) {
            require_once $path;
            break;
        }
    }
    
    if(!$connect) {
        throw new Exception("Tidak dapat menemukan file koneksi database");
    }
    
    // Mulai transaksi
    $connect->begin_transaction();
    
    if($action === 'approve') {
        // 1. Ambil data transaksi
        $stmt = $connect->prepare("
            SELECT ts.*, u.id as user_id, u.nama, ts.total as transaction_total 
            FROM transaksi_sampah ts 
            JOIN user u ON ts.id_anggota = u.id 
            WHERE ts.id = ? AND ts.status = 'pending'
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows === 0) {
            throw new Exception('Transaksi tidak ditemukan atau sudah diproses');
        }
        
        $transaction = $result->fetch_assoc();
        $user_id = $transaction['user_id'];
        $transaction_total = $transaction['total'];
        $user_name = $transaction['nama'];
        
        // 2. Update saldo user
        $updateSaldo = $connect->prepare("
            UPDATE user 
            SET saldo = saldo + ? 
            WHERE id = ?
        ");
        $updateSaldo->bind_param("di", $transaction_total, $user_id);
        if(!$updateSaldo->execute()) {
            throw new Exception('Gagal mengupdate saldo user: ' . $connect->error);
        }
        
        // 3. Update status transaksi
        $updateTransaksi = $connect->prepare("
            UPDATE transaksi_sampah 
            SET status = 'approved', 
                tanggal_disetujui = NOW() 
            WHERE id = ?
        ");
        $updateTransaksi->bind_param("i", $id);
        if(!$updateTransaksi->execute()) {
            throw new Exception('Gagal mengupdate status transaksi: ' . $connect->error);
        }
        
        // 4. Simpan riwayat saldo (opsional)
        $insertHistory = $connect->prepare("
            INSERT INTO riwayat_saldo 
            (user_id, jenis, jumlah, keterangan, created_at) 
            VALUES (?, 'penjualan', ?, ?, NOW())
        ");
        $keterangan = "Penjualan sampah disetujui #" . $id;
        $insertHistory->bind_param("ids", $user_id, $transaction_total, $keterangan);
        $insertHistory->execute();
        
        $message = "Penjualan dari " . $user_name . " disetujui. Saldo bertambah Rp " . number_format($transaction_total);
        
    } elseif($action === 'reject') {
        // Ambil data untuk notifikasi
        $stmt = $connect->prepare("
            SELECT u.nama 
            FROM transaksi_sampah ts 
            JOIN user u ON ts.id_anggota = u.id 
            WHERE ts.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $user_name = $data['nama'];
        
        // Update status transaksi menjadi rejected
        $updateTransaksi = $connect->prepare("
            UPDATE transaksi_sampah 
            SET status = 'rejected', 
                tanggal_disetujui = NOW() 
            WHERE id = ?
        ");
        $updateTransaksi->bind_param("i", $id);
        if(!$updateTransaksi->execute()) {
            throw new Exception('Gagal menolak transaksi: ' . $connect->error);
        }
        
        $message = "Penjualan dari " . $user_name . " ditolak";
    }
    
    // Commit transaksi
    $connect->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    // Rollback jika ada error
    if($connect && $connect instanceof mysqli) {
        $connect->rollback();
    }
    
    error_log("Error approve/reject waste: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>