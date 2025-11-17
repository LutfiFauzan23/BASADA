<?php
    session_start();
    include "connect.php";
?>

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

    <?php
        if(isset($_POST['nama'])) {
            $nama_lengkap = $_POST['nama'];
            $alamat_email = $_POST['email'];
            $no_hp = $_POST['no_hp'];
            $alamat = $_POST['alamat'];
            $password = $_POST['password'];

            $query = mysqli_query($connect, "INSERT INTO users(nama_lengkap, alamat_email, nomor_telepon, alamat, password) values ('$nama_lengkap', '$alamat_email', '$no_hp', '$alamat', '$password')");
            
            if($query) {
                echo '<script>alert("Kamu berhasil.Silahkan Login")</script>';
            } else {
                echo '<script>alert("Daftar kamu gagal")</script>';
            }
        }
    ?>

    <style>:root {
            --primary: #2e7d32;
            --primary-dark: #1b5e20;
            --primary-light: #81c784;
            --secondary: #f1f8e9;
            --accent: #ff8f00;
            --text: #2e3b0b;
            --text-light: #5a6e10;
            --error: #c62828;
            --border: #c8e6c9;
            --shadow: 0 4px 20px rgba(46, 125, 50, 0.15);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--secondary);
            color: var(--text);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-image: url('https://images.unsplash.com/photo-1466611653911-95081537e5b7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-blend-mode: overlay;
            background-color: rgba(46, 125, 50, 0.1);
        }
        
        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 40px;
            width: 100%;
            max-width: 480px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border);
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
        }
        
        .leaf-decoration {
            position: absolute;
            opacity: 0.1;
            z-index: 0;
        }
        
        .leaf-1 {
            top: 20px;
            right: 20px;
            font-size: 80px;
            color: var(--primary-dark);
            transform: rotate(30deg);
        }
        
        .leaf-2 {
            bottom: 30px;
            left: 25px;
            font-size: 60px;
            color: var(--primary);
            transform: rotate(-15deg);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }
        
        .logo {
            width: 80px;
            margin-bottom: 15px;
        }
        
        h1 {
            color: var(--primary-dark);
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
        }
        
        .subtitle {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .help-desk {
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 25px;
            text-align: center;
            background-color: var(--secondary);
            padding: 10px;
            border-radius: 8px;
            border: 1px dashed var(--primary-light);
        }
        
        .help-desk strong {
            color: var(--primary-dark);
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: var(--primary-dark);
        }
        
        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.8);
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.2);
            background-color: white;
        }
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-light);
            font-size: 18px;
        }
        
        .error-message {
            color: var(--error);
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }
        
        .register-btn {
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .register-btn:hover {
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
        }
        
        .register-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, var(--primary-light), var(--primary));
            opacity: 0;
            z-index: -1;
            transition: opacity 0.3s ease;
        }
        
        .register-btn:hover::after {
            opacity: 1;
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: var(--text-light);
            position: relative;
            z-index: 1;
        }
        
        .login-link a {
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .login-link a:hover {
            text-decoration: underline;
            color: var(--primary);
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 40px;
            color: var(--primary);
            font-size: 18px;
        }
        
        .input-with-icon {
            padding-left: 45px !important;
        }
        
        .eco-tip {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 5px;
            font-style: italic;
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .leaf-decoration {
                display: none;
            }
        }</style>
    <form method="POST">
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

    <script>        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            
            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                field.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthElement = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthElement.textContent = '';
                return;
            }
            
            if (password.length < 8) {
                strengthElement.textContent = '🔴 Lemah (minimal 8 karakter)';
                strengthElement.style.color = 'var(--error)';
            } else if (!/[A-Z]/.test(password) || !/[0-9]/.test(password) || !/[^A-Za-z0-9]/.test(password)) {
                strengthElement.textContent = '🟡 Sedang (gunakan kombinasi huruf, angka, dan simbol)';
                strengthElement.style.color = '#ff9800';
            } else {
                strengthElement.textContent = '🟢 Kuat - Siap mendukung lingkungan!';
                strengthElement.style.color = 'var(--primary)';
            }
        });
        
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const errorElement = document.getElementById('password-error');
            
            // Reset error state
            errorElement.style.display = 'none';
            document.getElementById('confirm-password').style.borderColor = '';
            
            if (password !== confirmPassword) {
                errorElement.style.display = 'block';
                document.getElementById('confirm-password').style.borderColor = 'var(--error)';
                document.getElementById('confirm-password').focus();
                return false;
            }
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            submitButton.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                // Form is valid - show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil!',
                    html: `
                        <div style="text-align: left;">
                            <p style="margin-bottom: 15px;">Selamat! Anda sekarang menjadi bagian dari gerakan lingkungan Bank Sampah Hijau Lestari.</p>
                            <div style="background-color: #e8f5e9; padding: 15px; border-radius: 8px; border-left: 4px solid var(--primary);">
                                <p><strong>Nama:</strong> ${document.getElementById('nama').value}</p>
                                <p><strong>Email:</strong> ${document.getElementById('email').value}</p>
                                <p><strong>No. HP:</strong> ${document.getElementById('no_hp').value}</p>
                                <p><strong>Jenis Nasabah:</strong> ${document.getElementById('jenis').options[document.getElementById('jenis').selectedIndex].text}</p>
                            </div>
                            <p style="margin-top: 15px; font-size: 14px;">Tim kami akan menghubungi Anda untuk informasi lebih lanjut tentang penjemputan sampah pertama Anda.</p>
                        </div>
                    `,
                    confirmButtonColor: 'var(--primary)',
                    confirmButtonText: 'Lanjut ke Dashboard',
                    footer: '<img src="https://img.icons8.com/color/48/000000/recycle-sign.png" width="30" style="margin-right: 5px;"> Terima kasih telah peduli terhadap lingkungan!'
                }).then(() => {
                    // Reset form and button state
                    this.reset();
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                    document.getElementById('password-strength').textContent = '';
                    
                    // Redirect to dashboard (simulated)
                    window.location.href = 'dashboard.php';
                });
            }, 1500);
        });

        // Real-time password validation
        document.getElementById('confirm-password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const errorElement = document.getElementById('password-error');
            
            if (password !== confirmPassword && confirmPassword !== '') {
                errorElement.style.display = 'block';
                this.style.borderColor = 'var(--error)';
            } else {
                errorElement.style.display = 'none';
                this.style.borderColor = '';
            }
        });</script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>