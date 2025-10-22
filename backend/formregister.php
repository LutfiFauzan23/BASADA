<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Nasabah - Bank Sampah Digital (BASADA)</title>
    <link rel="stylesheet" href="register.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <form action="databases/register.php" method="POST">
    <div class="container">
        <div class="leaf-decoration leaf-1">
            <i class="fas fa-leaf"></i>
        </div>
        <div class="leaf-decoration leaf-2">
            <i class="fas fa-seedling"></i>
        </div>
        
        <div class="header">
            <div class="logo">
                <i class="fas fa-recycle" style="font-size: 48px; color: var(--primary-dark);"></i>
            </div>
            <h1>Daftar Nasabah</h1>
            <p class="subtitle">Bergabunglah dengan gerakan peduli lingkungan</p>
            <p class="help-desk">
                <strong>Bank Sampah Digital (BASADA)</strong><br>
                Jadilah bagian dari perubahan lingkungan<br>
                Help Desk: <strong>0877-7760-4327</strong>
            </p>
        </div>
        
        <form id="registerForm">
            <div class="form-group">
                <label for="nama"><i class="fas fa-user" style="margin-right: 5px;"></i> Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
                <p class="eco-tip">Data Anda akan dijaga kerahasiaannya</p>
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope" style="margin-right: 5px;"></i> Alamat Email</label>
                <input type="email" id="email" name="email" placeholder="contoh@email.com" required>
            </div>
            
            <div class="form-group">
                <label for="no_hp"><i class="fas fa-mobile-alt" style="margin-right: 5px;"></i> Nomor Handphone</label>
                <input type="tel" id="no_hp" name="no_hp" placeholder="0812 3456 7890" required>
                <p class="eco-tip">Masukkan nomor Handphone sesuai dengan E-wallet yang anda pakai</p>
            </div>
            
            <div class="form-group">
                <label for="alamat"><i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i> Alamat Lengkap</label>
                <input type="text" id="alamat" name="alamat" placeholder="Jl. Contoh No. 123" required>
                <p class="eco-tip">Untuk memudahkan penjemputan sampah</p>
            </div>
            
            <div class="form-group">
                <label for="jenis"><i class="fas fa-users" style="margin-right: 5px;"></i> Jenis Nasabah</label>
                <select id="jenis" name="jenis" required>
                    <option value="" disabled selected>Pilih Jenis Nasabah</option>
                    <option value="individu">Individu</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock" style="margin-right: 5px;"></i> Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" minlength="8" required>
                    <span class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div id="password-strength" style="font-size: 12px; margin-top: 5px; color: var(--text-light);"></div>
            </div>
            
            <div class="form-group">
                <label for="confirm-password"><i class="fas fa-key" style="margin-right: 5px;"></i> Konfirmasi Password</label>
                <div class="password-container">
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Ketik ulang password" minlength="8" required>
                    <span class="toggle-password" onclick="togglePassword('confirm-password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div id="password-error" class="error-message">
                    <i class="fas fa-exclamation-circle"></i> Password dan Konfirmasi Password tidak sama
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 25px;">
                <a href="../backend/login.php"><button type="submit" class="register-btn" name="daftar">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button></a>
                <p class="eco-tip" style="text-align: center; margin-top: 10px;">
                    Dengan mendaftar, Anda menyetujui kebijakan pengelolaan sampah kami
                </p>
            </div>
        </form>
        
        <div class="login-link">
            Sudah punya akun? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Masuk disini</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="register.js"></script>
</body>
</html>