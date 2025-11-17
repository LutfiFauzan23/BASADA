<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - BASADA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2e7d32;
            --primary-light: #4caf50;
            --primary-dark: #1b5e20;
            --secondary-color: #f5f5f5;
            --accent-color: #ff9800;
            --text-dark: #333;
            --text-light: #fff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f9f9f9 0%, #e8f5e9 100%);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: linear-gradient(to bottom, var(--primary-color), var(--primary-dark));
            color: var(--text-light);
            height: 100vh;
            position: fixed;
            transition: var(--transition);
            box-shadow: var(--shadow);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .sidebar-menu ul {
            list-style: none;
        }

        .sidebar-menu li {
            padding: 12px 20px;
            transition: var(--transition);
            cursor: pointer;
        }

        .sidebar-menu li:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu li.active {
            background-color: var(--primary-dark);
            border-left: 4px solid var(--accent-color);
        }

        .sidebar-menu a {
            color: var(--text-light);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar-menu i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: var(--transition);
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(to right, var(--primary-color), var(--primary-light));
            color: var(--text-light);
            padding: 15px 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }

        .navbar h1 {
            font-size: 1.5rem;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            cursor: pointer;
            position: relative;
        }

        .navbar-user img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid var(--text-light);
        }

        .user-dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            width: 200px;
            display: none;
            z-index: 100;
        }

        .user-dropdown.active {
            display: block;
        }

        .user-dropdown a {
            display: block;
            padding: 12px 15px;
            color: var(--text-dark);
            text-decoration: none;
            transition: var(--transition);
        }

        .user-dropdown a:hover {
            background-color: #f5f5f5;
        }

        .user-dropdown a i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        /* Cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-top: 4px solid var(--primary-color);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-header h3 {
            font-size: 1rem;
            color: var(--text-dark);
        }

        .card-header i {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .card-body h2 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .card-body p {
            font-size: 0.9rem;
            color: #666;
        }

        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .content-section:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .section-header h2 {
            font-size: 1.3rem;
            color: var(--primary-color);
        }

        .btn {
            background: linear-gradient(to right, var(--primary-color), var(--primary-light));
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }

        .btn:hover {
            background: linear-gradient(to right, var(--primary-dark), var(--primary-color));
            transform: translateY(-2px);
        }

        .btn i {
            margin-right: 5px;
        }

        .btn-small {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .btn-danger {
            background: linear-gradient(to right, #d32f2f, #f44336);
        }

        .btn-warning {
            background: linear-gradient(to right, #ff9800, #ffb74d);
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background-color: #f5f5f5;
            color: var(--text-dark);
            font-weight: 600;
        }

        table tr {
            transition: var(--transition);
        }

        table tr:hover {
            background-color: #f9f9f9;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-pending {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        /* Profile Section */
        .profile-info {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-right: 30px;
            border: 5px solid var(--primary-light);
            box-shadow: var(--shadow);
            cursor: pointer;
            transition: var(--transition);
        }

        .profile-img:hover {
            transform: scale(1.05);
        }

        .profile-details h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .profile-details p {
            margin-bottom: 5px;
            color: #666;
            display: flex;
            align-items: center;
        }

        .profile-details i {
            margin-right: 10px;
            color: var(--primary-color);
            width: 20px;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: linear-gradient(to bottom right, #f5f5f5, #e8f5e9);
            border-radius: 8px;
            transition: var(--transition);
        }

        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }

        /* Chart Container */
        .chart-container {
            height: 300px;
            margin-top: 20px;
        }

        /* Modal */
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
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            width: 500px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.3s;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            color: var(--primary-color);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(46, 125, 50, 0.2);
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 3px solid transparent;
        }

        .tab.active {
            border-bottom: 3px solid var(--primary-color);
            color: var(--primary-color);
            font-weight: 600;
        }

        .tab:hover {
            background-color: #f5f5f5;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Search and Filter */
        .search-filter {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 15px;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .filter-box select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            z-index: 3000;
            transform: translateX(150%);
            transition: transform 0.3s ease;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            border-left: 4px solid var(--primary-color);
        }

        .notification.error {
            border-left: 4px solid #f44336;
        }

        .notification i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .notification.success i {
            color: var(--primary-color);
        }

        .notification.error i {
            color: #f44336;
        }

        /* Settings Section */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .setting-group {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .setting-group h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }

        .setting-group h3 i {
            margin-right: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                text-align: center;
            }
            
            .sidebar-header h2, .sidebar-header p, .sidebar-menu span {
                display: none;
            }
            
            .sidebar-menu i {
                margin-right: 0;
                font-size: 1.5rem;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .cards {
                grid-template-columns: 1fr;
            }
            
            .profile-info {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-img {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .profile-stats {
                grid-template-columns: 1fr;
            }
            
            .search-filter {
                flex-direction: column;
            }
            
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Add Transaction Modal -->
<div class="modal" id="addTransactionModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Transaksi Baru</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="transactionMember">Anggota</label>
                <select id="transactionMember" class="form-control">
                    <option value="">Pilih Anggota</option>
                </select>
            </div>
            <div class="form-group">
                <label for="transactionWasteType">Jenis Sampah</label>
                <select id="transactionWasteType" class="form-control">
                    <option value="">Pilih Jenis Sampah</option>
                    <option value="1">Plastik PET (Rp 10.000/kg)</option>
                    <option value="2">Kertas Koran (Rp 5.000/kg)</option>
                    <option value="3">Kaleng Aluminium (Rp 20.000/kg)</option>
                    <option value="4">Botol Kaca (Rp 5.000/kg)</option>
                    <option value="5">Plastik HDPE (Rp 10.000/kg)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="transactionWeight">Berat (kg)</label>
                <input type="number" id="transactionWeight" class="form-control" step="0.1" min="0" placeholder="Masukkan berat sampah">
            </div>
            <div class="form-group">
                <label for="transactionDate">Tanggal</label>
                <input type="date" id="transactionDate" class="form-control">
            </div>
            <div class="form-group">
                <label for="transactionTotal">Total (Rp)</label>
                <input type="text" id="transactionTotal" class="form-control" readonly>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" id="saveTransactionBtn"><i class="fas fa-save"></i> Simpan Transaksi</button>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal" id="addMemberModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Anggota Baru</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="memberName">Nama Lengkap</label>
                <input type="text" id="memberName" class="form-control" placeholder="Masukkan nama lengkap">
            </div>
            <div class="form-group">
                <label for="memberEmail">Email</label>
                <input type="email" id="memberEmail" class="form-control" placeholder="Masukkan alamat email">
            </div>
            <div class="form-group">
                <label for="memberPhone">Telepon</label>
                <input type="text" id="memberPhone" class="form-control" placeholder="Masukkan nomor telepon">
            </div>
            <div class="form-group">
                <label for="memberAddress">Alamat</label>
                <textarea id="memberAddress" class="form-control" rows="3" placeholder="Masukkan alamat lengkap"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" id="saveMemberBtn"><i class="fas fa-save"></i> Simpan Anggota</button>
        </div>
    </div>
</div>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>BASADA</h2>
            <p>Bank Sampah Digital</p>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li class="active" data-section="dashboard">
                    <a href="#">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li data-section="members">
                    <a href="#">
                        <i class="fas fa-users"></i>
                        <span>Anggota</span>
                    </a>
                </li>
                <li data-section="transactions">
                    <a href="#">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Transaksi</span>
                    </a>
                </li>
                <li data-section="reports">
                    <a href="#">
                        <i class="fas fa-chart-bar"></i>
                        <span>Laporan</span>
                    </a>
                </li>
                <li data-section="settings">
                    <a href="#">
                        <i class="fas fa-cog"></i>
                        <span>Pengaturan</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <div class="navbar">
            <h1>Dashboard Admin</h1>
            <div class="navbar-user" id="userMenu">
                <img src="https://ui-avatars.com/api/?name=Admin+BASADA&background=4caf50&color=fff" alt="Admin" id="adminAvatar">
                <span id="adminNameDisplay">Admin BASADA</span>
                <div class="user-dropdown" id="userDropdown">
                    <a href="#" id="profileBtn"><i class="fas fa-user"></i> Profil Saya</a>
                    <a href="#" id="settingsBtn"><i class="fas fa-cog"></i> Pengaturan</a>
                    <a href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                </div>
            </div>
        </div>

        <!-- Dashboard Section -->
        <div class="section" id="dashboard">
            <!-- Cards -->
            <div class="cards">
                <div class="card">
                    <div class="card-header">
                        <h3>Total Transaksi</h3>
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="card-body">
                        <h2 id="totalTransactions">1,248</h2>
                        <p>+12% dari bulan lalu</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>Total Anggota</h3>
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-body">
                        <h2 id="totalMembers">856</h2>
                        <p>+8 anggota baru bulan ini</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>Total Sampah</h3>
                        <i class="fas fa-recycle"></i>
                    </div>
                    <div class="card-body">
                        <h2 id="totalWaste">2.5 Ton</h2>
                        <p>+350 kg dari bulan lalu</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>Pendapatan</h3>
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="card-body">
                        <h2 id="totalIncome">Rp 18.5 Jt</h2>
                        <p>+15% dari bulan lalu</p>
                    </div>
                </div>
            </div>

            <!-- Transactions Section -->
            <div class="content-section">
                <div class="section-header">
                    <h2>Transaksi Terbaru</h2>
                    <button class="btn" id="viewAllTransactions"><i class="fas fa-list"></i> Lihat Semua</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Nama Anggota</th>
                            <th>Jenis Sampah</th>
                            <th>Berat (kg)</th>
                            <th>Total (Rp)</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="transactionsTable">
                        <!-- Data transaksi akan dimuat di sini -->
                    </tbody>
                </table>
            </div>

            <!-- Chart Section -->
            <div class="content-section">
                <div class="section-header">
                    <h2>Statistik Transaksi Bulanan</h2>
                </div>
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Members Section (Hidden by default) -->
        <div class="section" id="members" style="display: none;">
            <div class="content-section">
                <div class="section-header">
                    <h2>Daftar Anggota</h2>
                    <button class="btn" id="addMemberBtn"><i class="fas fa-plus"></i> Tambah Anggota</button>
                </div>
                
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchMember" placeholder="Cari anggota...">
                    </div>
                    <div class="filter-box">
                        <select id="filterMemberStatus">
                            <option value="">Semua Status</option>
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID Anggota</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Tanggal Bergabung</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="membersTable">
                        <!-- Data anggota akan dimuat di sini -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transactions Section (Hidden by default) -->
        <div class="section" id="transactions" style="display: none;">
            <div class="content-section">
                <div class="section-header">
                    <h2>Manajemen Transaksi</h2>
                    <button class="btn" id="addTransactionBtn"><i class="fas fa-plus"></i> Tambah Transaksi</button>
                </div>
                
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchTransaction" placeholder="Cari transaksi...">
                    </div>
                    <div class="filter-box">
                        <select id="filterTransactionStatus">
                            <option value="">Semua Status</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Nama Anggota</th>
                            <th>Jenis Sampah</th>
                            <th>Berat (kg)</th>
                            <th>Total (Rp)</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="allTransactionsTable">
                        <!-- Data transaksi akan dimuat di sini -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reports Section (Hidden by default) -->
        <div class="section" id="reports" style="display: none;">
            <div class="content-section">
                <div class="section-header">
                    <h2>Laporan Keuangan</h2>
                    <button class="btn" id="exportReportBtn"><i class="fas fa-download"></i> Ekspor Laporan</button>
                </div>
                
                <div class="chart-container">
                    <canvas id="reportsChart"></canvas>
                </div>
                
                <div class="content-section" style="margin-top: 20px;">
                    <div class="section-header">
                        <h2>Ringkasan Keuangan</h2>
                    </div>
                    <div class="cards">
                        <div class="card">
                            <div class="card-header">
                                <h3>Pendapatan Bulan Ini</h3>
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="card-body">
                                <h2 id="monthlyIncome">Rp 2.1 Jt</h2>
                                <p>+8% dari bulan lalu</p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h3>Transaksi Bulan Ini</h3>
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="card-body">
                                <h2 id="monthlyTransactions">142</h2>
                                <p>+12 transaksi dari bulan lalu</p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h3>Sampah Bulan Ini</h3>
                                <i class="fas fa-recycle"></i>
                            </div>
                            <div class="card-body">
                                <h2 id="monthlyWaste">320 kg</h2>
                                <p>+45 kg dari bulan lalu</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Section (Hidden by default) -->
        <div class="section" id="settings" style="display: none;">
            <div class="content-section">
                <div class="section-header">
                    <h2>Pengaturan Sistem</h2>
                </div>
                
                <div class="settings-grid">
                    <div class="setting-group">
                        <h3><i class="fas fa-user-cog"></i> Pengaturan Profil</h3>
                        <div class="form-group">
                            <label for="adminNameSetting">Nama Admin</label>
                            <input type="text" id="adminNameSetting" class="form-control" value="Admin BASADA">
                        </div>
                        <div class="form-group">
                            <label for="adminEmailSetting">Email Admin</label>
                            <input type="email" id="adminEmailSetting" class="form-control" value="admin@basada.id">
                        </div>
                        <div class="form-group">
                            <label for="adminPhoneSetting">Telepon Admin</label>
                            <input type="text" id="adminPhoneSetting" class="form-control" value="+62 812-3456-7890">
                        </div>
                        <button class="btn" id="saveProfileSettings"><i class="fas fa-save"></i> Simpan Profil</button>
                    </div>
                    
                    <div class="setting-group">
                        <h3><i class="fas fa-cogs"></i> Pengaturan Sistem</h3>
                        <div class="form-group">
                            <label for="systemName">Nama Sistem</label>
                            <input type="text" id="systemName" class="form-control" value="BASADA - Bank Sampah Digital">
                        </div>
                        <div class="form-group">
                            <label for="currency">Mata Uang</label>
                            <select id="currency" class="form-control">
                                <option value="IDR" selected>Rupiah (IDR)</option>
                                <option value="USD">Dollar (USD)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="language">Bahasa</label>
                            <select id="language" class="form-control">
                                <option value="id" selected>Indonesia</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                        <button class="btn" id="saveSystemSettings"><i class="fas fa-save"></i> Simpan Sistem</button>
                    </div>
                    
                    <div class="setting-group">
                        <h3><i class="fas fa-bell"></i> Notifikasi</h3>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="emailNotifications" checked> Notifikasi Email
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="transactionNotifications" checked> Notifikasi Transaksi Baru
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="memberNotifications" checked> Notifikasi Anggota Baru
                            </label>
                        </div>
                        <button class="btn" id="saveNotificationSettings"><i class="fas fa-save"></i> Simpan Notifikasi</button>
                    </div>
                    
                    <div class="setting-group">
                        <h3><i class="fas fa-shield-alt"></i> Keamanan</h3>
                        <div class="form-group">
                            <label for="currentPassword">Password Saat Ini</label>
                            <input type="password" id="currentPassword" class="form-control" placeholder="Masukkan password saat ini">
                        </div>
                        <div class="form-group">
                            <label for="newPassword">Password Baru</label>
                            <input type="password" id="newPassword" class="form-control" placeholder="Masukkan password baru">
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Konfirmasi Password</label>
                            <input type="password" id="confirmPassword" class="form-control" placeholder="Konfirmasi password baru">
                        </div>
                        <button class="btn" id="changePasswordBtn"><i class="fas fa-key"></i> Ubah Password</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Profile Modal -->
    <div class="modal" id="adminProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Profil Admin</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="profile-info">
                    <img src="https://ui-avatars.com/api/?name=Admin+BASADA&background=4caf50&color=fff&size=120" alt="Admin" class="profile-img" id="adminProfileImage">
                    <div class="profile-details">
                        <h3 id="adminProfileName">Admin BASADA</h3>
                        <p><i class="fas fa-envelope"></i> <span id="adminProfileEmail">admin@basada.id</span></p>
                        <p><i class="fas fa-phone"></i> <span id="adminProfilePhone">+62 812-3456-7890</span></p>
                        <p><i class="fas fa-user-tag"></i> <span id="adminProfileRole">Super Admin</span></p>
                        <p><i class="fas fa-calendar-alt"></i> Bergabung: <span id="adminProfileJoinDate">15 Januari 2022</span></p>
                    </div>
                </div>
                
                <div class="tabs">
                    <div class="tab active" data-tab="adminStats">Statistik</div>
                    <div class="tab" data-tab="adminActivity">Aktivitas Terbaru</div>
                </div>
                
                <div class="tab-content active" id="adminStatsTab">
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value" id="adminTotalMembers">856</div>
                            <div class="stat-label">Total Anggota</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="adminTotalTransactions">1,248</div>
                            <div class="stat-label">Total Transaksi</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="adminTotalWaste">2.5 Ton</div>
                            <div class="stat-label">Sampah Terkelola</div>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="adminActivityTab">
                    <div class="content-section" style="margin: 0; padding: 15px;">
                        <h4>Aktivitas Terbaru</h4>
                        <ul style="list-style-type: none; padding: 0;">
                            <li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                                <i class="fas fa-exchange-alt" style="color: var(--primary-color); margin-right: 10px;"></i>
                                Menambahkan transaksi baru - TRX-001245
                            </li>
                            <li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                                <i class="fas fa-user-plus" style="color: var(--primary-color); margin-right: 10px;"></i>
                                Menambahkan anggota baru - MEM-0086
                            </li>
                            <li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                                <i class="fas fa-cog" style="color: var(--primary-color); margin-right: 10px;"></i>
                                Memperbarui pengaturan sistem
                            </li>
                            <li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                                <i class="fas fa-file-export" style="color: var(--primary-color); margin-right: 10px;"></i>
                                Mengekspor laporan bulanan
                            </li>
                            <li style="padding: 8px 0;">
                                <i class="fas fa-user-edit" style="color: var(--primary-color); margin-right: 10px;"></i>
                                Memperbarui profil anggota - Ahmad Santoso
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" id="editAdminProfileBtn"><i class="fas fa-edit"></i> Edit Profil</button>
                <button class="btn" id="adminSettingsBtn"><i class="fas fa-cog"></i> Pengaturan</button>
            </div>
        </div>
    </div>

    <!-- Edit Admin Profile Modal -->
    <div class="modal" id="editAdminProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Profil Admin</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="editAdminName">Nama</label>
                    <input type="text" id="editAdminName" class="form-control" value="Admin BASADA">
                </div>
                <div class="form-group">
                    <label for="editAdminEmail">Email</label>
                    <input type="email" id="editAdminEmail" class="form-control" value="admin@basada.id">
                </div>
                <div class="form-group">
                    <label for="editAdminPhone">Telepon</label>
                    <input type="text" id="editAdminPhone" class="form-control" value="+62 812-3456-7890">
                </div>
                <div class="form-group">
                    <label for="editAdminAvatar">URL Foto Profil</label>
                    <input type="text" id="editAdminAvatar" class="form-control" value="https://ui-avatars.com/api/?name=Admin+BASADA&background=4caf50&color=fff">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" id="saveAdminProfileBtn"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div class="notification success" id="successNotification">
        <i class="fas fa-check-circle"></i>
        <span id="notificationMessage">Operasi berhasil!</span>
    </div>

    <script>
        // Data contoh untuk aplikasi
        const sampleData = {
            transactions: [
                { id: 'TRX-001245', memberId: 'MEM-0001', member: 'Ahmad Santoso', wasteType: 'Plastik PET', weight: 5.2, total: 52000, date: '2023-03-15', status: 'Selesai' },
                { id: 'TRX-001244', memberId: 'MEM-0002', member: 'Siti Rahayu', wasteType: 'Kertas Koran', weight: 8.5, total: 42500, date: '2023-03-14', status: 'Selesai' },
                { id: 'TRX-001243', memberId: 'MEM-0003', member: 'Budi Prasetyo', wasteType: 'Kaleng Aluminium', weight: 3.7, total: 74000, date: '2023-03-14', status: 'Pending' },
                { id: 'TRX-001242', memberId: 'MEM-0004', member: 'Dewi Lestari', wasteType: 'Botol Kaca', weight: 6.8, total: 34000, date: '2023-03-13', status: 'Selesai' },
                { id: 'TRX-001241', memberId: 'MEM-0005', member: 'Rizki Maulana', wasteType: 'Plastik HDPE', weight: 4.3, total: 43000, date: '2023-03-12', status: 'Selesai' }
            ],
            members: [
                { id: 'MEM-0001', name: 'Ahmad Santoso', email: 'ahmad@email.com', phone: '081234567890', address: 'Jl. Merdeka No. 123, Jakarta', joinDate: '2022-05-10', status: 'Aktif' },
                { id: 'MEM-0002', name: 'Siti Rahayu', email: 'siti@email.com', phone: '081234567891', address: 'Jl. Sudirman No. 45, Bandung', joinDate: '2022-06-15', status: 'Aktif' },
                { id: 'MEM-0003', name: 'Budi Prasetyo', email: 'budi@email.com', phone: '081234567892', address: 'Jl. Gatot Subroto No. 78, Surabaya', joinDate: '2022-07-20', status: 'Aktif' },
                { id: 'MEM-0004', name: 'Dewi Lestari', email: 'dewi@email.com', phone: '081234567893', address: 'Jl. Thamrin No. 56, Medan', joinDate: '2022-08-05', status: 'Aktif' },
                { id: 'MEM-0005', name: 'Rizki Maulana', email: 'rizki@email.com', phone: '081234567894', address: 'Jl. Diponegoro No. 90, Yogyakarta', joinDate: '2022-09-12', status: 'Nonaktif' }
            ],
            wasteTypes: [
                { id: 1, name: 'Plastik PET', price: 10000 },
                { id: 2, name: 'Kertas Koran', price: 5000 },
                { id: 3, name: 'Kaleng Aluminium', price: 20000 },
                { id: 4, name: 'Botol Kaca', price: 5000 },
                { id: 5, name: 'Plastik HDPE', price: 10000 }
            ],
            monthlyStats: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                transactions: [85, 92, 78, 105, 120, 98, 110, 125, 115, 130, 140, 135],
                waste: [1.2, 1.5, 1.3, 1.8, 2.0, 1.7, 1.9, 2.2, 2.1, 2.3, 2.5, 2.4],
                income: [12, 14, 11, 16, 18, 15, 17, 19, 18, 20, 22, 21]
            },
            adminProfile: {
                name: "Admin BASADA",
                email: "admin@basada.id",
                phone: "+62 812-3456-7890",
                role: "Super Admin",
                joinDate: "2022-01-15",
                avatar: "https://ui-avatars.com/api/?name=Admin+BASADA&background=4caf50&color=fff"
            },
            systemSettings: {
                systemName: "BASADA - Bank Sampah Digital",
                currency: "IDR",
                language: "id",
                emailNotifications: true,
                transactionNotifications: true,
                memberNotifications: true
            }
        };

        // Inisialisasi aplikasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi data
            initializeData();
            
            // Setup event listeners
            setupEventListeners();
            
            // Setup chart
            setupCharts();
            
            // Load admin profile
            loadAdminProfile();
            
            // Load system settings
            loadSystemSettings();
        });

        function initializeData() {
            // Load transactions to table
            loadTransactionsToTable();
            
            // Load members to table
            loadMembersToTable();
            
            // Load all transactions to table
            loadAllTransactionsToTable();
            
            // Populate dropdowns in modals
            populateMemberDropdown();
            
            // Set tanggal hari ini sebagai default di form transaksi
            document.getElementById('transactionDate').valueAsDate = new Date();
        }

        function setupEventListeners() {
            // Sidebar menu navigation
            const menuItems = document.querySelectorAll('.sidebar-menu li');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    const sectionId = this.getAttribute('data-section');
                    switchSection(sectionId);
                    
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // User dropdown menu
            const userMenu = document.getElementById('userMenu');
            const userDropdown = document.getElementById('userDropdown');
            
            userMenu.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });
            
            // Close dropdown when clicking elsewhere
            document.addEventListener('click', function() {
                userDropdown.classList.remove('active');
            });
            
            // Profile button in dropdown
            const profileBtn = document.getElementById('profileBtn');
            const adminProfileModal = document.getElementById('adminProfileModal');
            
            profileBtn.addEventListener('click', function(e) {
                e.preventDefault();
                userDropdown.classList.remove('active');
                adminProfileModal.classList.add('active');
            });
            
            // Settings button in dropdown
            const settingsBtn = document.getElementById('settingsBtn');
            
            settingsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                userDropdown.classList.remove('active');
                switchSection('settings');
                menuItems.forEach(i => i.classList.remove('active'));
                document.querySelector('[data-section="settings"]').classList.add('active');
            });
            
            // Logout button
            const logoutBtn = document.getElementById('logoutBtn');
            
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Apakah Anda yakin ingin keluar?')) {
                    showNotification('Anda telah berhasil keluar', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 1500);
                }
            });
            
            // Edit admin profile button
            const editAdminProfileBtn = document.getElementById('editAdminProfileBtn');
            const editAdminProfileModal = document.getElementById('editAdminProfileModal');
            
            editAdminProfileBtn.addEventListener('click', function() {
                adminProfileModal.classList.remove('active');
                editAdminProfileModal.classList.add('active');
                
                // Isi form dengan data admin saat ini
                document.getElementById('editAdminName').value = sampleData.adminProfile.name;
                document.getElementById('editAdminEmail').value = sampleData.adminProfile.email;
                document.getElementById('editAdminPhone').value = sampleData.adminProfile.phone;
                document.getElementById('editAdminAvatar').value = sampleData.adminProfile.avatar;
            });
            
            // Save admin profile button
            const saveAdminProfileBtn = document.getElementById('saveAdminProfileBtn');
            
            saveAdminProfileBtn.addEventListener('click', function() {
                // Update admin profile data
                sampleData.adminProfile.name = document.getElementById('editAdminName').value;
                sampleData.adminProfile.email = document.getElementById('editAdminEmail').value;
                sampleData.adminProfile.phone = document.getElementById('editAdminPhone').value;
                sampleData.adminProfile.avatar = document.getElementById('editAdminAvatar').value;
                
                // Update UI
                loadAdminProfile();
                
                // Tutup modal
                editAdminProfileModal.classList.remove('active');
                
                // Tampilkan notifikasi
                showNotification('Profil admin berhasil diperbarui!', 'success');
            });
            
            // Admin settings button in profile modal
            const adminSettingsBtn = document.getElementById('adminSettingsBtn');
            
            adminSettingsBtn.addEventListener('click', function() {
                adminProfileModal.classList.remove('active');
                switchSection('settings');
                menuItems.forEach(i => i.classList.remove('active'));
                document.querySelector('[data-section="settings"]').classList.add('active');
            });
            
            // Save profile settings
            const saveProfileSettings = document.getElementById('saveProfileSettings');
            
            saveProfileSettings.addEventListener('click', function() {
                // Update admin profile from settings
                sampleData.adminProfile.name = document.getElementById('adminNameSetting').value;
                sampleData.adminProfile.email = document.getElementById('adminEmailSetting').value;
                sampleData.adminProfile.phone = document.getElementById('adminPhoneSetting').value;
                
                // Update UI
                loadAdminProfile();
                
                showNotification('Pengaturan profil berhasil disimpan!', 'success');
            });
            
            // Save system settings
            const saveSystemSettings = document.getElementById('saveSystemSettings');
            
            saveSystemSettings.addEventListener('click', function() {
                // Update system settings
                sampleData.systemSettings.systemName = document.getElementById('systemName').value;
                sampleData.systemSettings.currency = document.getElementById('currency').value;
                sampleData.systemSettings.language = document.getElementById('language').value;
                
                showNotification('Pengaturan sistem berhasil disimpan!', 'success');
            });
            
            // Save notification settings
            const saveNotificationSettings = document.getElementById('saveNotificationSettings');
            
            saveNotificationSettings.addEventListener('click', function() {
                // Update notification settings
                sampleData.systemSettings.emailNotifications = document.getElementById('emailNotifications').checked;
                sampleData.systemSettings.transactionNotifications = document.getElementById('transactionNotifications').checked;
                sampleData.systemSettings.memberNotifications = document.getElementById('memberNotifications').checked;
                
                showNotification('Pengaturan notifikasi berhasil disimpan!', 'success');
            });
            
            // Change password button
            const changePasswordBtn = document.getElementById('changePasswordBtn');
            
            changePasswordBtn.addEventListener('click', function() {
                const currentPassword = document.getElementById('currentPassword').value;
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                if (!currentPassword || !newPassword || !confirmPassword) {
                    showNotification('Harap lengkapi semua field password!', 'error');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    showNotification('Password baru dan konfirmasi password tidak cocok!', 'error');
                    return;
                }
                
                if (newPassword.length < 6) {
                    showNotification('Password baru harus minimal 6 karakter!', 'error');
                    return;
                }
                
                // Simulasi perubahan password
                // Dalam aplikasi nyata, ini akan mengirim request ke server
                
                // Reset form
                document.getElementById('currentPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';
                
                showNotification('Password berhasil diubah!', 'success');
            });
            
            // Tabs in admin profile modal
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding content
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    document.getElementById(tabId + 'Tab').classList.add('active');
                });
            });
            
            // Close modal buttons
            const closeModalBtns = document.querySelectorAll('.close-modal');
            closeModalBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.modal').classList.remove('active');
                });
            });
            
            // Admin avatar click to change
            const adminAvatar = document.getElementById('adminProfileImage');
            adminAvatar.addEventListener('click', function() {
                document.getElementById('editAdminAvatar').value = sampleData.adminProfile.avatar;
                editAdminProfileModal.classList.add('active');
            });
        }

        function loadAdminProfile() {
            // Update navbar
            document.getElementById('adminNameDisplay').textContent = sampleData.adminProfile.name;
            document.getElementById('adminAvatar').src = sampleData.adminProfile.avatar;
            
            // Update profile modal
            document.getElementById('adminProfileName').textContent = sampleData.adminProfile.name;
            document.getElementById('adminProfileEmail').textContent = sampleData.adminProfile.email;
            document.getElementById('adminProfilePhone').textContent = sampleData.adminProfile.phone;
            document.getElementById('adminProfileRole').textContent = sampleData.adminProfile.role;
            document.getElementById('adminProfileJoinDate').textContent = formatDate(sampleData.adminProfile.joinDate);
            document.getElementById('adminProfileImage').src = sampleData.adminProfile.avatar;
            
            // Update stats in profile modal
            document.getElementById('adminTotalMembers').textContent = sampleData.members.filter(m => m.status === 'Aktif').length;
            document.getElementById('adminTotalTransactions').textContent = sampleData.transactions.length;
            document.getElementById('adminTotalWaste').textContent = (sampleData.transactions.reduce((sum, t) => sum + t.weight, 0) / 1000).toFixed(1) + ' Ton';
            
            // Update settings form
            document.getElementById('adminNameSetting').value = sampleData.adminProfile.name;
            document.getElementById('adminEmailSetting').value = sampleData.adminProfile.email;
            document.getElementById('adminPhoneSetting').value = sampleData.adminProfile.phone;
        }

        function loadSystemSettings() {
            // Update system settings form
            document.getElementById('systemName').value = sampleData.systemSettings.systemName;
            document.getElementById('currency').value = sampleData.systemSettings.currency;
            document.getElementById('language').value = sampleData.systemSettings.language;
            document.getElementById('emailNotifications').checked = sampleData.systemSettings.emailNotifications;
            document.getElementById('transactionNotifications').checked = sampleData.systemSettings.transactionNotifications;
            document.getElementById('memberNotifications').checked = sampleData.systemSettings.memberNotifications;
        }

        // ... (fungsi-fungsi lainnya tetap sama seperti sebelumnya)

        function showNotification(message, type) {
            const notification = document.getElementById('successNotification');
            const notificationMessage = document.getElementById('notificationMessage');
            
            notificationMessage.textContent = message;
            notification.className = `notification ${type} show`;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        function formatDate(dateString) {
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        }

        // ... (fungsi-fungsi lainnya tetap sama seperti sebelumnya)
        // Tambahkan kode ini di dalam tag <script> setelah deklarasi sampleData

function switchSection(sectionId) {
    // Sembunyikan semua section
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.style.display = 'none';
    });
    
    // Tampilkan section yang dipilih
    const activeSection = document.getElementById(sectionId);
    if (activeSection) {
        activeSection.style.display = 'block';
    }
    
    // Jika section laporan, perbarui chart
    if (sectionId === 'reports') {
        setTimeout(() => {
            setupReportsChart();
        }, 100);
    }
}

