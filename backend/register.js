        // Toggle password visibility
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
                    // window.location.href = 'dashboard.html';
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
        });