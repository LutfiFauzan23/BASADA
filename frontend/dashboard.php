<?php
session_start();
include '../backend/connect.php';

// Cek apakah user sudah login
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../backend/login.php');
    exit;
}

// Ambil data user dari session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nama'];
$user_email = $_SESSION['alamat_email'];

// Ambil data lengkap user dari database
$user_query = mysqli_prepare($connect, "SELECT nama, email, no_hp, alamat FROM user WHERE id = ?");
mysqli_stmt_bind_param($user_query, "i", $user_id);
mysqli_stmt_execute($user_query);
mysqli_stmt_bind_result($user_query, $nama_lengkap, $alamat_email, $nomor_telepon, $alamat);
mysqli_stmt_fetch($user_query);
mysqli_stmt_close($user_query);

// Ambil statistik user dari transaksi_sampah
$stats_query = mysqli_prepare($connect, "
    SELECT 
        COALESCE(SUM(berat), 0) as berat,
        COALESCE(SUM(total), 0) as total_nilai,
        COUNT(*) as total,
        COALESCE(SUM(total_poin), 0) as total_poin
    FROM transaksi_sampah
    WHERE id_anggota = ?
");
mysqli_stmt_bind_param($stats_query, "i", $user_id);
mysqli_stmt_execute($stats_query);
mysqli_stmt_bind_result($stats_query, $total_berat, $total_nilai, $total_transaksi, $total_poin_history);
mysqli_stmt_fetch($stats_query);
mysqli_stmt_close($stats_query);

// Handle Form Submit Jadwal Penjemputan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_pickup'])) {
    $jenis_sampah = mysqli_real_escape_string($connect, $_POST['jenis_sampah']);
    $alamat_jemput = mysqli_real_escape_string($connect, $_POST['alamat_jemput']);
    $catatan = mysqli_real_escape_string($connect, $_POST['catatan']);
    
    $foto_sampah = NULL;
    
    // Handle file upload
    if (isset($_FILES['foto_sampah']) && $_FILES['foto_sampah']['error'] === 0) {
        $uploadDir = '../uploads/jadwal_penjemputan/';
        
        // Buat folder jika belum ada
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($_FILES['foto_sampah']['name'], PATHINFO_EXTENSION);
        $fileName = 'foto_' . $user_id . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        // Validasi file (hanya gambar)
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        if (in_array(strtolower($fileExtension), $allowedTypes)) {
            if ($_FILES['foto_sampah']['size'] <= $maxFileSize) {
                if (move_uploaded_file($_FILES['foto_sampah']['tmp_name'], $filePath)) {
                    $foto_sampah = $fileName;
                } else {
                    $upload_error = "Gagal mengupload file.";
                }
            } else {
                $upload_error = "Ukuran file terlalu besar (maksimal 5MB).";
            }
        } else {
            $upload_error = "Hanya file gambar (JPG, PNG, GIF) yang diizinkan.";
        }
    }
    
    // Insert ke database
    $insert_query = mysqli_prepare($connect, "
        INSERT INTO jadwal_penjemputan (id_user, jenis_sampah, alamat_jemput, foto_sampah, catatan, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    mysqli_stmt_bind_param($insert_query, "issss", $user_id, $jenis_sampah, $alamat_jemput, $foto_sampah, $catatan);
    
    if (mysqli_stmt_execute($insert_query)) {
        $success_message = "Jadwal penjemputan berhasil diajukan!";
        // Reset form values setelah sukses
        $_POST = array();
    } else {
        $error_message = "Gagal mengajukan jadwal penjemputan: " . mysqli_error($connect);
    }
    
    mysqli_stmt_close($insert_query);
}

// Ambil data reward dari database
$reward_query = mysqli_prepare($connect, "
    SELECT id, nama_reward, deskripsi, poin_dibutuhkan, stok, gambar, kategori 
    FROM reward 
    WHERE status = 'active' AND stok > 0
    ORDER BY poin_dibutuhkan ASC
");
mysqli_stmt_execute($reward_query);
$reward_result = mysqli_stmt_get_result($reward_query);
$reward_data = [];
while($row = mysqli_fetch_assoc($reward_result)) {
    $reward_data[] = $row;
}
mysqli_stmt_close($reward_query);

// Handle Redeem Reward
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_reward'])) {
    $reward_id = mysqli_real_escape_string($connect, $_POST['reward_id']);
    
    // Ambil data reward
    $reward_detail_query = mysqli_prepare($connect, "
        SELECT nama_reward, poin_dibutuhkan, stok 
        FROM reward 
        WHERE id = ? AND status = 'active'
    ");
    mysqli_stmt_bind_param($reward_detail_query, "i", $reward_id);
    mysqli_stmt_execute($reward_detail_query);
    mysqli_stmt_bind_result($reward_detail_query, $reward_name, $poin_dibutuhkan, $stok);
    mysqli_stmt_fetch($reward_detail_query);
    mysqli_stmt_close($reward_detail_query);
    
    // Cek apakah poin mencukupi dan stok tersedia
    if ($total_poin_history >= $poin_dibutuhkan && $stok > 0) {
        // Mulai transaction
        mysqli_begin_transaction($connect);
        
        try {
            // Kurangi poin user di transaksi_sampah
            $insert_transaksi = mysqli_prepare($connect, "
                INSERT INTO transaksi_sampah (id_anggota, jenis_sampah, berat, harga_per_kg, total, status, total_poin, catatan) 
                VALUES (?, 'Penukaran Reward', 0, 0, 0, 'berhasil', ?, ?)
            ");
            $poin_negative = -$poin_dibutuhkan;
            $keterangan = "Penukaran reward: " . $reward_name;
            mysqli_stmt_bind_param($insert_transaksi, "iis", $user_id, $poin_negative, $keterangan);
            mysqli_stmt_execute($insert_transaksi);
            mysqli_stmt_close($insert_transaksi);
            
            // Kurangi stok reward
            $update_reward = mysqli_prepare($connect, "
                UPDATE reward SET stok = stok - 1 WHERE id = ?
            ");
            mysqli_stmt_bind_param($update_reward, "i", $reward_id);
            mysqli_stmt_execute($update_reward);
            mysqli_stmt_close($update_reward);
            
            // Commit transaction
            mysqli_commit($connect);
            
            $redeem_success = "Reward berhasil ditukar! Poin Anda telah dikurangi.";
            
            // Refresh poin history
            $stats_query = mysqli_prepare($connect, "
                SELECT COALESCE(SUM(total_poin), 0) AS total_poin
                FROM transaksi_sampah
                WHERE id_anggota = ?
            ");
            mysqli_stmt_bind_param($stats_query, "i", $user_id);
            mysqli_stmt_execute($stats_query);
            mysqli_stmt_bind_result($stats_query, $total_poin_history);
            mysqli_stmt_fetch($stats_query);
            mysqli_stmt_close($stats_query);
            
        } catch (Exception $e) {
            mysqli_rollback($connect);
            $redeem_error = "Gagal menukar reward: " . $e->getMessage();
        }
    } else {
        if ($total_poin_history < $poin_dibutuhkan) {
            $redeem_error = "Poin tidak mencukupi untuk menukar reward ini.";
        } else {
            $redeem_error = "Stok reward habis.";
        }
    }
}

// Ambil riwayat jadwal penjemputan user
$jadwal_query = mysqli_prepare($connect, "
    SELECT jenis_sampah, alamat_jemput, foto_sampah, catatan, status, tanggal_jemput, waktu_jemput, created_at 
    FROM jadwal_penjemputan 
    WHERE id_user = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
mysqli_stmt_bind_param($jadwal_query, "i", $user_id);
mysqli_stmt_execute($jadwal_query);
$jadwal_result = mysqli_stmt_get_result($jadwal_query);
$jadwal_data = [];
while($row = mysqli_fetch_assoc($jadwal_result)) {
    $jadwal_data[] = $row;
}
mysqli_stmt_close($jadwal_query);

// Ambil transaksi terbaru user
$transaksi_query = mysqli_prepare($connect, "
    SELECT tanggal, jenis_sampah, berat, status, total_poin, total
    FROM transaksi_sampah
    WHERE id_anggota = ? 
    ORDER BY tanggal DESC 
    LIMIT 5
");
mysqli_stmt_bind_param($transaksi_query, "i", $user_id);
mysqli_stmt_execute($transaksi_query);
$transaksi_result = mysqli_stmt_get_result($transaksi_query);
$transaksi_data = [];
while($row = mysqli_fetch_assoc($transaksi_result)) {
    $transaksi_data[] = $row;
}
// mysqli_stmt_close($transaksi_query);

// // Format currency
// function format_currency($number) {
//     return 'Rp ' . number_format($number, 0, ',', '.');
// }

// // Get initials for avatar
// function get_initials($name) {
//     $names = explode(' ', $name);
//     $initials = '';
//     foreach($names as $name) {
//         $initials .= strtoupper(substr($name, 0, 1));
//     }
//     return substr($initials, 0, 2);
// }
// ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Bank Sampah Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2e7d32;
            --primary-light: #4caf50;
            --primary-dark: #1b5e20;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --gray: #e9ecef;
            --dark-gray: #6c757d;
            --text: #212529;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --radius: 8px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-gray);
            color: var(--text);
            line-height: 1.6;
        }

        /* Header & Navbar - FIXED */
        .header {
            background-color: var(--white);
            box-shadow: var(--shadow);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: var(--transition);
        }

        .header.scrolled {
            padding: 5px 0;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 5%;
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo h1 {
            font-size: 1.3rem;
            color: var(--primary);
        }

        .logo span {
            color: var(--primary-light);
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            font-size: 0.9rem;
            padding: 8px 12px;
            border-radius: 6px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-links a:hover, .nav-links a.active {
            background-color: rgba(46, 125, 50, 0.1);
            color: var(--primary);
        }

        .nav-links a i {
            font-size: 1rem;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: var(--primary);
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.8rem;
            color: var(--dark-gray);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: var(--primary);
            cursor: pointer;
            padding: 5px;
        }

        /* Main Content */
        .main-content {
            margin-top: 80px;
            padding: 20px 5%;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }

        .page-title {
            margin-bottom: 25px;
            color: var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h2 {
            font-size: 1.5rem;
        }

        /* Dashboard Content */
        .dashboard-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px;
            text-align: center;
            border-top: 4px solid var(--primary);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary);
            margin: 8px 0;
        }

        .stat-label {
            color: var(--dark-gray);
            font-size: 0.85rem;
        }

        .stat-icon {
            font-size: 2rem;
            color: var(--primary-light);
            margin-bottom: 8px;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            border-radius: var(--radius);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray);
            font-size: 0.9rem;
        }

        th {
            background-color: rgba(46, 125, 50, 0.08);
            color: var(--primary);
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background-color: rgba(46, 125, 50, 0.03);
        }

        /* Button Styles */
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: rgba(46, 125, 50, 0.1);
            transform: translateY(-2px);
        }

        /* Button Groups */
        .btn-group {
            display: flex;
            gap: 8px;
        }

        .btn-group .btn {
            margin: 0;
        }

        /* Badge Styles */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: #e8f5e9;
            color: var(--primary);
        }

        .badge-warning {
            background-color: #fff8e1;
            color: #ff8f00;
        }

        .badge-danger {
            background-color: #ffebee;
            color: #f44336;
        }

        /* Tabs */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Section Title */
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .section-title h3 {
            color: var(--primary);
            font-size: 1.2rem;
        }

        /* Progress Bar */
        .progress-container {
            margin-top: 10px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .progress-bar {
            height: 10px;
            background-color: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background-color: var(--primary);
            border-radius: 5px;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .action-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid var(--gray);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 20px;
            color: var(--primary);
        }

        .action-card h3 {
            font-size: 16px;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .action-card p {
            font-size: 13px;
            color: #777;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(46, 125, 50, 0.2);
        }

        /* Profile Styles */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
        }

        .profile-info h2 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .profile-info p {
            color: #777;
        }

        /* Auto Update Indicator */
        .auto-update-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: var(--dark-gray);
            margin-top: 10px;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: var(--primary-light);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* File Upload Styles */
        .file-upload {
            border: 2px dashed var(--gray);
            border-radius: var(--radius);
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 15px;
        }

        .file-upload:hover {
            border-color: var(--primary);
            background-color: rgba(46, 125, 50, 0.05);
        }

        .file-upload i {
            font-size: 3rem;
            color: var(--primary-light);
            margin-bottom: 15px;
        }

        .file-upload p {
            margin-bottom: 10px;
            color: var(--dark-gray);
        }

        .file-upload small {
            color: var(--dark-gray);
        }

        .file-preview {
            margin-top: 15px;
            display: none;
            text-align: center;
        }

        .file-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: var(--radius);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            padding: 20px;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: var(--radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px 25px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            color: var(--primary);
            font-size: 1.3rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark-gray);
            transition: var(--transition);
        }

        .modal-close:hover {
            color: var(--primary);
        }

        .modal-body {
            padding: 20px 25px;
        }

        .modal-footer {
            padding: 15px 25px 25px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            border-top: 1px solid var(--gray);
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--primary);
            color: white;
            padding: 12px 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            z-index: 3000;
            display: flex;
            align-items: center;
            gap: 8px;
            transform: translateY(100px);
            opacity: 0;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast i {
            font-size: 1.1rem;
        }

        /* Monthly Stats Styles */
        .monthly-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .month-stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            text-align: center;
            border-left: 4px solid var(--primary);
        }

        .month-stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary);
            margin: 10px 0;
        }

        .month-stat-label {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }

        .month-stat-period {
            font-size: 0.8rem;
            color: var(--dark-gray);
            margin-top: 5px;
        }

        /* Alert Styles */
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        /* Alert Styles */
        .alert {
            padding: 12px 15px;
            border-radius: var(--radius);
            margin-bottom: 15px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Reward Card Styles */
        .reward-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            border: 1px solid var(--gray);
            position: relative;
            cursor: pointer;
        }

        .reward-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .reward-card.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .reward-card.disabled:hover {
            transform: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-color: var(--gray);
        }

        .reward-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            color: var(--primary);
        }

        .reward-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            border: 2px solid var(--primary);
        }

        .reward-card h3 {
            font-size: 16px;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .reward-card p {
            font-size: 13px;
            color: #777;
            margin-bottom: 10px;
        }

        .reward-points {
            font-size: 14px;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .reward-stock {
            font-size: 12px;
            color: var(--dark-gray);
            margin-bottom: 10px;
        }

        .reward-category {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            text-transform: uppercase;
        }

        /* Responsive Styles - IMPROVED */
        @media (max-width: 992px) {
            .nav-links {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 280px;
                height: calc(100vh - 70px);
                background-color: var(--white);
                flex-direction: column;
                padding: 20px;
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
                transition: var(--transition);
                gap: 10px;
                z-index: 1001;
                overflow-y: auto;
            }
            
            .nav-links.active {
                left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .dashboard-content {
                grid-template-columns: 1fr;
            }

            .nav-right {
                gap: 10px;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 10px 3%;
            }
            
            .main-content {
                padding: 15px 3%;
                margin-top: 70px;
            }
            
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .page-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .section-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .monthly-stats {
                grid-template-columns: 1fr;
            }

            .user-details {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .nav-right {
                gap: 5px;
            }
            
            .btn-group {
                flex-wrap: wrap;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }

            .modal-content {
                margin: 10px;
                width: calc(100% - 20px);
            }

            .logo h1 {
                font-size: 1.1rem;
            }
        }

        /* Overlay for mobile menu */
        .nav-overlay {
            display: none;
            position: fixed;
            top: 70px;
            left: 0;
            width: 100%;
            height: calc(100vh - 70px);
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .nav-overlay.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Header & Navbar -->
    <header class="header" id="header">
        <nav class="navbar">
            <div class="logo">
                <h1>Bank <span>Sampah</span> Digital</h1>
            </div>
            
            <div class="nav-links" id="navLinks">
                <a href="#" class="nav-link active" data-tab="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="#" class="nav-link" data-tab="profile"><i class="fas fa-user"></i> Profil Saya</a>
                <a href="#" class="nav-link" data-tab="transaksiBaru"><i class="fas fa-plus-circle"></i> Transaksi Baru</a>
                <a href="#" class="nav-link" data-tab="transaksi"><i class="fas fa-exchange-alt"></i> Riwayat Transaksi</a>
                <a href="#" class="nav-link" data-tab="history"><i class="fas fa-chart-line"></i> History Poin</a>
                <a href="#" class="nav-link" data-tab="reward"><i class="fas fa-gift"></i> Reward</a>
            </div>
            
            <div class="nav-right">
                <div class="user-info"> 
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($nama_lengkap); ?></div>
                        <div class="user-role">Anggota</div>
                    </div>
                    <div class="user-avatar"><?php echo get_initials($nama_lengkap); ?></div>
                </div>
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- Overlay for mobile menu -->
    <div class="nav-overlay" id="navOverlay"></div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-title">
            <h2>Dashboard Saya</h2>
            <div class="actions">
                <button class="btn btn-primary" id="refreshData">
                    <i class="fas fa-sync-alt"></i> Refresh Data
                </button>
            </div>
        </div>

        <!-- Dashboard Tab -->
        <div class="tab-content active" id="dashboardTab">
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-weight-hanging"></i>
                    </div>
                    <div class="stat-value" id="totalWaste"><?php echo number_format($total_berat, 1); ?> kg</div>
                    <div class="stat-label">Total Sampah</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-value" id="totalIncome"><?php echo format_currency($total_nilai); ?></div>
                    <div class="stat-label">Total Tabungan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-value" id="totalPoints"><?php echo number_format($total_poin_history); ?></div>
                    <div class="stat-label">Poin Reward</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="stat-value" id="totalTransactions"><?php echo $total_transaksi; ?></div>
                    <div class="stat-label">Total Transaksi</div>
                </div>
            </div>

            <div class="dashboard-content">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="section-title">
                        <h3>Aksi Cepat</h3>
                    </div>
                    <div class="quick-actions">
                        <div class="action-card" id="schedulePickup">
                            <div class="action-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <h3>Jadwalkan Penjemputan</h3>
                            <p>Atur jadwal penjemputan sampah</p>
                        </div>
                        <div class="action-card" id="viewHistory">
                            <div class="action-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <h3>Lihat Riwayat</h3>
                            <p>Cek riwayat transaksi terbaru</p>
                        </div>
                        <div class="action-card" id="redeemPoints">
                            <div class="action-icon">
                                <i class="fas fa-gift"></i>
                            </div>
                            <h3>Tukar Poin</h3>
                            <p>Tukar poin dengan reward menarik</p>
                        </div>
                        <div class="action-card" id="monthlyReport">
                            <div class="action-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3>Laporan Bulanan</h3>
                            <p>Lihat laporan aktivitas sampah</p>
                        </div>
                    </div>
                </div>

                <!-- Transaksi Terbaru -->
                <div class="card">
                    <div class="section-title">
                        <h3>Transaksi Terbaru</h3>
                        <button class="btn btn-outline btn-sm" id="viewAllTransactions">
                            Lihat Semua
                        </button>
                    </div>
                    <div class="auto-update-indicator">
                        <div class="pulse-dot"></div>
                        <span>Data transaksi diperbarui otomatis</span>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis Sampah</th>
                                    <th>Berat</th>
                                    <th>Poin</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="recentTransactionsTable">
                                <?php if(empty($transaksi_data)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: #777;">Belum ada transaksi</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($transaksi_data as $transaksi): ?>
                                    <tr>
                                        <td><?php echo date('d M Y', strtotime($transaksi['tanggal'])); ?></td>
                                        <td><?php echo htmlspecialchars($transaksi['jenis_sampah']); ?></td>
                                        <td><?php echo number_format($transaksi['berat'], 1); ?> kg</td>
                                        <td><?php echo $transaksi['total_poin']; ?></td>
                                        <td>
                                            <span class="badge <?php echo get_status_badge($transaksi['status']); ?>">
                                                <?php echo get_status_text($transaksi['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Progress Section -->
            <div class="card">
                <div class="section-title">
                    <h3>Target Bulanan</h3>
                </div>
                <?php
                $target_bulanan = 50;
                $persentase = min(100, ($total_berat / $target_bulanan) * 100);
                $sisa_target = max(0, $target_bulanan - $total_berat);
                ?>
                <div class="progress-container">
                    <div class="progress-label">
                        <span>Target: <?php echo $target_bulanan; ?> Kg</span>
                        <span><?php echo number_format($persentase, 0); ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $persentase; ?>%"></div>
                    </div>
                </div>
                <p style="margin-top: 15px; color: #777; font-size: 14px;">
                    Anda telah mengumpulkan <?php echo number_format($total_berat, 1); ?> Kg dari target <?php echo $target_bulanan; ?> Kg sampah bulan ini. 
                    <?php if($sisa_target > 0): ?>
                        <strong><?php echo number_format($sisa_target, 1); ?> Kg lagi untuk mencapai target!</strong>
                    <?php else: ?>
                        <strong>Target tercapai! 🎉</strong>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Profile Tab -->
        <div class="tab-content" id="profileTab">
            <div class="card">
                <div class="profile-header">
                    <div class="profile-avatar"><?php echo get_initials($nama_lengkap); ?></div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($nama_lengkap); ?></h2>
                        <p>Anggota Aktif</p>
                    </div>
                </div>

                <div class="section-title">
                    <h3>Informasi Pribadi</h3>
                    <button class="btn btn-primary">Edit Profil</button>
                </div>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($nama_lengkap); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($alamat_email); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($nomor_telepon); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <textarea class="form-control" rows="3" readonly><?php echo htmlspecialchars($alamat); ?></textarea>
                </div>
            </div>

            <div class="card">
                <div class="section-title">
                    <h3>Statistik Saya</h3>
                </div>
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-weight-hanging"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($total_berat, 1); ?> kg</div>
                        <div class="stat-label">Total Sampah</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-value"><?php echo format_currency($total_nilai); ?></div>
                        <div class="stat-label">Total Tabungan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($total_poin_history); ?></div>
                        <div class="stat-label">Total Poin</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaksi Baru Tab -->
        <div class="tab-content" id="transaksiBaruTab">
            <div class="card">
                <div class="section-title">
                    <h3>Ajukan Transaksi Sampah Baru</h3>
                </div>
                
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="transaksiForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="jenis_sampah">Jenis Sampah *</label>
                            <select class="form-control" id="jenis_sampah" name="jenis_sampah" required>
                                <option value="">Pilih Jenis Sampah</option>
                                <option value="Plastik">Plastik (Rp 2.000/kg)</option>
                                <option value="Logam">Logam (Rp 5.000/kg)</option>
                                <option value="Kertas">Kertas (Rp 1.500/kg)</option>
                                <option value="Kaca">Kaca (Rp 1.000/kg)</option>
                                <option value="Organik">Organik (Rp 1.000/kg)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="berat">Berat Sampah (kg) *</label>
                            <input type="number" class="form-control" id="berat" name="berat" min="0.1" step="0.1" placeholder="Contoh: 2.5" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="alamat_jemput">Alamat Penjemputan *</label>
                        <textarea class="form-control" id="alamat_jemput" name="alamat_jemput" rows="3" required placeholder="Masukkan alamat lengkap penjemputan sampah"><?php echo htmlspecialchars($alamat); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="catatan">Catatan (Opsional)</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="2" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                    
                    <!-- Preview Kalkulasi -->
                    <div class="card" style="background-color: #f8f9fa; margin-bottom: 20px;">
                        <div class="section-title">
                            <h3>Perhitungan Transaksi</h3>
                        </div>
                        <div id="calculationPreview">
                            <p style="text-align: center; color: #6c757d;">Pilih jenis dan berat sampah untuk melihat perhitungan</p>
                        </div>
                    </div>
                    
                    <div class="form-group" style="text-align: center;">
                        <button type="submit" name="submit_transaksi" class="btn btn-primary" style="padding: 12px 30px; font-size: 1rem;">
                            <i class="fas fa-paper-plane"></i> Ajukan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transaksi Tab -->
        <div class="tab-content" id="transaksiTab">
            <div class="card">
                <div class="section-title">
                    <h3>Riwayat Transaksi</h3>
                    <div class="btn-group">
                        <button class="btn btn-outline btn-sm" id="filterTransactions">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button class="btn btn-primary btn-sm" id="exportTransactions">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis Sampah</th>
                                <th>Berat (kg)</th>
                                <th>Harga/Kg</th>
                                <th>Total (Rp)</th>
                                <th>Poin</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="allTransactionsTable">
                            <?php if(empty($all_transaksi_data)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #777;">Belum ada transaksi</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($all_transaksi_data as $transaksi): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($transaksi['tanggal'])); ?></td>
                                    <td><?php echo htmlspecialchars($transaksi['jenis_sampah']); ?></td>
                                    <td><?php echo number_format($transaksi['berat'], 1); ?></td>
                                    <td><?php echo format_currency($transaksi['harga_per_kg']); ?></td>
                                    <td><?php echo format_currency($transaksi['total']); ?></td>
                                    <td><?php echo $transaksi['total_poin']; ?></td>
                                    <td>
                                        <span class="badge <?php echo get_status_badge($transaksi['status']); ?>">
                                            <?php echo get_status_text($transaksi['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- History Poin Tab -->
        <div class="tab-content" id="historyTab">
            <div class="card">
                <div class="section-title">
                    <h3>Ringkasan History Poin</h3>
                    <div class="btn-group">
                        <button class="btn btn-outline btn-sm active" data-period="month">Bulan Ini</button>
                        <button class="btn btn-outline btn-sm" data-period="quarter">3 Bulan</button>
                        <button class="btn btn-outline btn-sm" data-period="year">1 Tahun</button>
                    </div>
                </div>
                
                <!-- Statistik Bulanan -->
                <div class="monthly-stats">
                    <div class="month-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-weight-hanging"></i>
                        </div>
                        <div class="month-stat-value"><?php echo number_format($total_berat * 0.3, 1); ?> kg</div>
                        <div class="month-stat-label">Total Sampah Bulan Ini</div>
                        <div class="month-stat-period"><?php echo date('F Y'); ?></div>
                    </div>
                    <div class="month-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="month-stat-value"><?php echo number_format($total_poin_history * 0.3); ?></div>
                        <div class="month-stat-label">Total Poin Bulan Ini</div>
                        <div class="month-stat-period"><?php echo date('F Y'); ?></div>
                    </div>
                    <div class="month-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="month-stat-value"><?php echo $total_transaksi; ?></div>
                        <div class="month-stat-label">Transaksi Bulan Ini</div>
                        <div class="month-stat-period"><?php echo date('F Y'); ?></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">
                    <h3>Riwayat Poin & Kilogram per Bulan</h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Total Sampah (kg)</th>
                                <th>Total Poin</th>
                                <th>Jumlah Transaksi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong><?php echo date('F Y'); ?></strong></td>
                                <td><?php echo number_format($total_berat * 0.3, 1); ?></td>
                                <td><?php echo number_format($total_poin_history * 0.3); ?></td>
                                <td><?php echo $total_transaksi; ?></td>
                                <td><span class="badge badge-success">Aktif</span></td>
                            </tr>
                            <tr>
                                <td><?php echo date('F Y', strtotime('-1 month')); ?></td>
                                <td>28.5</td>
                                <td>285</td>
                                <td>5</td>
                                <td><span class="badge badge-success">Selesai</span></td>
                            </tr>
                            <tr>
                                <td><?php echo date('F Y', strtotime('-2 months')); ?></td>
                                <td>26.3</td>
                                <td>263</td>
                                <td>4</td>
                                <td><span class="badge badge-success">Selesai</span></td>
                            </tr>
                            <tr>
                                <td><?php echo date('F Y', strtotime('-3 months')); ?></td>
                                <td>27.7</td>
                                <td>277</td>
                                <td>5</td>
                                <td><span class="badge badge-success">Selesai</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

           
        <!-- Reward Tab -->
        <div class="tab-content" id="rewardTab">
            <?php if(isset($redeem_success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $redeem_success; ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($redeem_error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $redeem_error; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="section-title">
                    <h3>Poin Reward Saya</h3>
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm" id="redeemReward">
                            <i class="fas fa-gift"></i> Tukar Poin
                        </button>
                    </div>
                </div>
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($total_poin_history); ?></div>
                        <div class="stat-label">Poin Tersedia</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="stat-value">0</div>
                        <div class="stat-label">Reward Ditukar</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-value">Gold</div>
                        <div class="stat-label">Tier Member</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">
                    <h3>Katalog Reward</h3>
                </div>
                <div class="quick-actions">
                    <?php if(empty($reward_data)): ?>
                        <div style="text-align: center; padding: 40px; color: #777;">
                            <i class="fas fa-gift" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
                            <p>Belum ada reward yang tersedia</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($reward_data as $reward): ?>
                            <?php 
                            $can_redeem = $total_poin_history >= $reward['poin_dibutuhkan'];
                            $card_class = $can_redeem ? 'reward-card' : 'reward-card disabled';
                            ?>
                            <div class="<?php echo $card_class; ?>" onclick="<?php echo $can_redeem ? 'showRedeemConfirm(' . $reward['id'] . ', ' . $reward['poin_dibutuhkan'] . ')' : ''; ?>">
                                <span class="reward-category"><?php echo $reward['kategori']; ?></span>
                                
                                <?php if(!empty($reward['gambar'])): ?>
                                    <img src="../uploads/reward/<?php echo $reward['gambar']; ?>" alt="<?php echo htmlspecialchars($reward['nama_reward']); ?>" class="reward-image">
                                <?php else: ?>
                                    <div class="reward-icon">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <h3><?php echo htmlspecialchars($reward['nama_reward']); ?></h3>
                                <p><?php echo htmlspecialchars($reward['deskripsi']); ?></p>
                                <div class="reward-points"><?php echo number_format($reward['poin_dibutuhkan']); ?> Poin</div>
                                <div class="reward-stock">Stok: <?php echo $reward['stok']; ?></div>
                                
                                <?php if($can_redeem): ?>
                                    <button class="btn btn-primary btn-sm">Tukar Sekarang</button>
                                <?php else: ?>
                                    <button class="btn btn-outline btn-sm" disabled>Poin Tidak Cukup</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Jadwal Penjemputan -->
    <div class="modal" id="pickupModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Jadwalkan Penjemputan Sampah</h3>
                <button class="modal-close" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="pickupForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <?php if(isset($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($error_message)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($upload_error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $upload_error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="jenis_sampah">Jenis Sampah</label>
                        <select class="form-control" id="jenis_sampah" name="jenis_sampah" required>
                            <option value="">Pilih Jenis Sampah</option>
                            <option value="Plastik" <?php echo (isset($_POST['jenis_sampah']) && $_POST['jenis_sampah'] == 'Plastik') ? 'selected' : ''; ?>>Plastik</option>
                            <option value="Logam" <?php echo (isset($_POST['jenis_sampah']) && $_POST['jenis_sampah'] == 'Logam') ? 'selected' : ''; ?>>Logam</option>
                            <option value="Organik" <?php echo (isset($_POST['jenis_sampah']) && $_POST['jenis_sampah'] == 'Organik') ? 'selected' : ''; ?>>Organik</option>
                            <option value="Kertas" <?php echo (isset($_POST['jenis_sampah']) && $_POST['jenis_sampah'] == 'Kertas') ? 'selected' : ''; ?>>Kertas</option>
                            <option value="Kaca" <?php echo (isset($_POST['jenis_sampah']) && $_POST['jenis_sampah'] == 'Kaca') ? 'selected' : ''; ?>>Kaca</option>
                            <option value="Elektronik" <?php echo (isset($_POST['jenis_sampah']) && $_POST['jenis_sampah'] == 'Elektronik') ? 'selected' : ''; ?>>Elektronik</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="alamat_jemput">Alamat Penjemputan</label>
                        <textarea class="form-control" id="alamat_jemput" name="alamat_jemput" rows="3" required placeholder="Masukkan alamat lengkap penjemputan sampah"><?php echo isset($_POST['alamat_jemput']) ? htmlspecialchars($_POST['alamat_jemput']) : htmlspecialchars($alamat); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="foto_sampah">Foto Sampah</label>
                        <div class="file-upload" id="fileUploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Klik atau seret gambar ke sini</p>
                            <small>Format yang didukung: JPG, PNG, GIF (Maks. 5MB)</small>
                            <input type="file" id="foto_sampah" name="foto_sampah" accept="image/*" style="display: none;">
                        </div>
                        <div class="file-preview" id="filePreview">
                            <img id="previewImage" src="" alt="Preview Foto Sampah">
                            <button type="button" class="btn btn-outline btn-sm" id="removeImage" style="margin-top: 10px;">
                                <i class="fas fa-trash"></i> Hapus Gambar
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="catatan">Catatan (Opsional)</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="2" placeholder="Tambahkan catatan jika diperlukan..."><?php echo isset($_POST['catatan']) ? htmlspecialchars($_POST['catatan']) : ''; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" id="cancelPickup">Batal</button>
                    <button type="submit" name="submit_pickup" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Ajukan Penjemputan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Redeem -->
    <div class="modal" id="redeemModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tukar Poin Reward</h3>
                <button class="modal-close" id="closeRedeemModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="redeemForm" method="POST">
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menukar poin untuk reward ini?</p>
                    <div id="redeemDetails"></div>
                    <input type="hidden" id="rewardId" name="reward_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" id="cancelRedeem">Batal</button>
                    <button type="submit" name="redeem_reward" class="btn btn-primary">
                        <i class="fas fa-check"></i> Ya, Tukar Poin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Tindakan berhasil dilakukan!</span>
    </div>

    <script>
        // DOM Elements
        const header = document.getElementById('header');
        const navLinks = document.getElementById('navLinks');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navOverlay = document.getElementById('navOverlay');
        const refreshDataBtn = document.getElementById('refreshData');
        const viewAllTransactionsBtn = document.getElementById('viewAllTransactions');
        const filterTransactionsBtn = document.getElementById('filterTransactions');
        const exportTransactionsBtn = document.getElementById('exportTransactions');
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        const navLinksElements = document.querySelectorAll('.nav-link');
        const tabContents = document.querySelectorAll('.tab-content');

        // Quick action elements
        const schedulePickupBtn = document.getElementById('schedulePickup');
        const viewHistoryBtn = document.getElementById('viewHistory');
        const redeemPointsBtn = document.getElementById('redeemPoints');
        const monthlyReportBtn = document.getElementById('monthlyReport');

        // Modal elements
        const pickupModal = document.getElementById('pickupModal');
        const closeModal = document.getElementById('closeModal');
        const cancelPickup = document.getElementById('cancelPickup');
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('foto_sampah');
        const filePreview = document.getElementById('filePreview');
        const previewImage = document.getElementById('previewImage');
        const removeImageBtn = document.getElementById('removeImage');

        // Redeem modal elements
        const redeemModal = document.getElementById('redeemModal');
        const closeRedeemModal = document.getElementById('closeRedeemModal');
        const cancelRedeem = document.getElementById('cancelRedeem');
        const redeemDetails = document.getElementById('redeemDetails');
        const rewardIdInput = document.getElementById('rewardId');

        // Initialize the application
        function init() {
            setupEventListeners();
            updateGreeting();
            
            // Auto open modal jika ada error
            <?php if(isset($error_message) || isset($upload_error)): ?>
                setTimeout(() => {
                    openPickupModal();
                }, 500);
            <?php endif; ?>
            
            // Show welcome message
            setTimeout(() => {
                showToast('Selamat datang di dashboard Bank Sampah Digital!');
            }, 1000);
        }

        // Setup event listeners
        function setupEventListeners() {
            // Navbar scroll effect
            window.addEventListener('scroll', handleScroll);
            
            // Mobile menu toggle
            mobileMenuBtn.addEventListener('click', toggleMobileMenu);
            navOverlay.addEventListener('click', toggleMobileMenu);
            
            // Navigation links
            navLinksElements.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const tab = e.currentTarget.getAttribute('data-tab');
                    switchTab(tab);
                    
                    // Close mobile menu if open
                    if (navLinks.classList.contains('active')) {
                        toggleMobileMenu();
                    }
                });
            });
            
            // Refresh data
            refreshDataBtn.addEventListener('click', refreshData);
            
            // View all transactions
            viewAllTransactionsBtn.addEventListener('click', () => {
                switchTab('transaksi');
            });
            
            // Filter transactions
            filterTransactionsBtn.addEventListener('click', () => {
                showToast('Filter transaksi diterapkan');
            });
            
            // Export transactions
            exportTransactionsBtn.addEventListener('click', () => {
                showToast('Data transaksi berhasil diexport');
            });
            
            // Quick actions
            schedulePickupBtn.addEventListener('click', openPickupModal);
            
            viewHistoryBtn.addEventListener('click', () => {
                switchTab('transaksi');
            });
            
            redeemPointsBtn.addEventListener('click', () => {
                switchTab('reward');
            });
            
            monthlyReportBtn.addEventListener('click', () => {
                switchTab('history');
            });
            
            // Modal events
            closeModal.addEventListener('click', closePickupModal);
            cancelPickup.addEventListener('click', closePickupModal);
            
            // Redeem modal events
            closeRedeemModal.addEventListener('click', closeRedeemModal);
            cancelRedeem.addEventListener('click', closeRedeemModal);
            
            // File upload handling
            fileUploadArea.addEventListener('click', () => {
                fileInput.click();
            });
            
            fileInput.addEventListener('change', handleFileSelect);
            
            // Remove image button
            removeImageBtn.addEventListener('click', removeImage);
            
            // Drag and drop for file upload
            fileUploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                fileUploadArea.style.borderColor = 'var(--primary)';
                fileUploadArea.style.backgroundColor = 'rgba(46, 125, 50, 0.1)';
            });
            
            fileUploadArea.addEventListener('dragleave', () => {
                fileUploadArea.style.borderColor = 'var(--gray)';
                fileUploadArea.style.backgroundColor = '';
            });
            
            fileUploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                fileUploadArea.style.borderColor = 'var(--gray)';
                fileUploadArea.style.backgroundColor = '';
                
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    handleFileSelect(e);
                }
            });

            // Filter periode untuk history poin
            document.querySelectorAll('[data-period]').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Hapus active class dari semua button
                    document.querySelectorAll('[data-period]').forEach(b => {
                        b.classList.remove('active');
                    });
                    // Tambah active class ke button yang diklik
                    this.classList.add('active');
                    
                    const period = this.getAttribute('data-period');
                    filterHistoryByPeriod(period);
                });
            });
        }

        // Filter history berdasarkan periode
        function filterHistoryByPeriod(period) {
            let message = '';
            switch(period) {
                case 'month':
                    message = 'Menampilkan data bulan ini';
                    break;
                case 'quarter':
                    message = 'Menampilkan data 3 bulan terakhir';
                    break;
                case 'year':
                    message = 'Menampilkan data 1 tahun terakhir';
                    break;
            }
            showToast(message);
        }

        // Handle file selection for image preview
        function handleFileSelect(e) {
            const file = fileInput.files[0];
            
            if (file) {
                // Check file type
                if (!file.type.match('image.*')) {
                    showToast('Hanya file gambar yang diizinkan', 'error');
                    return;
                }
                
                // Check file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    showToast('Ukuran file maksimal 5MB', 'error');
                    return;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    filePreview.style.display = 'block';
                };
                
                reader.readAsDataURL(file);
            }
        }

        // Remove image preview
        function removeImage() {
            fileInput.value = '';
            filePreview.style.display = 'none';
            showToast('Gambar berhasil dihapus');
        }

        // Open pickup modal
        function openPickupModal() {
            pickupModal.classList.add('active');
        }

        // Close pickup modal
        function closePickupModal() {
            pickupModal.classList.remove('active');
            // Reset form preview
            filePreview.style.display = 'none';
        }

        // Handle scroll for navbar effect
        function handleScroll() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }

        // Toggle mobile menu
        function toggleMobileMenu() {
            navLinks.classList.toggle('active');
            navOverlay.classList.toggle('active');
            const icon = mobileMenuBtn.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
                document.body.style.overflow = 'hidden';
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
                document.body.style.overflow = '';
            }
        }

        // Switch tabs
        function switchTab(tabId) {
            // Update active nav link
            navLinksElements.forEach(link => {
                if (link.getAttribute('data-tab') === tabId) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
            
            // Update active tab content
            tabContents.forEach(content => {
                if (content.id === `${tabId}Tab`) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        }

        // Update greeting based on time of day
        function updateGreeting() {
            const hour = new Date().getHours();
            let greeting = "Selamat datang";
            
            if (hour < 12) greeting = "Selamat pagi";
            else if (hour < 15) greeting = "Selamat siang";
            else if (hour < 19) greeting = "Selamat sore";
            else greeting = "Selamat malam";
            
            // Update page title if on dashboard
            const pageTitle = document.querySelector('.page-title h2');
            if (pageTitle && document.getElementById('dashboardTab').classList.contains('active')) {
                pageTitle.textContent = `${greeting}, <?php echo explode(' ', $nama_lengkap)[0]; ?>!`;
            }
        }

        // Refresh data
        function refreshData() {
            // Show loading state
            const originalText = refreshDataBtn.innerHTML;
            refreshDataBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';
            refreshDataBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Update greeting
                updateGreeting();
                
                // Restore button
                refreshDataBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Data';
                refreshDataBtn.disabled = false;
                
                showToast('Data berhasil diperbarui');
            }, 1500);
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            toastMessage.textContent = message;
            
            // Change icon based on type
            const icon = toast.querySelector('i');
            if (type === 'error') {
                icon.className = 'fas fa-exclamation-circle';
                toast.style.backgroundColor = '#f44336';
            } else if (type === 'info') {
                icon.className = 'fas fa-info-circle';
                toast.style.backgroundColor = '#2196f3';
            } else {
                icon.className = 'fas fa-check-circle';
                toast.style.backgroundColor = 'var(--primary)';
            }
            
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Initialize the app when DOM is loaded
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>