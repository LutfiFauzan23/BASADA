<?php
session_start();
include "connect.php";

$errors = [];
$old_input = [];

if(isset($_POST['login'])) {
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $password = $_POST['password'];
    
    // Simpan input lama
    $old_input = ['email' => $email];
    
    // Validasi
    if(empty($email)) {
        $errors['email'] = "Email harus diisi";
    }
    if(empty($password)) {
        $errors['password'] = "Password harus diisi";
    }
    
    if(empty($errors)) {
        // Cari user berdasarkan email
        $query = mysqli_prepare($connect, "SELECT id, nama, email, password FROM user WHERE email = ?");
        
        if($query) {
            mysqli_stmt_bind_param($query, "s", $email);
            mysqli_stmt_execute($query);
            $result = mysqli_stmt_get_result($query);
            
            if(mysqli_num_rows($result) == 1) {
                $user = mysqli_fetch_assoc($result);
                $hashed_password = $user['password'];
                
                // Verifikasi password
                if(password_verify($password, $hashed_password)) {
                    // Login berhasil
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['alamat_email'] = $user['email'];
                    $_SESSION['logged_in'] = true;
                    
                    // Redirect langsung tanpa SweetAlert (lebih reliable)
                    header("Location: ../frontend/dashboard.php");
                    exit;
                } else {
                    $errors['password'] = "Password salah";
                }
            } else {
                $errors['email'] = "Email tidak terdaftar";
            }
            
            mysqli_stmt_close($query);
        } else {
            $errors['general'] = "Error database";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bank Sampah Digital (BASADA)</title>
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
            max-width: 450px;
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
            margin-bottom: 15px;
        }
        
        h1 {
            color: var(--primary-dark);
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
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
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.8);
        }
        
        input:focus {
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
        
        .login-btn {
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
        }
        
        .login-btn:hover {
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
        }
        
        .login-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: var(--text-light);
        }
        
        .register-link a {
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link a:hover {
            text-decoration: underline;
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
            <h1>Login Nasabah</h1>
            <p class="subtitle">Masuk ke akun Bank Sampah Digital Anda</p>
            <p class="help-desk">
                <strong>Bank Sampah Digital (BASADA)</strong><br>
                Help Desk: <strong>0877-7760-4327</strong>
            </p>
        </div>

        <!-- Tampilkan error general -->
        <?php if(isset($errors['general'])): ?>
            <div class="general-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>
        
        <!-- Tampilkan error jika login gagal -->
        <?php if(isset($_GET['error']) && $_GET['error'] == 'login_failed'): ?>
            <div class="general-error">
                <i class="fas fa-exclamation-triangle"></i> Email atau password salah
            </div>
        <?php endif; ?>
        
        <form method="POST" id="loginForm">
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
                <label for="password"><i class="fas fa-lock" style="margin-right: 5px;"></i> Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="Masukkan password Anda" 
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
            </div>
            
            <div class="form-group" style="margin-top: 25px;">
                <button type="submit" class="login-btn" name="login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </div>
        </form>
        
        <div class="register-link">
            Belum punya akun? <a href="register.php"><i class="fas fa-user-plus"></i> Daftar disini</a>
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

        // Clear error styles on input
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('input-error');
                const errorMessage = this.parentNode.parentNode.querySelector('.error-message');
                if (errorMessage) {
                    errorMessage.style.display = 'none';
                }
            });
        });

        // Auto focus ke field yang error
        document.addEventListener('DOMContentLoaded', function() {
            <?php if(isset($errors['email'])): ?>
                document.getElementById('email').focus();
            <?php elseif(isset($errors['password'])): ?>
                document.getElementById('password').focus();
            <?php endif; ?>
        });
    </script>
</body>
</html>