<?php
session_start();
include "../backend/connect.php";

$errors = [];
$old_input = [];
$success = false;

if(isset($_POST['submit'])) {
    // Ambil data dari form
    $nama_lengkap = mysqli_real_escape_string($connect, $_POST['nama']);
    $alamat_email = mysqli_real_escape_string($connect, $_POST['email']);
    $no_hp = mysqli_real_escape_string($connect, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($connect, $_POST['alamat']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    
    // Simpan input lama
    $old_input = [
        'nama' => $nama_lengkap,
        'email' => $alamat_email,
        'no_hp' => $no_hp,
        'alamat' => $alamat
    ];
    
    // Validasi required fields
    if(empty($nama_lengkap)) $errors['nama'] = "Nama lengkap harus diisi";
    if(empty($alamat_email)) $errors['email'] = "Email harus diisi";
    if(empty($password)) $errors['password'] = "Password harus diisi";
    if(empty($confirm_password)) $errors['confirm_password'] = "Konfirmasi password harus diisi";
    if(empty($no_hp)) $errors['no_hp'] = "Nomor telepon harus diisi";
    if(empty($alamat)) $errors['alamat'] = "Alamat harus diisi";
    
    // Validasi format email
    if(!empty($alamat_email) && !filter_var($alamat_email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format email tidak valid";
    }
    
    // Validasi password match
    if(!empty($password) && !empty($confirm_password) && $password !== $confirm_password) {
        $errors['confirm_password'] = "Password dan konfirmasi password tidak sama";
    }
    
    // Validasi panjang password
    if(!empty($password) && strlen($password) < 6) {
        $errors['password'] = "Password minimal 6 karakter";
    }
    
    // Cek apakah email sudah terdaftar (hanya jika tidak ada error email)
    if(empty($errors['email']) && !empty($email)) {
        $check_email = mysqli_prepare($connect, "SELECT id FROM users WHERE email = ?");
        if($check_email) {
            mysqli_stmt_bind_param($check_email, "s", $email);
            mysqli_stmt_execute($check_email);
            mysqli_stmt_store_result($check_email);
            
            if(mysqli_stmt_num_rows($check_email) > 0) {
                $errors['email'] = "Email sudah terdaftar";
            }
            mysqli_stmt_close($check_email);
        } else {
            $errors['general'] = "Error database: " . mysqli_error($connect);
        }
    }
    
    // Cek apakah nomor HP sudah terdaftar (hanya jika tidak ada error no_hp)
    if(empty($errors['no_hp']) && !empty($no_hp)) {
        $check_hp = mysqli_prepare($connect, "SELECT id FROM user WHERE no_hp = ?");
        if($check_hp) {
            mysqli_stmt_bind_param($check_hp, "s", $no_hp);
            mysqli_stmt_execute($check_hp);
            mysqli_stmt_store_result($check_hp);
            
            if(mysqli_stmt_num_rows($check_hp) > 0) {
                $errors['no_hp'] = "Nomor telepon sudah terdaftar";
            }
            mysqli_stmt_close($check_hp);
        } else {
            $errors['general'] = "Error database: " . mysqli_error($connect);
        }
    }
    
    // Jika tidak ada error, proses pendaftaran
    if(empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Gunakan prepared statement untuk insert
        $query = mysqli_prepare($connect, "INSERT INTO user (nama, email, password, no_hp, alamat) VALUES (?, ?, ?, ?, ?)");
        
        if($query) {
            mysqli_stmt_bind_param($query, "sssss", $nama_lengkap, $alamat_email, $password_hash, $no_hp, $alamat);
            
            if(mysqli_stmt_execute($query)) {
                $success = true;
                // Clear old input setelah sukses
                $old_input = [];
            } else {
                $errors['general'] = "Pendaftaran gagal: " . mysqli_stmt_error($query);
            }
            
            mysqli_stmt_close($query);
        } else {
            $errors['general'] = "Error prepared statement: " . mysqli_error($connect);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Nasabah - Bank Sampah Digital (BASADA)</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
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
        
        .input-error {
            border-color: var(--error) !important;
            box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.1) !important;
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
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .error-message i {
            font-size: 14px;
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
        
        .register-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
        
        .eco-tip {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 5px;
            font-style: italic;
        }
        
        .general-error {
            background-color: #ffebee;
            border: 1px solid var(--error);
            color: var(--error);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
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
        }
    </style>
</head>
<body>
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

        <?php if(isset($errors['general'])): ?>
            <div class="general-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="registerForm">
            <div class="form-group">
                <label for="nama"><i class="fas fa-user" style="margin-right: 5px;"></i> Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" 
                       value="<?php echo isset($old_input['nama']) ? htmlspecialchars($old_input['nama']) : ''; ?>"
                       class="<?php echo isset($errors['nama']) ? 'input-error' : ''; ?>">
                <?php if(isset($errors['nama'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['nama']; ?>
                    </div>
                <?php endif; ?>
                <p class="eco-tip">Data Anda akan dijaga kerahasiaannya</p>
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope" style="margin-right: 5px;"></i> Alamat Email</label>
                <input type="email" id="email" name="email" placeholder="contoh@email.com" 
                       value="<?php echo isset($old_input['email']) ? htmlspecialchars($old_input['email']) : ''; ?>"
                       class="<?php echo isset($errors['email']) ? 'input-error' : ''; ?>">
                <?php if(isset($errors['email'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['email']; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="no_hp"><i class="fas fa-mobile-alt" style="margin-right: 5px;"></i> Nomor Handphone</label>
                <input type="tel" id="no_hp" name="no_hp" placeholder="0812 3456 7890" 
                       value="<?php echo isset($old_input['no_hp']) ? htmlspecialchars($old_input['no_hp']) : ''; ?>"
                       class="<?php echo isset($errors['no_hp']) ? 'input-error' : ''; ?>">
                <?php if(isset($errors['no_hp'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['no_hp']; ?>
                    </div>
                <?php endif; ?>
                <p class="eco-tip">Masukkan nomor Handphone sesuai dengan E-wallet yang anda pakai</p>
            </div>
            
            <div class="form-group">
                <label for="alamat"><i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i> Alamat Lengkap</label>
                <input type="text" id="alamat" name="alamat" placeholder="Jl. Contoh No. 123" 
                       value="<?php echo isset($old_input['alamat']) ? htmlspecialchars($old_input['alamat']) : ''; ?>"
                       class="<?php echo isset($errors['alamat']) ? 'input-error' : ''; ?>">
                <?php if(isset($errors['alamat'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['alamat']; ?>
                    </div>
                <?php endif; ?>
                <p class="eco-tip">Untuk memudahkan penjemputan sampah</p>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock" style="margin-right: 5px;"></i> Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" 
                           class="<?php echo isset($errors['password']) ? 'input-error' : ''; ?>">
                    <span class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <?php if(isset($errors['password'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['password']; ?>
                    </div>
                <?php endif; ?>
                <div id="password-strength" style="font-size: 12px; margin-top: 5px; color: var(--text-light);"></div>
            </div>
            
            <div class="form-group">
                <label for="confirm-password"><i class="fas fa-key" style="margin-right: 5px;"></i> Konfirmasi Password</label>
                <div class="password-container">
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Ketik ulang password" 
                           class="<?php echo isset($errors['confirm_password']) ? 'input-error' : ''; ?>">
                    <span class="toggle-password" onclick="togglePassword('confirm-password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <?php if(isset($errors['confirm_password'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errors['confirm_password']; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group" style="margin-top: 25px;">
                <button type="submit" class="register-btn" name="submit">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>
                <p class="eco-tip" style="text-align: center; margin-top: 10px;">
                    Dengan mendaftar, Anda menyetujui kebijakan pengelolaan sampah kami
                </p>
            </div>
        </form>
        
        <div class="login-link">
            Sudah punya akun? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Masuk disini</a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.parentNode.querySelector('.toggle-password i');
            
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
            
            if (password.length < 6) {
                strengthElement.textContent = '🔴 Lemah (minimal 6 karakter)';
                strengthElement.style.color = 'var(--error)';
            } else if (!/[A-Z]/.test(password) || !/[0-9]/.test(password) || !/[^A-Za-z0-9]/.test(password)) {
                strengthElement.textContent = '🟡 Sedang (gunakan kombinasi huruf, angka, dan simbol)';
                strengthElement.style.color = '#ff9800';
            } else {
                strengthElement.textContent = '🟢 Kuat - Siap mendukung lingkungan!';
                strengthElement.style.color = 'var(--primary)';
            }
        });

        // Real-time password confirmation check
        document.getElementById('confirm-password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.classList.add('input-error');
            } else {
                this.classList.remove('input-error');
            }
        });

        // Clear error styles on input
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('input-error');
            });
        });

        // SweetAlert untuk notifikasi
        <?php if($success): ?>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Pendaftaran Berhasil!',
                text: 'Akun Anda telah berhasil dibuat. Silakan login untuk melanjutkan.',
                icon: 'success',
                iconColor: '#2e7d32',
                confirmButtonText: 'OK',
                confirmButtonColor: '#2e7d32',
                customClass: {
                    popup: 'sweetalert-custom',
                    title: 'sweetalert-title',
                    confirmButton: 'sweetalert-confirm-button'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'login.php';
                }
            });
        });
        <?php elseif(isset($errors['general'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Ada Kesalahan!',
                text: '<?php echo $errors['general']; ?>',
                icon: 'error',
                iconColor: '#c62828',
                confirmButtonText: 'OK',
                confirmButtonColor: '#c62828',
                customClass: {
                    popup: 'sweetalert-custom',
                    title: 'sweetalert-title',
                    confirmButton: 'sweetalert-confirm-button'
                }
            });
        });
        <?php endif; ?>

        // Style tambahan untuk SweetAlert
        const style = document.createElement('style');
        style.textContent = `
            .sweetalert-custom {
                border-radius: 12px;
                border: 2px solid var(--primary);
            }
            .sweetalert-title {
                color: var(--primary-dark);
                font-family: 'Poppins', sans-serif;
            }
            .sweetalert-confirm-button {
                font-family: 'Poppins', sans-serif;
                font-weight: 600;
                border-radius: 8px;
            }
            .swal2-success [class^=swal2-success-line] {
                background-color: #2e7d32 !important;
            }
            .swal2-success .swal2-success-ring {
                border-color: #2e7d32 !important;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>