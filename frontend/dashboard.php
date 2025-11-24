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

// Ambil statistik user
$stats_query = mysqli_prepare($connect, "
    SELECT 
        COALESCE(SUM(berat), 0) as berat,
        COALESCE(SUM(harga_per_kg), 0) as harga_per_kg,
        COALESCE(SUM(harga_per_kg), 0) as harga_per_kg,
        COUNT(*) as total
    FROM transaksi_sampah
    WHERE id_anggota = ?
");
mysqli_stmt_bind_param($stats_query, "i", $user_id);
mysqli_stmt_execute($stats_query);
mysqli_stmt_bind_result($stats_query, $total_berat, $total_nilai, $total_poin, $total_transaksi);
mysqli_stmt_fetch($stats_query);
mysqli_stmt_close($stats_query);

// Ambil transaksi terbaru user
$transaksi_query = mysqli_prepare($connect, "
    SELECT tanggal, jenis_sampah, berat, status 
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
mysqli_stmt_close($transaksi_query);

// Format currency
function format_currency($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Get initials for avatar
function get_initials($name) {
    $names = explode(' ', $name);
    $initials = '';
    foreach($names as $name) {
        $initials .= strtoupper(substr($name, 0, 1));
    }
    return substr($initials, 0, 2);
}
?>

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
            
            .profile-header {
                flex-direction: column;
                text-align: center;
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
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
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
                <a href="#" class="nav-link" data-tab="transaksi"><i class="fas fa-exchange-alt"></i> Transaksi</a>
                <a href="#" class="nav-link" data-tab="tabungan"><i class="fas fa-wallet"></i> Tabungan</a>
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
                    <div class="stat-value" id="totalPoints"><?php echo number_format($total_poin); ?></div>
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
                                        <td><?php echo $transaksi['poin']; ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                if($transaksi['status'] == 'selesai') echo 'badge-success';
                                                elseif($transaksi['status'] == 'proses') echo 'badge-warning';
                                                else echo 'badge-danger';
                                            ?>">
                                                <?php echo ucfirst($transaksi['status']); ?>
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
                $target_bulanan = 50; // Target dalam Kg
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
                        <div class="stat-value"><?php echo number_format($total_poin); ?></div>
                        <div class="stat-label">Total Poin</div>
                    </div>
                </div>
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
                                <th>Nilai (Rp)</th>
                                <th>Poin</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="allTransactionsTable">
                            <?php if(empty($transaksi_data)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #777;">Belum ada transaksi</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($transaksi_data as $transaksi): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($transaksi['tanggal'])); ?></td>
                                    <td><?php echo htmlspecialchars($transaksi['jenis_sampah']); ?></td>
                                    <td><?php echo number_format($transaksi['berat'], 1); ?></td>
                                    <td><?php echo format_currency($transaksi['berat'] * 2000); ?></td>
                                    <td><?php echo $transaksi['poin']; ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            if($transaksi['status'] == 'selesai') echo 'badge-success';
                                            elseif($transaksi['status'] == 'proses') echo 'badge-warning';
                                            else echo 'badge-danger';
                                        ?>">
                                            <?php echo ucfirst($transaksi['status']); ?>
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

        <!-- Tabungan Tab -->
        <div class="tab-content" id="tabunganTab">
            <div class="card">
                <div class="section-title">
                    <h3>Ringkasan Tabungan</h3>
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
                        <div class="stat-value"><?php echo format_currency($total_nilai * 0.3); ?></div>
                        <div class="stat-label">Tabungan Bulan Ini</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-weight-hanging"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($total_berat * 0.3, 1); ?> kg</div>
                        <div class="stat-label">Sampah Bulan Ini</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value">+8%</div>
                        <div class="stat-label">Pertumbuhan</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">
                    <h3>Riwayat Tabungan</h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Total Sampah (kg)</th>
                                <th>Tabungan (Rp)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Juni 2023</td>
                                <td><?php echo number_format($total_berat * 0.3, 1); ?></td>
                                <td><?php echo format_currency($total_nilai * 0.3); ?></td>
                                <td><span class="badge badge-success">Aktif</span></td>
                            </tr>
                            <tr>
                                <td>Mei 2023</td>
                                <td>28.5</td>
                                <td>Rp 85.500</td>
                                <td><span class="badge badge-success">Aktif</span></td>
                            </tr>
                            <tr>
                                <td>April 2023</td>
                                <td>32.1</td>
                                <td>Rp 96.300</td>
                                <td><span class="badge badge-success">Aktif</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Reward Tab -->
        <div class="tab-content" id="rewardTab">
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
                        <div class="stat-value"><?php echo number_format($total_poin); ?></div>
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
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h3>Voucher Belanja</h3>
                        <p>100 Poin</p>
                    </div>
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-mug-hot"></i>
                        </div>
                        <h3>Voucher Kopi</h3>
                        <p>50 Poin</p>
                    </div>
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-film"></i>
                        </div>
                        <h3>Voucher Bioskop</h3>
                        <p>150 Poin</p>
                    </div>
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h3>Voucher Restoran</h3>
                        <p>200 Poin</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
        const refreshDataBtn = document.getElementById('refreshData');
        const viewAllTransactionsBtn = document.getElementById('viewAllTransactions');
        const filterTransactionsBtn = document.getElementById('filterTransactions');
        const exportTransactionsBtn = document.getElementById('exportTransactions');
        const redeemRewardBtn = document.getElementById('redeemReward');
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        const navLinksElements = document.querySelectorAll('.nav-link');
        const tabContents = document.querySelectorAll('.tab-content');

        // Quick action elements
        const schedulePickupBtn = document.getElementById('schedulePickup');
        const viewHistoryBtn = document.getElementById('viewHistory');
        const redeemPointsBtn = document.getElementById('redeemPoints');
        const monthlyReportBtn = document.getElementById('monthlyReport');

        // Initialize the application
        function init() {
            setupEventListeners();
            updateGreeting();
            
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
            
            // Redeem reward
            redeemRewardBtn.addEventListener('click', () => {
                showToast('Poin reward berhasil ditukar');
            });
            
            // Quick actions
            schedulePickupBtn.addEventListener('click', () => {
                showToast('Jadwal penjemputan berhasil dibuat');
            });
            
            viewHistoryBtn.addEventListener('click', () => {
                switchTab('transaksi');
            });
            
            redeemPointsBtn.addEventListener('click', () => {
                switchTab('reward');
            });
            
            monthlyReportBtn.addEventListener('click', () => {
                showToast('Laporan bulanan berhasil di-generate');
            });
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
            refreshDataBtn.innerHTML = '<div class="loading"></div> Memuat...';
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