function loadTransactionsToTable() {
    const tableBody = document.getElementById('transactionsTable');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    // Ambil 5 transaksi terbaru
    const recentTransactions = sampleData.transactions.slice(0, 5);
    
    recentTransactions.forEach(transaction => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${transaction.id}</td>
            <td>${transaction.member}</td>
            <td>${transaction.wasteType}</td>
            <td>${transaction.weight}</td>
            <td>${transaction.total.toLocaleString('id-ID')}</td>
            <td>${formatDate(transaction.date)}</td>
            <td><span class="status status-${transaction.status === 'Selesai' ? 'success' : 'pending'}">${transaction.status}</span></td>
            <td>
                <button class="btn btn-small" onclick="viewTransaction('${transaction.id}')">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function loadAllTransactionsToTable() {
    const tableBody = document.getElementById('allTransactionsTable');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    sampleData.transactions.forEach(transaction => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${transaction.id}</td>
            <td>${transaction.member}</td>
            <td>${transaction.wasteType}</td>
            <td>${transaction.weight}</td>
            <td>${transaction.total.toLocaleString('id-ID')}</td>
            <td>${formatDate(transaction.date)}</td>
            <td><span class="status status-${transaction.status === 'Selesai' ? 'success' : 'pending'}">${transaction.status}</span></td>
            <td>
                <button class="btn btn-small" onclick="viewTransaction('${transaction.id}')">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-small btn-danger" onclick="deleteTransaction('${transaction.id}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function loadMembersToTable() {
    const tableBody = document.getElementById('membersTable');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    sampleData.members.forEach(member => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${member.id}</td>
            <td>${member.name}</td>
            <td>${member.email}</td>
            <td>${member.phone}</td>
            <td>${formatDate(member.joinDate)}</td>
            <td><span class="status status-${member.status === 'Aktif' ? 'success' : 'pending'}">${member.status}</span></td>
            <td>
                <button class="btn btn-small" onclick="viewMemberProfile('${member.id}')">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-small btn-warning" onclick="editMember('${member.id}')">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-small btn-danger" onclick="deleteMember('${member.id}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function populateMemberDropdown() {
    const dropdown = document.getElementById('transactionMember');
    if (!dropdown) return;
    
    dropdown.innerHTML = '<option value="">Pilih Anggota</option>';
    
    sampleData.members.forEach(member => {
        if (member.status === 'Aktif') {
            const option = document.createElement('option');
            option.value = member.id;
            option.textContent = `${member.name} (${member.id})`;
            dropdown.appendChild(option);
        }
    });
}

function setupCharts() {
    // Chart untuk dashboard
    const monthlyCtx = document.getElementById('monthlyChart');
    if (!monthlyCtx) return;
    
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: sampleData.monthlyStats.labels,
            datasets: [
                {
                    label: 'Jumlah Transaksi',
                    data: sampleData.monthlyStats.transactions,
                    backgroundColor: 'rgba(76, 175, 80, 0.7)',
                    borderColor: '#4caf50',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Statistik Transaksi Bulanan'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Transaksi'
                    }
                }
            }
        }
    });
}

function setupReportsChart() {
    // Chart untuk laporan
    const reportsCtx = document.getElementById('reportsChart');
    if (!reportsCtx) return;
    
    new Chart(reportsCtx, {
        type: 'line',
        data: {
            labels: sampleData.monthlyStats.labels,
            datasets: [
                {
                    label: 'Pendapatan (Juta Rupiah)',
                    data: sampleData.monthlyStats.income,
                    borderColor: '#4caf50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Pendapatan Bulanan'
                }
            }
        }
    });
}

function updateStatistics() {
    // Perbarui statistik di card
    document.getElementById('totalTransactions').textContent = sampleData.transactions.length.toLocaleString('id-ID');
    document.getElementById('totalMembers').textContent = sampleData.members.filter(m => m.status === 'Aktif').length.toLocaleString('id-ID');
    
    const totalWaste = sampleData.transactions.reduce((sum, transaction) => sum + transaction.weight, 0);
    document.getElementById('totalWaste').textContent = (totalWaste / 1000).toFixed(1) + ' Ton';
    
    const totalIncome = sampleData.transactions.reduce((sum, transaction) => sum + transaction.total, 0);
    document.getElementById('totalIncome').textContent = 'Rp ' + (totalIncome / 1000000).toFixed(1) + ' Jt';
    
    // Perbarui statistik bulanan di laporan
    const currentMonth = new Date().getMonth();
    document.getElementById('monthlyIncome').textContent = 'Rp ' + (sampleData.monthlyStats.income[currentMonth] * 100000).toLocaleString('id-ID');
    document.getElementById('monthlyTransactions').textContent = sampleData.monthlyStats.transactions[currentMonth];
    document.getElementById('monthlyWaste').textContent = (sampleData.monthlyStats.waste[currentMonth] * 1000) + ' kg';
}

function filterMembers() {
    const searchTerm = document.getElementById('searchMember').value.toLowerCase();
    const statusFilter = document.getElementById('filterMemberStatus').value;
    
    const filteredMembers = sampleData.members.filter(member => {
        const matchesSearch = member.name.toLowerCase().includes(searchTerm) || 
                            member.email.toLowerCase().includes(searchTerm) ||
                            member.id.toLowerCase().includes(searchTerm);
        const matchesStatus = statusFilter === '' || member.status === statusFilter;
        
        return matchesSearch && matchesStatus;
    });
    
    const tableBody = document.getElementById('membersTable');
    tableBody.innerHTML = '';
    
    filteredMembers.forEach(member => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${member.id}</td>
            <td>${member.name}</td>
            <td>${member.email}</td>
            <td>${member.phone}</td>
            <td>${formatDate(member.joinDate)}</td>
            <td><span class="status status-${member.status === 'Aktif' ? 'success' : 'pending'}">${member.status}</span></td>
            <td>
                <button class="btn btn-small" onclick="viewMemberProfile('${member.id}')">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-small btn-warning" onclick="editMember('${member.id}')">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-small btn-danger" onclick="deleteMember('${member.id}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function filterTransactions() {
    const searchTerm = document.getElementById('searchTransaction').value.toLowerCase();
    const statusFilter = document.getElementById('filterTransactionStatus').value;
    
    const filteredTransactions = sampleData.transactions.filter(transaction => {
        const matchesSearch = transaction.member.toLowerCase().includes(searchTerm) || 
                            transaction.id.toLowerCase().includes(searchTerm) ||
                            transaction.wasteType.toLowerCase().includes(searchTerm);
        const matchesStatus = statusFilter === '' || transaction.status === statusFilter;
        
        return matchesSearch && matchesStatus;
    });
    
    const tableBody = document.getElementById('allTransactionsTable');
    tableBody.innerHTML = '';
    
    filteredTransactions.forEach(transaction => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${transaction.id}</td>
            <td>${transaction.member}</td>
            <td>${transaction.wasteType}</td>
            <td>${transaction.weight}</td>
            <td>${transaction.total.toLocaleString('id-ID')}</td>
            <td>${formatDate(transaction.date)}</td>
            <td><span class="status status-${transaction.status === 'Selesai' ? 'success' : 'pending'}">${transaction.status}</span></td>
            <td>
                <button class="btn btn-small" onclick="viewTransaction('${transaction.id}')">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-small btn-danger" onclick="deleteTransaction('${transaction.id}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Tambahkan juga fungsi-fungsi global untuk aksi
function viewTransaction(id) {
    const transaction = sampleData.transactions.find(t => t.id === id);
    if (transaction) {
        alert(`Detail Transaksi:\nID: ${transaction.id}\nAnggota: ${transaction.member}\nJenis Sampah: ${transaction.wasteType}\nBerat: ${transaction.weight} kg\nTotal: Rp ${transaction.total.toLocaleString('id-ID')}\nTanggal: ${formatDate(transaction.date)}\nStatus: ${transaction.status}`);
    }
}

function deleteTransaction(id) {
    if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
        sampleData.transactions = sampleData.transactions.filter(t => t.id !== id);
        loadTransactionsToTable();
        loadAllTransactionsToTable();
        updateStatistics();
        showNotification('Transaksi berhasil dihapus!', 'success');
    }
}

function viewMemberProfile(id) {
    const member = sampleData.members.find(m => m.id === id);
    if (member) {
        alert(`Profil Anggota:\nID: ${member.id}\nNama: ${member.name}\nEmail: ${member.email}\nTelepon: ${member.phone}\nAlamat: ${member.address}\nBergabung: ${formatDate(member.joinDate)}\nStatus: ${member.status}`);
    }
}

function editMember(id) {
    const member = sampleData.members.find(m => m.id === id);
    if (member) {
        alert(`Edit anggota: ${member.name}\nFitur ini akan dikembangkan lebih lanjut.`);
    }
}

function deleteMember(id) {
    if (confirm('Apakah Anda yakin ingin menghapus anggota ini?')) {
        sampleData.members = sampleData.members.filter(m => m.id !== id);
        loadMembersToTable();
        populateMemberDropdown();
        showNotification('Anggota berhasil dihapus!', 'success');
    }
}

function formatDate(dateString) {
    const options = { day: 'numeric', month: 'short', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}
// Tambahkan di dalam setupEventListeners()

// Add transaction button
const addTransactionBtn = document.getElementById('addTransactionBtn');
const addTransactionModal = document.getElementById('addTransactionModal');
const saveTransactionBtn = document.getElementById('saveTransactionBtn');

if (addTransactionBtn) {
    addTransactionBtn.addEventListener('click', function() {
        addTransactionModal.classList.add('active');
    });
}

// Calculate transaction total when weight or waste type changes
document.getElementById('transactionWeight').addEventListener('input', calculateTransactionTotal);
document.getElementById('transactionWasteType').addEventListener('change', calculateTransactionTotal);

saveTransactionBtn.addEventListener('click', function() {
    saveTransaction();
});

// Add member button
const addMemberBtn = document.getElementById('addMemberBtn');
const addMemberModal = document.getElementById('addMemberModal');
const saveMemberBtn = document.getElementById('saveMemberBtn');

if (addMemberBtn) {
    addMemberBtn.addEventListener('click', function() {
        addMemberModal.classList.add('active');
    });
}

saveMemberBtn.addEventListener('click', function() {
    saveMember();
});

// Fungsi calculateTransactionTotal
function calculateTransactionTotal() {
    const wasteTypeId = document.getElementById('transactionWasteType').value;
    const weight = parseFloat(document.getElementById('transactionWeight').value) || 0;
    
    if (wasteTypeId && weight > 0) {
        const wasteType = sampleData.wasteTypes.find(w => w.id == wasteTypeId);
        const total = weight * wasteType.price;
        document.getElementById('transactionTotal').value = total.toLocaleString('id-ID');
    } else {
        document.getElementById('transactionTotal').value = '';
    }
}

// Fungsi saveTransaction
function saveTransaction() {
    const memberId = document.getElementById('transactionMember').value;
    const wasteTypeId = document.getElementById('transactionWasteType').value;
    const weight = parseFloat(document.getElementById('transactionWeight').value);
    const date = document.getElementById('transactionDate').value;
    
    if (!memberId || !wasteTypeId || !weight || !date) {
        showNotification('Harap lengkapi semua field!', 'error');
        return;
    }
    
    // Hitung total berdasarkan harga jenis sampah
    const wasteType = sampleData.wasteTypes.find(w => w.id == wasteTypeId);
    const total = weight * wasteType.price;
    
    // Generate ID transaksi baru
    const newId = 'TRX-' + String(sampleData.transactions.length + 1).padStart(6, '0');
    
    // Dapatkan nama anggota
    const member = sampleData.members.find(m => m.id === memberId);
    
    // Tambahkan transaksi baru
    const newTransaction = {
        id: newId,
        memberId: memberId,
        member: member.name,
        wasteType: wasteType.name,
        weight: weight,
        total: total,
        date: date,
        status: 'Selesai'
    };
    
    sampleData.transactions.unshift(newTransaction);
    
    // Perbarui tabel
    loadTransactionsToTable();
    loadAllTransactionsToTable();
    
    // Perbarui statistik
    updateStatistics();
    
    // Tutup modal
    document.getElementById('addTransactionModal').classList.remove('active');
    
    // Reset form
    document.getElementById('transactionWeight').value = '';
    document.getElementById('transactionTotal').value = '';
    document.getElementById('transactionWasteType').value = '';
    
    // Tampilkan notifikasi sukses
    showNotification('Transaksi berhasil ditambahkan!', 'success');
}

// Fungsi saveMember
function saveMember() {
    const name = document.getElementById('memberName').value;
    const email = document.getElementById('memberEmail').value;
    const phone = document.getElementById('memberPhone').value;
    const address = document.getElementById('memberAddress').value;
    
    if (!name || !email || !phone || !address) {
        showNotification('Harap lengkapi semua field!', 'error');
        return;
    }
    
    // Generate ID anggota baru
    const newId = 'MEM-' + String(sampleData.members.length + 1).padStart(4, '0');
    
    // Tambahkan anggota baru
    const newMember = {
        id: newId,
        name: name,
        email: email,
        phone: phone,
        address: address,
        joinDate: new Date().toISOString().split('T')[0],
        status: 'Aktif'
    };
    
    sampleData.members.push(newMember);
    
    // Perbarui tabel
    loadMembersToTable();
    
    // Perbarui dropdown anggota
    populateMemberDropdown();
    
    // Tutup modal
    document.getElementById('addMemberModal').classList.remove('active');
    
    // Reset form
    document.getElementById('memberName').value = '';
    document.getElementById('memberEmail').value = '';
    document.getElementById('memberPhone').value = '';
    document.getElementById('memberAddress').value = '';
    
    // Tampilkan notifikasi sukses
    showNotification('Anggota berhasil ditambahkan!', 'success');
}
    </script>
</body>
</html>