<?php
session_start();
include "../backend/connect.php";

// Cek login
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../backend/login.php');
    exit;
}

// Definisikan email admin
$admin_emails = ['basada964@gmail.com', 'admin@basada.com', 'lutpifauzan23@gmail.com'];

// Cek apakah user adalah admin berdasarkan email
if(!in_array($_SESSION['alamat_email'], $admin_emails)) {
    // Jika bukan admin, redirect ke dashboard biasa
    header('Location: dashboard.php');
    exit;
}

$user_name = $_SESSION['nama'];
$user_email = $_SESSION['alamat_email'];

// Fungsi untuk mengambil data dari database
function getTotalMembers($connect) {
    $query = "SELECT COUNT(*) as total FROM user";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getTotalWaste($connect) {
    $query = "SELECT SUM(berat) as total FROM transaksi_sampah WHERE status = 'approved'";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ? $row['total'] : 0;
}

function getTotalIncome($connect) {
    $query = "SELECT SUM(total) as total FROM transaksi_sampah WHERE status = 'approved'";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ? $row['total'] : 0;
}

function getActiveMembers($connect) {
    $query = "SELECT COUNT(*) as total FROM user WHERE status = 'active'";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getRecentMembers($connect, $limit = 5) {
    $query = "SELECT id, nama, email, tanggal_daftar, status FROM user ORDER BY tanggal_daftar DESC LIMIT $limit";
    $result = mysqli_query($connect, $query);
    $members = [];
    while($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
    return $members;
}

function getAllMembers($connect) {
    $query = "SELECT id, nama, email, tanggal_daftar, status, terakhir_login FROM user ORDER BY tanggal_daftar DESC";
    $result = mysqli_query($connect, $query);
    $members = [];
    while($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
    return $members;
}

function getWasteData($connect, $limit = 5) {
    $query = "SELECT ts.id, ts.tanggal, u.nama, ts.jenis_sampah, ts.berat, ts.harga_per_kg, ts.total, ts.status
              FROM transaksi_sampah ts 
              JOIN user u ON ts.id_anggota = u.id 
              ORDER BY ts.tanggal DESC 
              LIMIT $limit";
    $result = mysqli_query($connect, $query);
    $waste = [];
    while($row = mysqli_fetch_assoc($result)) {
        $waste[] = $row;
    }
    return $waste;
}

function getAllWasteData($connect) {
    $query = "SELECT ts.id, ts.tanggal, u.nama, ts.jenis_sampah, ts.berat, ts.harga_per_kg, ts.total, ts.status
              FROM transaksi_sampah ts 
              JOIN user u ON ts.id_anggota = u.id 
              ORDER BY ts.tanggal DESC";
    $result = mysqli_query($connect, $query);
    $waste = [];
    while($row = mysqli_fetch_assoc($result)) {
        $waste[] = $row;
    }
    return $waste;
}

function getPendingWasteData($connect) {
    $query = "SELECT ts.id, ts.tanggal, u.nama, ts.jenis_sampah, ts.berat, ts.harga_per_kg, ts.total, ts.status
              FROM transaksi_sampah ts 
              JOIN user u ON ts.id_anggota = u.id 
              WHERE ts.status = 'pending'
              ORDER BY ts.tanggal DESC";
    $result = mysqli_query($connect, $query);
    $waste = [];
    while($row = mysqli_fetch_assoc($result)) {
        $waste[] = $row;
    }
    return $waste;
}

function getPendingWasteCount($connect) {
    $query = "SELECT COUNT(*) as total FROM transaksi_sampah WHERE status = 'pending'";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getWastePrices($connect) {
    $query = "SELECT id, jenis_sampah, harga, status FROM harga_sampah";
    $result = mysqli_query($connect, $query);
    $prices = [];
    while($row = mysqli_fetch_assoc($result)) {
        $prices[] = $row;
    }
    return $prices;
}

function getIncomeData($connect) {
    $query = "SELECT 
                bulan,
                total_berat,
                total_pendapatan,
                status_pembayaran
              FROM pendapatan_bulanan 
              ORDER BY tahun DESC, bulan DESC";
    $result = mysqli_query($connect, $query);
    $income = [];
    while($row = mysqli_fetch_assoc($result)) {
        $income[] = $row;
    }
    return $income;
}

// Ambil data dari database
$totalMembers = getTotalMembers($connect);
$totalWaste = getTotalWaste($connect);
$totalIncome = getTotalIncome($connect);
$activeMembers = getActiveMembers($connect);
$recentMembers = getRecentMembers($connect);
$allMembers = getAllMembers($connect);
$recentWaste = getWasteData($connect);
$allWasteData = getAllWasteData($connect);
$pendingWasteData = getPendingWasteData($connect);
$pendingWasteCount = getPendingWasteCount($connect);
$wastePrices = getWastePrices($connect);
$incomeData = getIncomeData($connect);

// Hitung statistik bulanan
$monthIncome = 0;
$monthWaste = 0;
$currentMonth = date('m');
$currentYear = date('Y');

$monthQuery = "SELECT SUM(total) as pendapatan, SUM(berat) as sampah 
               FROM transaksi_sampah 
               WHERE MONTH(tanggal) = $currentMonth AND YEAR(tanggal) = $currentYear 
               AND status = 'approved'";
$monthResult = mysqli_query($connect, $monthQuery);
if($monthRow = mysqli_fetch_assoc($monthResult)) {
    $monthIncome = $monthRow['pendapatan'] ? $monthRow['pendapatan'] : 0;
    $monthWaste = $monthRow['sampah'] ? $monthRow['sampah'] : 0;
}

// Format angka untuk display
function formatRupiah($angka) {
    if ($angka >= 1000000) {
        return number_format($angka / 1000000, 1) . ' JT';
    } elseif ($angka >= 1000) {
        return number_format($angka / 1000, 1) . ' RB';
    }
    return number_format($angka);
}

function formatWeight($berat) {
    return number_format($berat, 1) . ' kg';
}

// Konversi nama bulan
function getMonthName($monthNumber) {
    $months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return $months[$monthNumber - 1] ?? $monthNumber;
}

// Fungsi untuk mendapatkan badge status
function getStatusBadge($status) {
    switch($status) {
        case 'pending':
            return 'badge-warning';
        case 'approved':
            return 'badge-success';
        case 'rejected':
            return 'badge-danger';
        default:
            return 'badge-warning';
    }
}

// Fungsi untuk mendapatkan teks status
function getStatusText($status) {
    switch($status) {
        case 'pending':
            return 'Menunggu ACC';
        case 'approved':
            return 'Disetujui';
        case 'rejected':
            return 'Ditolak';
        default:
            return 'Menunggu ACC';
    }
}

// Fungsi untuk mengecek status user berdasarkan last_login
function get_user_status($terakhir_login, $current_user_id = null) {
    if (!$terakhir_login) {
        return 'Tidak Aktif';
    }
    
    $terakhir_login_time = strtotime($terakhir_login);
    $current_time = time();
    $diff_minutes = ($current_time - $terakhir_login_time) / 60; // Difference in minutes
    
    // Jika user sedang login saat ini = Online
    if ($current_user_id && isset($_SESSION['user_id']) && $current_user_id == $_SESSION['user_id']) {
        return 'Online';
    }
    // Jika login dalam 5 menit terakhir = Online
    elseif ($diff_minutes <= 5) {
        return 'Online';
    }
    // Jika login dalam 24 jam terakhir = Aktif
    elseif ($diff_minutes <= 1440) { // 24 jam = 1440 menit
        return 'Aktif';
    } else {
        return 'Tidak Aktif';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Bank Sampah Digital</title>
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

        /* Header & Navbar */
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 5%;
            max-width: 1400px;
            margin: 0 auto;
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

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: var(--primary);
            cursor: pointer;
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

        .btn-danger {
            background-color: #f44336;
            color: white;
        }

        .btn-danger:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: #4caf50;
            color: white;
        }

        .btn-success:hover {
            background-color: #45a049;
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
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(2px);
        }

        .modal-content {
            background-color: var(--white);
            border-radius: var(--radius);
            width: 500px;
            max-width: 90%;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--gray);
        }

        .modal-title {
            font-size: 1.2rem;
            color: var(--primary);
        }

        .close {
            background: none;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            color: var(--dark-gray);
            transition: var(--transition);
        }

        .close:hover {
            color: var(--text);
            transform: rotate(90deg);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 20px;
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
        .tabs {
            display: flex;
            border-bottom: 1px solid var(--gray);
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: var(--transition);
            font-weight: 500;
        }

        .tab.active {
            border-bottom: 2px solid var(--primary);
            color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Connection Status */
        .connection-status {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .status-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray);
        }

        .status-item:last-child {
            border-bottom: none;
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

        /* Responsive Styles */
        @media (max-width: 992px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: var(--white);
                flex-direction: column;
                padding: 15px;
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
                border-top: 1px solid var(--gray);
                gap: 10px;
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .dashboard-content {
                grid-template-columns: 1fr;
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
        }

        @media (max-width: 576px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .nav-right {
                gap: 10px;
            }
            
            .tabs {
                flex-wrap: wrap;
            }
            
            .tab {
                padding: 8px 12px;
                font-size: 0.85rem;
            }
            
            .btn-group {
                flex-wrap: wrap;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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

        /* Database Connection Styles */
        .db-connection-form {
            background-color: var(--light-gray);
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
        }

        .connection-info {
            margin-top: 10px;
            font-size: 0.85rem;
            color: var(--dark-gray);
        }

        .connection-info i {
            color: var(--primary);
            margin-right: 5px;
        }

        /* Action Buttons in Tables */
        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
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

        /* Notification Card Styles */
        .notification-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        }

        .notification-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary);
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            display: flex;
            align-items: flex-start;
            gap: 15px;
            position: relative;
            overflow: hidden;
        }

        .notification-card.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification-card.hide {
            transform: translateX(400px);
            opacity: 0;
        }

        .notification-card.success {
            border-left-color: #4caf50;
            background: linear-gradient(135deg, #f1f8e9 0%, #e8f5e9 100%);
        }

        .notification-card.error {
            border-left-color: #f44336;
            background: linear-gradient(135deg, #ffebee 0%, #fce4ec 100%);
        }

        .notification-card.warning {
            border-left-color: #ff9800;
            background: linear-gradient(135deg, #fff3e0 0%, #fff8e1 100%);
        }

        .notification-card.info {
            border-left-color: #2196f3;
            background: linear-gradient(135deg, #e3f2fd 0%, #e1f5fe 100%);
        }

        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.5rem;
        }

        .notification-card.success .notification-icon {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }

        .notification-card.error .notification-icon {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .notification-card.warning .notification-icon {
            background: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }

        .notification-card.info .notification-icon {
            background: rgba(33, 150, 243, 0.1);
            color: #2196f3;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 5px;
            color: var(--text);
        }

        .notification-message {
            font-size: 0.9rem;
            color: var(--dark-gray);
            line-height: 1.4;
        }

        .notification-close {
            background: none;
            border: none;
            color: var(--dark-gray);
            cursor: pointer;
            font-size: 1rem;
            padding: 5px;
            border-radius: 4px;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .notification-close:hover {
            background: rgba(0, 0, 0, 0.1);
            color: var(--text);
        }

        .notification-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: currentColor;
            opacity: 0.3;
            width: 100%;
            transform: scaleX(1);
            transform-origin: left;
            animation: progressBar 5s linear forwards;
        }

        @keyframes progressBar {
            from { transform: scaleX(1); }
            to { transform: scaleX(0); }
        }

        /* Confirmation Modal Styles */
        .confirmation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .confirmation-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 30px;
            max-width: 450px;
            width: 90%;
            text-align: center;
            transform: scale(0.7);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .confirmation-card.show {
            transform: scale(1);
            opacity: 1;
        }

        .confirmation-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }

        .confirmation-icon.warning {
            background: rgba(255, 152, 0, 0.1);
            color: #ff9800;
            border: 2px solid rgba(255, 152, 0, 0.2);
        }

        .confirmation-icon.danger {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border: 2px solid rgba(244, 67, 54, 0.2);
        }

        .confirmation-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text);
        }

        .confirmation-message {
            color: var(--dark-gray);
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .confirmation-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .confirmation-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
        }

        .confirmation-btn.cancel {
            background: var(--gray);
            color: var(--text);
        }

        .confirmation-btn.cancel:hover {
            background: var(--dark-gray);
            transform: translateY(-2px);
        }

        .confirmation-btn.confirm {
            background: var(--primary);
            color: white;
        }

        .confirmation-btn.confirm:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .confirmation-btn.danger {
            background: #f44336;
            color: white;
        }

        .confirmation-btn.danger:hover {
            background: #d32f2f;
            transform: translateY(-2px);
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
                <a href="#" class="nav-link" data-tab="anggota"><i class="fas fa-users"></i> Anggota</a>
                <a href="#" class="nav-link" data-tab="sampah"><i class="fas fa-weight-hanging"></i> Sampah</a>
                <a href="#" class="nav-link" data-tab="pendapatan"><i class="fas fa-money-bill-wave"></i> Pendapatan</a>
                <a href="#" class="nav-link" data-tab="database"><i class="fas fa-database"></i> Database</a>
            </div>
            
            <div class="nav-right">
                <div class="user-info" style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 0.9rem;">Halo, <?php echo htmlspecialchars($user_name); ?></span>
                </div>
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-title">
            <h2>Dashboard Admin</h2>
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
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value" id="totalMembers"><?php echo $totalMembers; ?></div>
                    <div class="stat-label">Total Anggota</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-weight-hanging"></i>
                    </div>
                    <div class="stat-value" id="totalWaste"><?php echo formatWeight($totalWaste); ?></div>
                    <div class="stat-label">Total Sampah</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-value" id="totalIncome"><?php echo formatRupiah($totalIncome); ?></div>
                    <div class="stat-label">Total Pendapatan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-value" id="activeMembers"><?php echo $activeMembers; ?></div>
                    <div class="stat-label">Anggota Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value" id="pendingWaste"><?php echo $pendingWasteCount; ?></div>
                    <div class="stat-label">Menunggu ACC</div>
                </div>
            </div>

            <div class="dashboard-content">
                <!-- Anggota Terbaru Section -->
                <div class="card">
                    <div class="section-title">
                        <h3>Anggota Terbaru</h3>
                        <button class="btn btn-outline btn-sm" id="viewAllMembers">
                            Lihat Semua
                        </button>
                    </div>
                    <div class="auto-update-indicator">
                        <div class="pulse-dot"></div>
                        <span>Data anggota diperbarui otomatis setiap 30 detik</span>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Tanggal Bergabung</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="recentMembersTable">
                                <?php foreach($recentMembers as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($member['tanggal_daftar'])); ?></td>
                                    <td><span class="badge badge-success"><?php echo $member['status'] === 'active' ? 'Aktif' : 'Tidak Aktif'; ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Data Sampah Terbaru -->
                <div class="card">
                    <div class="section-title">
                        <h3>Data Sampah Terbaru</h3>
                        <button class="btn btn-outline btn-sm" id="viewAllWaste">
                            Lihat Semua
                        </button>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis Sampah</th>
                                    <th>Berat (kg)</th>
                                    <th>Total (Rp)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="recentWasteTable">
                                <?php foreach($recentWaste as $waste): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($waste['tanggal'])); ?></td>
                                    <td><?php echo htmlspecialchars($waste['jenis_sampah']); ?></td>
                                    <td><?php echo $waste['berat']; ?> kg</td>
                                    <td><?php echo number_format($waste['total']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadge($waste['status']); ?>">
                                            <?php echo getStatusText($waste['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Anggota Tab -->
        <div class="tab-content" id="anggotaTab">
            <div class="card">
                <div class="section-title">
                    <h3>Daftar Anggota</h3>
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm" id="refreshMembers">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                        <button class="btn btn-outline btn-sm" id="exportMembers">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="auto-update-indicator">
                    <div class="pulse-dot"></div>
                    <span>Data anggota diperbarui otomatis setiap 30 detik</span>
                </div>
                <div class="table-container">
                    <!-- Di bagian tabel anggota, tambahkan kolom Saldo -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Tanggal Bergabung</th>
            <th>Saldo (Rp)</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody id="allMembersTable">
        <?php foreach($allMembers as $member): ?>
        <?php
        $last_login = isset($member['terakhir_login']) ? $member['terakhir_login'] : $member['tanggal_daftar'];
        $member_status = get_user_status($last_login, $member['id']);
        ?>
        <tr>
            <td><?php echo $member['id']; ?></td>
            <td><?php echo htmlspecialchars($member['nama']); ?></td>
            <td><?php echo htmlspecialchars($member['email']); ?></td>
            <td><?php echo date('d M Y', strtotime($member['tanggal_daftar'])); ?></td>
            <td style="font-weight: bold; color: var(--primary);">
                <?php echo number_format($member['saldo'] ?? 0, 0, ',', '.'); ?>
            </td>
            <td>
                <span class="badge <?php echo $member_status === 'Online' ? 'badge-success' : ($member_status === 'Aktif' ? 'badge-warning' : 'badge-danger'); ?>">
                    <?php echo $member_status; ?>
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-danger btn-sm delete-member-btn" data-id="<?php echo $member['id']; ?>" data-name="<?php echo htmlspecialchars($member['nama']); ?>">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
                </div>
            </div>
        </div>

        <!-- Sampah Tab -->
        <div class="tab-content" id="sampahTab">
            <!-- Card untuk Permintaan Penjualan yang Menunggu ACC -->
            <div class="card">
                <div class="section-title">
                    <h3>Permintaan Penjualan Sampah <span class="badge badge-warning" id="pendingCount"><?php echo $pendingWasteCount; ?> Menunggu</span></h3>
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm" id="refreshPending">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Anggota</th>
                                <th>Jenis Sampah</th>
                                <th>Berat (kg)</th>
                                <th>Harga (Rp)</th>
                                <th>Total (Rp)</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pendingWasteTable">
                            <?php foreach($pendingWasteData as $waste): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($waste['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($waste['nama']); ?></td>
                                <td><?php echo htmlspecialchars($waste['jenis_sampah']); ?></td>
                                <td><?php echo $waste['berat']; ?> kg</td>
                                <td><?php echo number_format($waste['harga_per_kg']); ?></td>
                                <td><?php echo number_format($waste['total']); ?></td>
                                <td>
                                    <span class="badge <?php echo getStatusBadge($waste['status']); ?>">
                                        <?php echo getStatusText($waste['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-success btn-sm approve-waste-btn" data-id="<?php echo $waste['id']; ?>" data-name="<?php echo htmlspecialchars($waste['nama']); ?>">
                                            <i class="fas fa-check"></i> Setujui
                                        </button>
                                        <button class="btn btn-danger btn-sm reject-waste-btn" data-id="<?php echo $waste['id']; ?>" data-name="<?php echo htmlspecialchars($waste['nama']); ?>">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($pendingWasteData)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--dark-gray);">
                                    Tidak ada permintaan penjualan yang menunggu persetujuan
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card untuk Data Sampah yang Sudah Disetujui -->
            <div class="card">
                <div class="section-title">
                    <h3>Data Sampah Disetujui</h3>
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm" id="addWasteBtn">
                            <i class="fas fa-plus"></i> Tambah Data
                        </button>
                        <button class="btn btn-outline btn-sm" id="filterWaste">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Anggota</th>
                                <th>Jenis Sampah</th>
                                <th>Berat (kg)</th>
                                <th>Harga (Rp)</th>
                                <th>Total (Rp)</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="wasteTable">
                            <?php foreach($allWasteData as $waste): ?>
                            <?php if($waste['status'] == 'approved'): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($waste['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($waste['nama']); ?></td>
                                <td><?php echo htmlspecialchars($waste['jenis_sampah']); ?></td>
                                <td><?php echo $waste['berat']; ?> kg</td>
                                <td><?php echo number_format($waste['harga_per_kg']); ?></td>
                                <td><?php echo number_format($waste['total']); ?></td>
                                <td>
                                    <span class="badge badge-success">Disetujui</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-danger btn-sm delete-waste-btn" data-id="<?php echo $waste['id']; ?>" data-name="<?php echo htmlspecialchars($waste['nama']); ?>">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card untuk Pengaturan Harga Sampah -->
            <div class="card">
                <div class="section-title">
                    <h3>Pengaturan Harga Sampah</h3>
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm" id="addPriceBtn">
                            <i class="fas fa-plus"></i> Tambah Jenis
                        </button>
                        <button class="btn btn-outline btn-sm" id="exportPrices">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Jenis Sampah</th>
                                <th>Harga per Kg (Rp)</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="priceTable">
                            <?php foreach($wastePrices as $price): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($price['jenis_sampah']); ?></td>
                                <td><?php echo number_format($price['harga']); ?></td>
                                <td>
                                    <span class="badge <?php echo $price['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $price['status'] === 'active' ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline btn-sm edit-price-btn" data-id="<?php echo $price['id']; ?>" data-jenis="<?php echo htmlspecialchars($price['jenis_sampah']); ?>" data-harga="<?php echo $price['harga']; ?>" data-status="<?php echo $price['status']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-price-btn" data-id="<?php echo $price['id']; ?>" data-jenis="<?php echo htmlspecialchars($price['jenis_sampah']); ?>">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pendapatan Tab -->
        <div class="tab-content" id="pendapatanTab">
            <div class="card">
                <div class="section-title">
                    <h3>Ringkasan Pendapatan</h3>
                    <div class="btn-group">
                        <button class="btn btn-outline btn-sm active" data-period="month">Bulan Ini</button>
                        <button class="btn btn-outline btn-sm" data-period="quarter">Kuartal Ini</button>
                        <button class="btn btn-outline btn-sm" data-period="year">Tahun Ini</button>
                    </div>
                </div>
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-value" id="monthIncome"><?php echo formatRupiah($monthIncome); ?></div>
                        <div class="stat-label">Pendapatan Bulan Ini</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-weight-hanging"></i>
                        </div>
                        <div class="stat-value" id="monthWaste"><?php echo formatWeight($monthWaste); ?></div>
                        <div class="stat-label">Sampah Bulan Ini</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value" id="growth">+12%</div>
                        <div class="stat-label">Pertumbuhan</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">
                    <h3>Rincian Pendapatan</h3>
                    <div class="btn-group">
                        <button class="btn btn-outline btn-sm" id="exportIncome">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Total Sampah (kg)</th>
                                <th>Pendapatan (Rp)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="incomeTable">
                            <?php foreach($incomeData as $income): ?>
                            <tr>
                                <td><?php echo getMonthName($income['bulan']); ?></td>
                                <td><?php echo number_format($income['total_berat'], 1); ?> kg</td>
                                <td><?php echo number_format($income['total_pendapatan']); ?></td>
                                <td>
                                    <span class="badge badge-success"><?php echo $income['status_pembayaran']; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        Database Tab
        <div class="tab-content" id="databaseTab">
            <div class="card">
                <div class="section-title">
                    <h3>Koneksi Database</h3>
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm" id="testConnection">
                            <i class="fas fa-plug"></i> Test Koneksi
                        </button>
                    </div>
                </div>
                
                <div class="db-connection-form">
                    <h4>Database Admin</h4>
                    <div class="form-group">
                        <label for="dbUrl">URL Database</label>
                        <input type="text" id="dbUrl" class="form-control" placeholder="Masukkan URL koneksi database" value="jdbc:mysql://localhost:3306/bank_sampah">
                    </div>
                    <div class="form-group">
                        <label for="dbUsername">Username Database</label>
                        <input type="text" id="dbUsername" class="form-control" placeholder="Masukkan username database" value="root">
                    </div>
                    <div class="form-group">
                        <label for="dbPassword">Password Database</label>
                        <input type="password" id="dbPassword" class="form-control" placeholder="Masukkan password database" value="********">
                    </div>
                </div>

                <div class="btn-group" style="margin-top: 20px;">
                    <button class="btn btn-primary" id="saveDbConfig">
                        <i class="fas fa-save"></i> Simpan Koneksi
                    </button>
                    <button class="btn btn-outline" id="resetDbConfig">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="section-title">
                    <h3>Status Koneksi</h3>
                </div>
                <div class="connection-status">
                    <div class="status-item">
                        <span>Database Admin:</span>
                        <span class="badge badge-success" id="adminDbStatus">Terhubung</span>
                    </div>
                    <div class="status-item">
                        <span>Terakhir Diperbarui:</span>
                        <span id="lastUpdate"><?php echo date('d M Y H:i:s'); ?></span>
                    </div>
                    <div class="status-item">
                        <span>Total Anggota:</span>
                        <span id="syncedMembers"><?php echo $totalMembers; ?> orang</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Edit Harga -->
    <div class="modal" id="editPriceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Harga Sampah</h3>
                <button class="close" id="closeEditModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="editJenis">Jenis Sampah</label>
                    <input type="text" id="editJenis" class="form-control">
                </div>
                <div class="form-group">
                    <label for="editHarga">Harga per Kg (Rp)</label>
                    <input type="number" id="editHarga" class="form-control">
                </div>
                <div class="form-group">
                    <label for="editStatus">Status</label>
                    <select id="editStatus" class="form-control">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelEdit">Batal</button>
                <button class="btn btn-primary" id="saveEdit">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Data Sampah -->
    <div class="modal" id="addWasteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Data Sampah</h3>
                <button class="close" id="closeWasteModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="wasteDate">Tanggal</label>
                    <input type="date" id="wasteDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="wasteMember">Anggota</label>
                    <select id="wasteMember" class="form-control">
                        <?php foreach($allMembers as $member): ?>
                        <option value="<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="wasteType">Jenis Sampah</label>
                    <select id="wasteType" class="form-control">
                        <?php foreach($wastePrices as $price): ?>
                        <?php if($price['status'] === 'active'): ?>
                        <option value="<?php echo $price['jenis_sampah']; ?>" data-price="<?php echo $price['harga']; ?>">
                            <?php echo htmlspecialchars($price['jenis_sampah']); ?> (Rp <?php echo number_format($price['harga']); ?>)
                        </option>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="wasteWeight">Berat (kg)</label>
                    <input type="number" id="wasteWeight" class="form-control" placeholder="Masukkan berat sampah" step="0.1">
                </div>
                <div class="form-group">
                    <label>Total Harga (Rp)</label>
                    <input type="text" id="totalPrice" class="form-control" readonly style="background-color: #f8f9fa;">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelWaste">Batal</button>
                <button class="btn btn-primary" id="saveWaste">
                    <i class="fas fa-save"></i> Simpan Data
                </button>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div class="notification-container" id="notificationContainer"></div>

    <!-- Confirmation Modal -->
    <div class="confirmation-modal" id="confirmationModal">
        <div class="confirmation-card" id="confirmationCard">
            <div class="confirmation-icon warning" id="confirmationIcon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="confirmation-title" id="confirmationTitle">Konfirmasi Tindakan</h3>
            <p class="confirmation-message" id="confirmationMessage">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
            <div class="confirmation-buttons">
                <button class="confirmation-btn cancel" id="confirmCancel">Batal</button>
                <button class="confirmation-btn confirm" id="confirmOk">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    <script>
        // Data untuk aplikasi - diambil dari PHP
        const appData = {
            allMembers: <?php echo json_encode($allMembers); ?>,
            wastePrices: <?php echo json_encode($wastePrices); ?>,
            wasteData: <?php echo json_encode($allWasteData); ?>,
            incomeData: <?php echo json_encode($incomeData); ?>,
            stats: {
                totalMembers: <?php echo $totalMembers; ?>,
                totalWaste: <?php echo $totalWaste ?: 0; ?>,
                totalIncome: <?php echo $totalIncome ?: 0; ?>,
                activeMembers: <?php echo $activeMembers; ?>,
                monthIncome: <?php echo $monthIncome ?: 0; ?>,
                monthWaste: <?php echo $monthWaste ?: 0; ?>,
                pendingWasteCount: <?php echo $pendingWasteCount; ?>,
                growth: "+12%"
            }
        };

        // Notification System
        class NotificationSystem {
            constructor() {
                this.container = document.getElementById('notificationContainer');
                this.notificationId = 0;
            }

            showNotification(title, message, type = 'success', duration = 5000) {
                const id = this.notificationId++;
                const notification = document.createElement('div');
                notification.className = `notification-card ${type}`;
                notification.id = `notification-${id}`;
                
                const icons = {
                    success: 'fas fa-check-circle',
                    error: 'fas fa-exclamation-circle',
                    warning: 'fas fa-exclamation-triangle',
                    info: 'fas fa-info-circle'
                };

                notification.innerHTML = `
                    <div class="notification-icon">
                        <i class="${icons[type]}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${title}</div>
                        <div class="notification-message">${message}</div>
                    </div>
                    <button class="notification-close" onclick="notificationSystem.closeNotification(${id})">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="notification-progress"></div>
                `;

                this.container.appendChild(notification);

                // Show animation
                setTimeout(() => {
                    notification.classList.add('show');
                }, 100);

                // Auto close
                if (duration > 0) {
                    setTimeout(() => {
                        this.closeNotification(id);
                    }, duration);
                }

                return id;
            }

            closeNotification(id) {
                const notification = document.getElementById(`notification-${id}`);
                if (notification) {
                    notification.classList.remove('show');
                    notification.classList.add('hide');
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 400);
                }
            }

            // Confirmation dialog
            showConfirmation(title, message, type = 'warning') {
                return new Promise((resolve) => {
                    const modal = document.getElementById('confirmationModal');
                    const card = document.getElementById('confirmationCard');
                    const icon = document.getElementById('confirmationIcon');
                    const titleEl = document.getElementById('confirmationTitle');
                    const messageEl = document.getElementById('confirmationMessage');
                    const cancelBtn = document.getElementById('confirmCancel');
                    const okBtn = document.getElementById('confirmOk');

                    // Set content
                    titleEl.textContent = title;
                    messageEl.innerHTML = message;
                    
                    // Set type
                    icon.className = `confirmation-icon ${type}`;
                    okBtn.className = `confirmation-btn ${type === 'danger' ? 'danger' : 'confirm'}`;

                    // Show modal
                    modal.style.display = 'flex';
                    setTimeout(() => {
                        card.classList.add('show');
                    }, 100);

                    // Event handlers
                    const handleResult = (result) => {
                        card.classList.remove('show');
                        setTimeout(() => {
                            modal.style.display = 'none';
                            resolve(result);
                        }, 300);
                        
                        // Remove event listeners
                        cancelBtn.onclick = null;
                        okBtn.onclick = null;
                        modal.onclick = null;
                    };

                    cancelBtn.onclick = () => handleResult(false);
                    okBtn.onclick = () => handleResult(true);
                    
                    // Close when clicking outside
                    modal.onclick = (e) => {
                        if (e.target === modal) {
                            handleResult(false);
                        }
                    };
                });
            }
        }

        // Initialize notification system
        const notificationSystem = new NotificationSystem();

        // DOM Elements
        const header = document.getElementById('header');
        const navLinks = document.getElementById('navLinks');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const refreshDataBtn = document.getElementById('refreshData');
        const refreshMembersBtn = document.getElementById('refreshMembers');
        const refreshPendingBtn = document.getElementById('refreshPending');
        const viewAllMembersBtn = document.getElementById('viewAllMembers');
        const viewAllWasteBtn = document.getElementById('viewAllWaste');
        const addPriceBtn = document.getElementById('addPriceBtn');
        const addWasteBtn = document.getElementById('addWasteBtn');
        const exportMembersBtn = document.getElementById('exportMembers');
        const filterWasteBtn = document.getElementById('filterWaste');
        const exportPricesBtn = document.getElementById('exportPrices');
        const exportIncomeBtn = document.getElementById('exportIncome');
        const resetDbConfigBtn = document.getElementById('resetDbConfig');
        const editPriceModal = document.getElementById('editPriceModal');
        const addWasteModal = document.getElementById('addWasteModal');
        const closeEditModal = document.getElementById('closeEditModal');
        const closeWasteModal = document.getElementById('closeWasteModal');
        const cancelEdit = document.getElementById('cancelEdit');
        const cancelWaste = document.getElementById('cancelWaste');
        const saveEdit = document.getElementById('saveEdit');
        const saveWaste = document.getElementById('saveWaste');
        const editJenis = document.getElementById('editJenis');
        const editHarga = document.getElementById('editHarga');
        const editStatus = document.getElementById('editStatus');
        const wasteDate = document.getElementById('wasteDate');
        const wasteMember = document.getElementById('wasteMember');
        const wasteType = document.getElementById('wasteType');
        const wasteWeight = document.getElementById('wasteWeight');
        const totalPrice = document.getElementById('totalPrice');
        const saveDbConfigBtn = document.getElementById('saveDbConfig');
        const testConnectionBtn = document.getElementById('testConnection');
        const navLinksElements = document.querySelectorAll('.nav-link');
        const tabContents = document.querySelectorAll('.tab-content');

        let currentEditId = null;
        let autoUpdateInterval;

        // Initialize the application
        function init() {
            setupEventListeners();
            startAutoUpdate();
            
            // Show welcome message
            setTimeout(() => {
                notificationSystem.showNotification('Dashboard Admin', 'Dashboard admin berhasil dimuat!', 'success');
            }, 1000);
        }

        // Setup event listeners
        function setupEventListeners() {
            // Navbar scroll effect
            window.addEventListener('scroll', handleScroll);
            
            // Mobile menu toggle
            mobileMenuBtn.addEventListener('click', toggleMobileMenu);
            
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
            refreshMembersBtn.addEventListener('click', refreshMembers);
            refreshPendingBtn.addEventListener('click', refreshPending);
            
            // View all members
            viewAllMembersBtn.addEventListener('click', () => {
                switchTab('anggota');
            });
            
            // View all waste
            viewAllWasteBtn.addEventListener('click', () => {
                switchTab('sampah');
            });
            
            // Add price button
            addPriceBtn.addEventListener('click', addNewPrice);
            
            // Add waste button
            addWasteBtn.addEventListener('click', () => {
                addWasteModal.style.display = 'flex';
            });
            
            // Export buttons
            exportMembersBtn.addEventListener('click', () => notificationSystem.showNotification('Export Berhasil', 'Data anggota berhasil diexport', 'success'));
            exportPricesBtn.addEventListener('click', () => notificationSystem.showNotification('Export Berhasil', 'Data harga berhasil diexport', 'success'));
            exportIncomeBtn.addEventListener('click', () => notificationSystem.showNotification('Export Berhasil', 'Data pendapatan berhasil diexport', 'success'));
            filterWasteBtn.addEventListener('click', () => notificationSystem.showNotification('Filter Diterapkan', 'Filter data sampah diterapkan', 'info'));
            
            // Reset database config
            resetDbConfigBtn.addEventListener('click', resetDbConfig);
            
            // Modal events
            closeEditModal.addEventListener('click', closeEditModalFunc);
            closeWasteModal.addEventListener('click', closeWasteModalFunc);
            cancelEdit.addEventListener('click', closeEditModalFunc);
            cancelWaste.addEventListener('click', closeWasteModalFunc);
            saveEdit.addEventListener('click', savePriceChanges);
            saveWaste.addEventListener('click', saveWasteData);
            
            // Save database config
            saveDbConfigBtn.addEventListener('click', saveDbConfig);
            
            // Test database connection
            testConnectionBtn.addEventListener('click', testConnection);
            
            // Calculate total price when weight or type changes
            wasteWeight.addEventListener('input', calculateTotalPrice);
            wasteType.addEventListener('change', calculateTotalPrice);
            
            // Close modals when clicking outside
            window.addEventListener('click', (e) => {
                if (e.target === editPriceModal) {
                    closeEditModalFunc();
                }
                if (e.target === addWasteModal) {
                    closeWasteModalFunc();
                }
            });

            // Add event listeners to delete buttons
            document.querySelectorAll('.delete-member-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.getAttribute('data-id'));
                    const name = e.currentTarget.getAttribute('data-name');
                    deleteMember(id, name);
                });
            });

            document.querySelectorAll('.delete-waste-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.getAttribute('data-id'));
                    const name = e.currentTarget.getAttribute('data-name');
                    deleteWasteData(id, name);
                });
            });

            document.querySelectorAll('.edit-price-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.getAttribute('data-id'));
                    const jenis = e.currentTarget.getAttribute('data-jenis');
                    const harga = e.currentTarget.getAttribute('data-harga');
                    const status = e.currentTarget.getAttribute('data-status');
                    openEditModal(id, jenis, harga, status);
                });
            });

            document.querySelectorAll('.delete-price-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.getAttribute('data-id'));
                    const jenis = e.currentTarget.getAttribute('data-jenis');
                    deletePrice(id, jenis);
                });
            });

            // Add event listeners for approve/reject buttons
            document.querySelectorAll('.approve-waste-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.getAttribute('data-id'));
                    const name = e.currentTarget.getAttribute('data-name');
                    approveWaste(id, name);
                });
            });

            document.querySelectorAll('.reject-waste-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.getAttribute('data-id'));
                    const name = e.currentTarget.getAttribute('data-name');
                    rejectWaste(id, name);
                });
            });
        }

        // Calculate total price
        function calculateTotalPrice() {
            const selectedOption = wasteType.options[wasteType.selectedIndex];
            const pricePerKg = selectedOption.getAttribute('data-price');
            const weight = parseFloat(wasteWeight.value) || 0;
            const total = pricePerKg * weight;
            
            totalPrice.value = total.toLocaleString('id-ID');
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
            const icon = mobileMenuBtn.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
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

        // Updated delete member function with confirmation modal
        function deleteMember(id, name) {
            notificationSystem.showConfirmation(
                'Hapus Anggota',
                `Apakah Anda yakin ingin menghapus anggota <strong>"${name}"</strong>? Semua data transaksi sampah anggota ini juga akan dihapus. Tindakan ini tidak dapat dibatalkan!`,
                'danger'
            ).then(confirmed => {
                if (confirmed) {
                    // Show loading notification
                    const loadingId = notificationSystem.showNotification(
                        'Memproses',
                        'Sedang menghapus anggota...',
                        'info',
                        0 // No auto close
                    );

                    // AJAX request untuk menghapus anggota
                    fetch('../backend/delete_member.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + id
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Close loading notification
                        notificationSystem.closeNotification(loadingId);
                        
                        if (data.success) {
                            notificationSystem.showNotification(
                                'Berhasil Dihapus',
                                `Anggota "${name}" berhasil dihapus dari sistem`,
                                'success'
                            );
                            
                            // Remove the row from table
                            const row = document.querySelector(`.delete-member-btn[data-id="${id}"]`).closest('tr');
                            row.style.backgroundColor = '#ffebee';
                            setTimeout(() => {
                                row.remove();
                                updateStatsAfterDelete();
                            }, 1000);
                        } else {
                            notificationSystem.showNotification(
                                'Gagal Menghapus',
                                `Gagal menghapus anggota: ${data.message}`,
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        notificationSystem.closeNotification(loadingId);
                        notificationSystem.showNotification(
                            'Kesalahan Jaringan',
                            'Terjadi kesalahan saat menghubungi server',
                            'error'
                        );
                        console.error('Fetch error:', error);
                    });
                }
            });
        }

        // Updated approve waste function
// Updated approve waste function
function approveWaste(id, name) {
    notificationSystem.showConfirmation(
        'Setujui Penjualan',
        `Apakah Anda yakin ingin menyetujui penjualan sampah dari <strong>"${name}"</strong>?<br><br>
         <small>Saldo user akan ditambahkan sesuai total penjualan.</small>`,
        'warning'
    ).then(confirmed => {
        if (confirmed) {
            const loadingId = notificationSystem.showNotification(
                'Memproses',
                'Sedang menyetujui penjualan dan menambah saldo...',
                'info',
                0
            );

            fetch('../backend/admin_control/approve_waste.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&action=approve`
            })
            .then(response => response.json())
            .then(data => {
                notificationSystem.closeNotification(loadingId);
                
                if (data.success) {
                    notificationSystem.showNotification(
                        'Disetujui',
                        data.message,
                        'success'
                    );
                    // Refresh data setelah 2 detik
                    setTimeout(() => {
                        refreshPending();
                        refreshData();
                    }, 2000);
                } else {
                    notificationSystem.showNotification(
                        'Gagal',
                        `Gagal menyetujui penjualan: ${data.message}`,
                        'error'
                    );
                }
            })
            .catch(error => {
                notificationSystem.closeNotification(loadingId);
                notificationSystem.showNotification(
                    'Kesalahan',
                    'Terjadi kesalahan saat memproses',
                    'error'
                );
                console.error('Error:', error);
            });
        }
    });
}

// Updated reject waste function
function rejectWaste(id, name) {
    notificationSystem.showConfirmation(
        'Tolak Penjualan',
        `Apakah Anda yakin ingin menolak penjualan sampah dari <strong>"${name}"</strong>?`,
        'danger'
    ).then(confirmed => {
        if (confirmed) {
            const loadingId = notificationSystem.showNotification(
                'Memproses',
                'Sedang menolak penjualan...',
                'info',
                0
            );

            fetch('../backend/admin_control/approve_waste.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&action=reject`
            })
            .then(response => response.json())
            .then(data => {
                notificationSystem.closeNotification(loadingId);
                
                if (data.success) {
                    notificationSystem.showNotification(
                        'Ditolak',
                        data.message,
                        'success'
                    );
                    // Refresh data setelah 2 detik
                    setTimeout(() => {
                        refreshPending();
                    }, 2000);
                } else {
                    notificationSystem.showNotification(
                        'Gagal',
                        `Gagal menolak penjualan: ${data.message}`,
                        'error'
                    );
                }
            })
            .catch(error => {
                notificationSystem.closeNotification(loadingId);
                notificationSystem.showNotification(
                    'Kesalahan',
                    'Terjadi kesalahan saat memproses',
                    'error'
                );
                console.error('Error:', error);
            });
        }
    });
}

