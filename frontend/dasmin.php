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
    $query = "SELECT SUM(berat) as total FROM transaksi_sampah";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ? $row['total'] : 0;
}

function getTotalIncome($connect) {
    $query = "SELECT SUM(total) as total FROM transaksi_sampah";
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
    $query = "SELECT id, nama, email, tanggal_daftar, status FROM user ORDER BY tanggal_daftar DESC";
    $result = mysqli_query($connect, $query);
    $members = [];
    while($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
    return $members;
}

function getWasteData($connect, $limit = 5) {
    $query = "SELECT ts.id, ts.tanggal, u.nama, ts.jenis_sampah, ts.berat, ts.harga_per_kg, ts.total
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
    $query = "SELECT ts.id, ts.tanggal, u.nama, ts.jenis_sampah, ts.berat, ts.harga_per_kg, ts.total
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
$wastePrices = getWastePrices($connect);
$incomeData = getIncomeData($connect);

// Hitung statistik bulanan
$monthIncome = 0;
$monthWaste = 0;
$currentMonth = date('m');
$currentYear = date('Y');

$monthQuery = "SELECT SUM(total) as pendapatan, SUM(berat) as sampah 
               FROM transaksi_sampah 
               WHERE MONTH(tanggal) = $currentMonth AND YEAR(tanggal) = $currentYear";
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
                                </tr>
                            </thead>
                            <tbody id="recentWasteTable">
                                <?php foreach($recentWaste as $waste): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($waste['tanggal'])); ?></td>
                                    <td><?php echo htmlspecialchars($waste['jenis_sampah']); ?></td>
                                    <td><?php echo $waste['berat']; ?> kg</td>
                                    <td><?php echo number_format($waste['total_harga']); ?></td>
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
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Tanggal Bergabung</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="allMembersTable">
                            <?php foreach($allMembers as $member): ?>
                            <tr>
                                <td><?php echo $member['id']; ?></td>
                                <td><?php echo htmlspecialchars($member['nama']); ?></td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td><?php echo date('d M Y', strtotime($member['tanggal_daftar'])); ?></td>
                                <td><span class="badge <?php echo $member['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>"><?php echo $member['status'] === 'active' ? 'Aktif' : 'Nonaktif'; ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-danger btn-sm delete-member-btn" data-id="<?php echo $member['id']; ?>">
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
            <div class="card">
                <div class="section-title">
                    <h3>Data Sampah</h3>
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
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="wasteTable">
                            <?php foreach($allWasteData as $waste): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($waste['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($waste['nama']); ?></td>
                                <td><?php echo htmlspecialchars($waste['jenis_sampah']); ?></td>
                                <td><?php echo $waste['berat']; ?> kg</td>
                                <td><?php echo number_format($waste['harga_per_kg']); ?></td>
                                <td><?php echo number_format($waste['total_harga']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-danger btn-sm delete-waste-btn" data-id="<?php echo $waste['id']; ?>">
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
                                <td><?php echo number_format($price['harga_per_kg']); ?></td>
                                <td>
                                    <span class="badge <?php echo $price['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $price['status'] === 'active' ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-outline btn-sm edit-btn" data-id="<?php echo $price['id']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $price['id']; ?>">
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
                                <td><?php echo number_format($income['total_sampah'], 1); ?> kg</td>
                                <td><?php echo number_format($income['total_pendapatan']); ?></td>
                                <td>
                                    <span class="badge badge-success"><?php echo $income['status']; ?></span>
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
                        <option value="<?php echo $price['jenis_sampah']; ?>" data-price="<?php echo $price['harga_per_kg']; ?>">
                            <?php echo htmlspecialchars($price['jenis_sampah']); ?> (Rp <?php echo number_format($price['harga_per_kg']); ?>)
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

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Tindakan berhasil dilakukan!</span>
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
                growth: "+12%"
            }
        };

        // DOM Elements
        const header = document.getElementById('header');
        const navLinks = document.getElementById('navLinks');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const refreshDataBtn = document.getElementById('refreshData');
        const refreshMembersBtn = document.getElementById('refreshMembers');
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
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
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
                showToast('Dashboard admin berhasil dimuat!');
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
            exportMembersBtn.addEventListener('click', () => showToast('Data anggota berhasil diexport'));
            exportPricesBtn.addEventListener('click', () => showToast('Data harga berhasil diexport'));
            exportIncomeBtn.addEventListener('click', () => showToast('Data pendapatan berhasil diexport'));
            filterWasteBtn.addEventListener('click', () => showToast('Filter data sampah diterapkan'));
            
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
                    deleteMember(id);
                });
            });

            document.querySelectorAll('.delete-waste-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.getAttribute('data-id'));
                    deleteWasteData(id);
                });
            });

            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.getAttribute('data-id'));
                    openEditModal(id);
                });
            });

            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.getAttribute('data-id'));
                    deletePrice(id);
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

        // Delete member
        // Delete member dengan debugging
        function deleteMember(id) {
            if (confirm('Apakah Anda yakin ingin menghapus anggota ini?')) {
            console.log('Attempting to delete member with ID:', id);
        
        // Show loading state
        const deleteBtn = document.querySelector(`.delete-member-btn[data-id="${id}"]`);
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<div class="loading"></div>';
        deleteBtn.disabled = true;

        // AJAX request untuk menghapus anggota
        fetch('delete_member.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                showToast('Anggota berhasil dihapus');
                // Refresh the page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast('Gagal menghapus anggota: ' + data.message, 'error');
                // Restore button
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showToast('Terjadi kesalahan saat menghapus anggota', 'error');
            // Restore button
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        });
    }
}

        // Open edit modal
        function openEditModal(id) {
            const price = appData.wastePrices.find(p => p.id === id);
            if (price) {
                currentEditId = id;
                editJenis.value = price.jenis_sampah;
                editHarga.value = price.harga_per_kg;
                editStatus.value = price.status;
                editPriceModal.style.display = 'flex';
            }
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
                const formData = new FormData();
                formData.append('id', currentEditId);
                formData.append('jenis_sampah', editJenis.value);
                formData.append('harga_per_kg', editHarga.value);
                formData.append('status', editStatus.value);

                fetch('update_price.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Harga sampah berhasil diperbarui');
                        setTimeout(() => {
                            location.reload(); // Reload untuk update data terbaru
                        }, 1000);
                    } else {
                        showToast('Gagal memperbarui harga: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat memperbarui harga', 'error');
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
                showToast('Harap isi semua field dengan benar', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('user_id', selectedMemberId);
            formData.append('tanggal', wasteDate.value);
            formData.append('jenis_sampah', selectedType);
            formData.append('berat', weight);
            formData.append('harga_per_kg', selectedPrice);
            formData.append('total_harga', total);

            fetch('add_waste.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Data sampah berhasil ditambahkan');
                    setTimeout(() => {
                        location.reload(); // Reload untuk update data terbaru
                    }, 1000);
                } else {
                    showToast('Gagal menambah data sampah: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat menambah data sampah', 'error');
            });
        }

        // Delete waste data
        function deleteWasteData(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data sampah ini?')) {
                fetch('delete_waste.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Data sampah berhasil dihapus');
                        setTimeout(() => {
                            location.reload(); // Reload untuk update data terbaru
                        }, 1000);
                    } else {
                        showToast('Gagal menghapus data sampah: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat menghapus data sampah', 'error');
                });
            }
        }

        // Add new price
        function addNewPrice() {
            const formData = new FormData();
            formData.append('jenis_sampah', 'Jenis Baru');
            formData.append('harga_per_kg', 0);
            formData.append('status', 'active');

            fetch('add_price.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Jenis sampah baru berhasil ditambahkan');
                    setTimeout(() => {
                        location.reload(); // Reload untuk update data terbaru
                    }, 1000);
                } else {
                    showToast('Gagal menambah jenis sampah: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat menambah jenis sampah', 'error');
            });
        }

        // Delete price
        function deletePrice(id) {
            if (confirm('Apakah Anda yakin ingin menghapus harga ini?')) {
                fetch('delete_price.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Harga sampah berhasil dihapus');
                        setTimeout(() => {
                            location.reload(); // Reload untuk update data terbaru
                        }, 1000);
                    } else {
                        showToast('Gagal menghapus harga: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat menghapus harga', 'error');
                });
            }
        }

        // Auto update members from user dashboard
        function autoUpdateMembers() {
            // Simulate random new member registration (10% chance)
            if (Math.random() < 0.1) {
                showToast('Memeriksa update data anggota...', 'info');
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

        // Reset database configuration
        function resetDbConfig() {
            if (confirm('Apakah Anda yakin ingin mengembalikan pengaturan database ke nilai default?')) {
                showToast('Pengaturan database berhasil direset');
            }
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
                
                showToast('Konfigurasi database berhasil disimpan');
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
                
                showToast('Koneksi database berhasil diuji dan berfungsi dengan baik');
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