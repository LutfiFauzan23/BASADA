<?php
session_start();
include '../backend/connect.php'; // Include koneksi database

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

// Ambil statistik sampah user
$stats_query = mysqli_prepare($connect, "
    SELECT 
        COALESCE(SUM(berat), 0) as total_berat,
        COALESCE(SUM(nilai), 0) as total_nilai,
        COALESCE(SUM(poin), 0) as total_poin,
        COUNT(*) as total_transaksi
    FROM transaksi 
    WHERE user_id = ?
");
mysqli_stmt_bind_param($stats_query, "i", $user_id);
mysqli_stmt_execute($stats_query);
mysqli_stmt_bind_result($stats_query, $total_berat, $total_nilai, $total_poin, $total_transaksi);
mysqli_stmt_fetch($stats_query);
mysqli_stmt_close($stats_query);

// Ambil transaksi terbaru
$transaksi_query = mysqli_prepare($connect, "
    SELECT tanggal, jenis_sampah, berat, poin, status 
    FROM transaksi 
    WHERE user_id = ? 
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Bank Sampah Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS tetap sama seperti sebelumnya */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #2ecc71;
            --secondary: #27ae60;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --accent: #e74c3c;
            --sidebar-width: 250px;
            --primary-dark: #1b5e20;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }

        .container {
            display: flex;
            width: 100%;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-dark);
            color: white;
            height: 100vh;
            position: fixed;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header i {
            font-size: 24px;
            color: var(--primary);
        }

        .sidebar-header h2 {
            font-size: 20px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            color: white;
            border-left: 4px solid transparent;
        }

        .menu-item:hover, .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        .menu-item.active {
            border-left: 4px solid var(--primary);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid #e0e0e0;
        }

        .page-title h1 {
            font-size: 28px;
            color: var(--dark);
        }

        .page-title p {
            color: #777;
            margin-top: 5px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark);
        }

        .user-role {
            font-size: 14px;
            color: #777;
        }

        /* Dashboard Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-icon.sampah {
            background-color: var(--primary);
        }

        .stat-icon.saldo {
            background-color: #3498db;
        }

        .stat-icon.poin {
            background-color: #9b59b6;
        }

        .stat-icon.transaksi {
            background-color: #e67e22;
        }

        .stat-info h3 {
            font-size: 14px;
            color: #777;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--dark);
        }

        /* Content Sections */
        .content-section {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 20px;
            color: var(--dark);
        }

        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 14px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #f8f9fa;
            color: var(--dark);
            font-weight: 600;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status.selesai {
            background-color: #e8f6ef;
            color: var(--secondary);
        }

        .status.proses {
            background-color: #fff4e6;
            color: #e67e22;
        }

        .status.dijadwalkan {
            background-color: #e6f2ff;
            color: #3498db;
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
        }

        .action-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: all 0.3s;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--light);
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

        /* Page Content */
        .page-content {
            display: none;
        }

        .page-content.active {
            display: block;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
        }

        /* Profile Styles */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 36px;
        }

        .profile-info h2 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .profile-info p {
            color: #777;
        }

        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--dark);
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-header h2, .menu-item span {
                display: none;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .sidebar:hover {
                width: var(--sidebar-width);
            }
            
            .sidebar:hover .sidebar-header h2,
            .sidebar:hover .menu-item span {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block;
            }
            
            .sidebar.active {
                width: var(--sidebar-width);
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .user-info {
                align-self: flex-end;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .content-section {
                padding: 15px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-recycle"></i>
                <h2>Bank Sampah Digital</h2>
            </div>
            <div class="sidebar-menu">
                <a href="#" class="menu-item active" data-page="dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="menu-item" data-page="profile">
                    <i class="fas fa-user"></i>
                    <span>Profil Saya</span>
                </a>
                <a href="#" class="menu-item" data-page="sampah">
                    <i class="fas fa-trash-alt"></i>
                    <span>Kelola Sampah</span>
                </a>
                <a href="#" class="menu-item" data-page="riwayat">
                    <i class="fas fa-history"></i>
                    <span>Riwayat Transaksi</span>
                </a>
                <a href="#" class="menu-item" data-page="tabungan">
                    <i class="fas fa-wallet"></i>
                    <span>Tabungan Sampah</span>
                </a>
                <a href="#" class="menu-item" data-page="jadwal">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Jadwal Penjemputan</span>
                </a>
                <a href="#" class="menu-item" data-page="pengaturan">
                    <i class="fas fa-cog"></i>
                    <span>Pengaturan</span>
                </a>
                <a href="#" class="menu-item" data-page="logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>Dashboard</h1>
                    <p>Selamat datang di dashboard Bank Sampah Digital</p>
                </div>
                <div class="user-info">
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($nama_lengkap); ?></div>
                        <div class="user-role">Anggota Aktif</div>
                    </div>
                    <div class="user-avatar">
                        <?php 
                        // Ambil inisial dari nama
                        $names = explode(' ', $nama_lengkap);
                        $initials = '';
                        foreach($names as $name) {
                            $initials .= strtoupper(substr($name, 0, 1));
                        }
                        echo substr($initials, 0, 2);
                        ?>
                    </div>
                    <div class="mobile-toggle" id="mobileToggle">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
            </div>

            <!-- Dashboard Page -->
            <div class="page-content active" id="dashboard-page">
                <!-- Stats Section -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon sampah">
                            <i class="fas fa-trash-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Sampah Terkumpul</h3>
                            <div class="stat-value"><?php echo number_format($total_berat, 1); ?> Kg</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon saldo">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Saldo Tabungan</h3>
                            <div class="stat-value"><?php echo format_currency($total_nilai); ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon poin">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Poin Reward</h3>
                            <div class="stat-value"><?php echo number_format($total_poin); ?> Poin</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon transaksi">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Transaksi</h3>
                            <div class="stat-value"><?php echo $total_transaksi; ?> Kali</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Aksi Cepat</h2>
                    </div>
                    <div class="quick-actions">
                        <div class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <h3>Jadwalkan Penjemputan</h3>
                            <p>Atur jadwal penjemputan sampah</p>
                        </div>
                        <div class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <h3>Lihat Riwayat</h3>
                            <p>Cek riwayat transaksi terbaru</p>
                        </div>
                        <div class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-gift"></i>
                            </div>
                            <h3>Tukar Poin</h3>
                            <p>Tukar poin dengan reward menarik</p>
                        </div>
                        <div class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3>Laporan Bulanan</h3>
                            <p>Lihat laporan aktivitas sampah</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Transaksi Terbaru</h2>
                        <button class="btn btn-outline">Lihat Semua</button>
                    </div>
                    <div class="table-responsive">
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
                            <tbody>
                                <?php if(empty($transaksi_data)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: #777;">Belum ada transaksi</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($transaksi_data as $transaksi): ?>
                                    <tr>
                                        <td><?php echo date('d M Y', strtotime($transaksi['tanggal'])); ?></td>
                                        <td><?php echo htmlspecialchars($transaksi['jenis_sampah']); ?></td>
                                        <td><?php echo number_format($transaksi['berat'], 1); ?> Kg</td>
                                        <td><?php echo $transaksi['poin']; ?></td>
                                        <td>
                                            <span class="status <?php echo strtolower($transaksi['status']); ?>">
                                                <?php echo $transaksi['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Progress Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Target Bulanan</h2>
                    </div>
                    <?php
                    $target_bulanan = 150; // Target dalam Kg
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

            <!-- Profile Page -->
            <div class="page-content" id="profile-page">
                <div class="content-section">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php 
                            $names = explode(' ', $nama_lengkap);
                            $initials = '';
                            foreach($names as $name) {
                                $initials .= strtoupper(substr($name, 0, 1));
                            }
                            echo substr($initials, 0, 2);
                            ?>
                        </div>
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($nama_lengkap); ?></h2>
                            <p>Anggota Aktif</p>
                        </div>
                    </div>

                    <div class="section-header">
                        <h2 class="section-title">Informasi Pribadi</h2>
                        <button class="btn btn-primary">Edit Profil</button>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($nama_lengkap); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($alamat_email); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($nomor_telepon); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" rows="3" readonly><?php echo htmlspecialchars($alamat); ?></textarea>
                    </div>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Statistik Sampah</h2>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon sampah">
                                <i class="fas fa-trash-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3>Total Sampah</h3>
                                <div class="stat-value"><?php echo number_format($total_berat, 1); ?> Kg</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon saldo">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="stat-info">
                                <h3>Total Tabungan</h3>
                                <div class="stat-value"><?php echo format_currency($total_nilai); ?></div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon poin">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-info">
                                <h3>Total Poin</h3>
                                <div class="stat-value"><?php echo number_format($total_poin); ?> Poin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Halaman lainnya tetap sama, tapi akan menampilkan data real -->
            <!-- ... kode untuk halaman lainnya ... -->

            <div class="page-content" id="logout-page">
                <div class="content-section">
                    <h2 class="section-title">Konfirmasi Keluar</h2>
                    <p>Apakah Anda yakin ingin keluar dari akun Anda?</p>
                    <div style="margin-top: 20px;">
                        <button class="btn btn-primary" id="confirmLogout">Ya, Keluar</button>
                        <button class="btn btn-outline" id="cancelLogout">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        document.getElementById('mobileToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Navigation system
        const menuItems = document.querySelectorAll('.menu-item');
        const pageContents = document.querySelectorAll('.page-content');
        
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Get the target page from data attribute
                const targetPage = this.getAttribute('data-page');
                
                // Handle logout separately
                if (targetPage === 'logout') {
                    showPage('logout-page');
                    // Update active menu
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    return;
                }
                
                // Update active menu
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                
                // Show the target page
                showPage(`${targetPage}-page`);
                
                // Update page title based on active page
                updatePageTitle(this.querySelector('span').textContent);
                
                // Close sidebar on mobile after selection
                if (window.innerWidth <= 768) {
                    document.getElementById('sidebar').classList.remove('active');
                }
            });
        });
        
        function showPage(pageId) {
            // Hide all pages
            pageContents.forEach(page => {
                page.classList.remove('active');
            });
            
            // Show the target page
            document.getElementById(pageId).classList.add('active');
        }
        
        function updatePageTitle(pageName) {
            const pageTitle = document.querySelector('.page-title h1');
            const pageSubtitle = document.querySelector('.page-title p');
            
            pageTitle.textContent = pageName;
            
            // Update subtitle based on page
            switch(pageName) {
                case 'Dashboard':
                    pageSubtitle.textContent = 'Selamat datang di dashboard Bank Sampah Digital';
                    break;
                case 'Profil Saya':
                    pageSubtitle.textContent = 'Kelola informasi profil Anda';
                    break;
                case 'Kelola Sampah':
                    pageSubtitle.textContent = 'Kelola sampah yang akan disetor';
                    break;
                case 'Riwayat Transaksi':
                    pageSubtitle.textContent = 'Lihat riwayat transaksi sampah Anda';
                    break;
                default:
                    pageSubtitle.textContent = `Halaman ${pageName}`;
            }
        }

        // Logout functionality
        document.getElementById('confirmLogout').addEventListener('click', function() {
            window.location.href = '../backend/logout.php';
        });
        
        document.getElementById('cancelLogout').addEventListener('click', function() {
            // Go back to dashboard
            menuItems.forEach(i => i.classList.remove('active'));
            document.querySelector('[data-page="dashboard"]').classList.add('active');
            showPage('dashboard-page');
            updatePageTitle('Dashboard');
        });

        // Quick action cards functionality
        const actionCards = document.querySelectorAll('.action-card');
        actionCards.forEach(card => {
            card.addEventListener('click', function() {
                const title = this.querySelector('h3').textContent;
                alert(`Anda memilih: ${title}`);
            });
        });

        // Update greeting based on time of day
        window.addEventListener('DOMContentLoaded', function() {
            const hour = new Date().getHours();
            let greeting = "Selamat datang";
            
            if (hour < 12) greeting = "Selamat pagi";
            else if (hour < 15) greeting = "Selamat siang";
            else if (hour < 19) greeting = "Selamat sore";
            else greeting = "Selamat malam";
            
            // Only update if we're on dashboard
            if (document.getElementById('dashboard-page').classList.contains('active')) {
                document.querySelector('.page-title h1').textContent = `${greeting}, <?php echo explode(' ', $nama_lengkap)[0]; ?>!`;
            }
        });
    </script>
</body>
</html>