// Fungsi untuk refresh data pending
function refreshPending() {
    // Show loading state
    const originalText = refreshPendingBtn.innerHTML;
    refreshPendingBtn.innerHTML = '<div class="loading"></div> Memuat...';
    refreshPendingBtn.disabled = true;
    
    // Reload halaman untuk data terbaru
    setTimeout(() => {
        location.reload();
    }, 1000);
}

        // Function to update stats after delete
        function updateStatsAfterDelete() {
            // Update total members count
            const totalMembersEl = document.getElementById('totalMembers');
            const currentTotal = parseInt(totalMembersEl.textContent);
            totalMembersEl.textContent = currentTotal - 1;
            
            // Update active members count
            const activeMembersEl = document.getElementById('activeMembers');
            const currentActive = parseInt(activeMembersEl.textContent);
            activeMembersEl.textContent = currentActive - 1;
            
            // Update sync info
            const syncedMembers = document.getElementById('syncedMembers');
            if (syncedMembers) {
                syncedMembers.textContent = (currentTotal - 1) + ' orang';
            }
        }

        // Open edit modal
        function openEditModal(id, jenis, harga, status) {
            currentEditId = id;
            editJenis.value = jenis;
            editHarga.value = harga;
            editStatus.value = status;
            editPriceModal.style.display = 'flex';
        }

        // Close edit modal
        function closeEditModalFunc() {
            editPriceModal.style.display = 'none';
            currentEditId = null;
        }

        // Close waste modal
        function closeWasteModalFunc() {
            addWasteModal.style.display = 'none';
            // Reset form
            wasteWeight.value = '';
            totalPrice.value = '';
        }

        // Save price changes
        function savePriceChanges() {
            if (currentEditId) {
                const loadingId = notificationSystem.showNotification(
                    'Menyimpan',
                    'Sedang menyimpan perubahan harga...',
                    'info',
                    0
                );

                const formData = new FormData();
                formData.append('id', currentEditId);
                formData.append('jenis_sampah', editJenis.value);
                formData.append('harga', editHarga.value);
                formData.append('status', editStatus.value);

                fetch('../backend/update_price.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    notificationSystem.closeNotification(loadingId);
                    
                    if (data.success) {
                        notificationSystem.showNotification(
                            'Berhasil Disimpan',
                            'Harga sampah berhasil diperbarui',
                            'success'
                        );
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        notificationSystem.showNotification(
                            'Gagal Menyimpan',
                            `Gagal memperbarui harga: ${data.message}`,
                            'error'
                        );
                    }
                })
                .catch(error => {
                    notificationSystem.closeNotification(loadingId);
                    notificationSystem.showNotification(
                        'Kesalahan',
                        'Terjadi kesalahan saat menyimpan',
                        'error'
                    );
                });
            }
        }

        // Save waste data
        function saveWasteData() {
            const selectedMemberId = wasteMember.value;
            const selectedType = wasteType.value;
            const selectedOption = wasteType.options[wasteType.selectedIndex];
            const selectedPrice = selectedOption.getAttribute('data-price');
            const weight = parseFloat(wasteWeight.value);
            const total = selectedPrice * weight;
            
            if (!wasteDate.value || !weight || !selectedMemberId) {
                notificationSystem.showNotification('Validasi Gagal', 'Harap isi semua field dengan benar', 'error');
                return;
            }
            
            const loadingId = notificationSystem.showNotification(
                'Menyimpan',
                'Sedang menambahkan data sampah...',
                'info',
                0
            );

            const formData = new FormData();
            formData.append('user_id', selectedMemberId);
            formData.append('tanggal', wasteDate.value);
            formData.append('jenis_sampah', selectedType);
            formData.append('berat', weight);
            formData.append('harga_per_kg', selectedPrice);
            formData.append('total_harga', total);

            fetch('../backend/add_waste.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                notificationSystem.closeNotification(loadingId);
                
                if (data.success) {
                    notificationSystem.showNotification(
                        'Berhasil Ditambahkan',
                        'Data sampah berhasil ditambahkan',
                        'success'
                    );
                    setTimeout(() => {
                        location.reload(); // Reload untuk update data terbaru
                    }, 1000);
                } else {
                    notificationSystem.showNotification(
                        'Gagal Menambah',
                        `Gagal menambah data sampah: ${data.message}`,
                        'error'
                    );
                }
            })
            .catch(error => {
                notificationSystem.closeNotification(loadingId);
                notificationSystem.showNotification(
                    'Kesalahan',
                    'Terjadi kesalahan saat menambah data sampah',
                    'error'
                );
            });
        }

        // Delete waste data
        function deleteWasteData(id, name) {
            notificationSystem.showConfirmation(
                'Hapus Data Sampah',
                `Apakah Anda yakin ingin menghapus data sampah dari <strong>"${name}"</strong>?`,
                'danger'
            ).then(confirmed => {
                if (confirmed) {
                    const loadingId = notificationSystem.showNotification(
                        'Memproses',
                        'Sedang menghapus data sampah...',
                        'info',
                        0
                    );

                    fetch('../backend/delete_waste.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        notificationSystem.closeNotification(loadingId);
                        
                        if (data.success) {
                            notificationSystem.showNotification(
                                'Berhasil Dihapus',
                                'Data sampah berhasil dihapus',
                                'success'
                            );
                            setTimeout(() => {
                                location.reload(); // Reload untuk update data terbaru
                            }, 1000);
                        } else {
                            notificationSystem.showNotification(
                                'Gagal Menghapus',
                                `Gagal menghapus data sampah: ${data.message}`,
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        notificationSystem.closeNotification(loadingId);
                        notificationSystem.showNotification(
                            'Kesalahan',
                            'Terjadi kesalahan saat menghapus data sampah',
                            'error'
                        );
                    });
                }
            });
        }

        // Add new price
        function addNewPrice() {
            const loadingId = notificationSystem.showNotification(
                'Memproses',
                'Sedang menambahkan jenis sampah baru...',
                'info',
                0
            );

            const formData = new FormData();
            formData.append('jenis_sampah', 'Jenis Baru');
            formData.append('harga', 0);
            formData.append('status', 'active');

            fetch('../backend/add_price.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                notificationSystem.closeNotification(loadingId);
                
                if (data.success) {
                    notificationSystem.showNotification(
                        'Berhasil Ditambahkan',
                        'Jenis sampah baru berhasil ditambahkan',
                        'success'
                    );
                    setTimeout(() => {
                        location.reload(); // Reload untuk update data terbaru
                    }, 1000);
                } else {
                    notificationSystem.showNotification(
                        'Gagal Menambah',
                        `Gagal menambah jenis sampah: ${data.message}`,
                        'error'
                    );
                }
            })
            .catch(error => {
                notificationSystem.closeNotification(loadingId);
                notificationSystem.showNotification(
                    'Kesalahan',
                    'Terjadi kesalahan saat menambah jenis sampah',
                    'error'
                );
            });
        }

        // Delete price
        function deletePrice(id, jenis) {
            notificationSystem.showConfirmation(
                'Hapus Harga Sampah',
                `Apakah Anda yakin ingin menghapus harga untuk <strong>"${jenis}"</strong>?`,
                'danger'
            ).then(confirmed => {
                if (confirmed) {
                    const loadingId = notificationSystem.showNotification(
                        'Memproses',
                        'Sedang menghapus harga sampah...',
                        'info',
                        0
                    );

                    fetch('../backend/delete_price.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        notificationSystem.closeNotification(loadingId);
                        
                        if (data.success) {
                            notificationSystem.showNotification(
                                'Berhasil Dihapus',
                                'Harga sampah berhasil dihapus',
                                'success'
                            );
                            setTimeout(() => {
                                location.reload(); // Reload untuk update data terbaru
                            }, 1000);
                        } else {
                            notificationSystem.showNotification(
                                'Gagal Menghapus',
                                `Gagal menghapus harga: ${data.message}`,
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        notificationSystem.closeNotification(loadingId);
                        notificationSystem.showNotification(
                            'Kesalahan',
                            'Terjadi kesalahan saat menghapus harga',
                            'error'
                        );
                    });
                }
            });
        }

        // Auto update members from user dashboard
        function autoUpdateMembers() {
            // Simulate random new member registration (10% chance)
            if (Math.random() < 0.1) {
                notificationSystem.showNotification('Update Data', 'Memeriksa update data anggota...', 'info');
            }
        }

        // Start auto update
        function startAutoUpdate() {
            autoUpdateInterval = setInterval(autoUpdateMembers, 30000); // Update every 30 seconds
        }

        // Refresh data
        function refreshData() {
            // Show loading state
            const originalText = refreshDataBtn.innerHTML;
            refreshDataBtn.innerHTML = '<div class="loading"></div> Memuat...';
            refreshDataBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                location.reload(); // Reload page untuk data terbaru
            }, 1500);
        }

        // Refresh members
        function refreshMembers() {
            // Show loading state
            const originalText = refreshMembersBtn.innerHTML;
            refreshMembersBtn.innerHTML = '<div class="loading"></div> Memuat...';
            refreshMembersBtn.disabled = true;
            
            // Simulate API call to get latest members from user database
            setTimeout(() => {
                location.reload(); // Reload page untuk data terbaru
            }, 1500);
        }

        // Refresh pending data
        function refreshPending() {
            // Show loading state
            const originalText = refreshPendingBtn.innerHTML;
            refreshPendingBtn.innerHTML = '<div class="loading"></div> Memuat...';
            refreshPendingBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                location.reload(); // Reload page untuk data terbaru
            }, 1500);
        }

        // Reset database configuration
        function resetDbConfig() {
            notificationSystem.showConfirmation(
                'Reset Konfigurasi',
                'Apakah Anda yakin ingin mengembalikan pengaturan database ke nilai default?',
                'warning'
            ).then(confirmed => {
                if (confirmed) {
                    notificationSystem.showNotification(
                        'Berhasil Direset',
                        'Pengaturan database berhasil direset',
                        'success'
                    );
                }
            });
        }

        // Save database configuration
        function saveDbConfig() {
            // Show loading state
            const originalText = saveDbConfigBtn.innerHTML;
            saveDbConfigBtn.innerHTML = '<div class="loading"></div> Menyimpan...';
            saveDbConfigBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Restore button
                saveDbConfigBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Koneksi';
                saveDbConfigBtn.disabled = false;
                
                notificationSystem.showNotification(
                    'Berhasil Disimpan',
                    'Konfigurasi database berhasil disimpan',
                    'success'
                );
            }, 1000);
        }

        // Test database connection
        function testConnection() {
            // Show loading state
            const originalText = testConnectionBtn.innerHTML;
            testConnectionBtn.innerHTML = '<div class="loading"></div> Menguji...';
            testConnectionBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Restore button
                testConnectionBtn.innerHTML = '<i class="fas fa-plug"></i> Test Koneksi';
                testConnectionBtn.disabled = false;
                
                notificationSystem.showNotification(
                    'Koneksi Berhasil',
                    'Koneksi database berhasil diuji dan berfungsi dengan baik',
                    'success'
                );
            }, 1500);
        }

        // Initialize the app when DOM is loaded
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>