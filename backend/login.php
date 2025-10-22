<!-- <?php

//     include "databases/config.php";

//     if(isset($_POST["login"]));
//     $nama = $_POST["nama"];
//     $email = $_POST["email"];
//     $password = $_POST["password"];
// ?> -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Nasabah - Bank Sampah Hijau Lestari</title>
    <link rel="stylesheet" href="register.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <form action="login.php" method="POST">
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
            <h1>Login Nasabah</h1>
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
                <p class="eco-tip">Masukkan nama sesuai dengan akun yang anda sudah buat</p>
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope" style="margin-right: 5px;"></i> Alamat Email</label>
                <input type="email" id="email" name="email" placeholder="contoh@email.com" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock" style="margin-right: 5px;"></i> Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="Masukkan Password Anda" minlength="8" required>
                    <span class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div id="password-strength" style="font-size: 12px; margin-top: 5px; color: var(--text-light);"></div>
            </div> 

            <div class="form-group" style="margin-top: 25px;">
                <a href="../frontend/userdashboard.php"><button type="submit" class="register-btn" name="login">
                    <i class="fas fa-user"></i> Login
                </button></a>

                <div class="login-link">
            Belum Punya Akun? <a href="../backend/formregister.php"><i class="fas fa-sign-in-alt"></i> Daftar Sekarang</a>
        </div>
</form>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="register.js"></script>
</body>
</html>