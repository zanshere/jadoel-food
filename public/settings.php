<?php
include_once __DIR__ . "/../config/connect.php";
include_once __DIR__ . "/../config/baseURL.php";

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['role'] !== 'customer') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

// Ambil user_id dari session
$user_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$message = '';
$error = '';
$message_type = '';

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

// Handle form submissions
// Ubah Password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validasi password
        if (strlen($new_password) < 6) {
            $error = "Password baru harus minimal 6 karakter";
            $message_type = 'error';
        } elseif ($new_password !== $confirm_password) {
            $error = "Konfirmasi password tidak cocok";
            $message_type = 'error';
        } else {
            // Verifikasi password lama
            $stmt = $conn->prepare("SELECT pass FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            
            if ($user_data && password_verify($current_password, $user_data['pass'])) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET pass = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($stmt->execute()) {
                    $message = "Password berhasil diubah";
                    $message_type = 'success';
                } else {
                    $error = "Gagal mengubah password";
                    $message_type = 'error';
                }
            } else {
                $error = "Password lama tidak benar";
                $message_type = 'error';
            }
        }
    }
    
    // Ubah Email
    if (isset($_POST['change_email'])) {
        $new_email = trim($_POST['new_email']);
        $password = $_POST['email_password'];
        
        // Validasi email
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid";
            $message_type = 'error';
        } else {
            // Verifikasi password
            $stmt = $conn->prepare("SELECT pass FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            
            if ($user_data && password_verify($password, $user_data['pass'])) {
                // Cek apakah email sudah digunakan
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->bind_param("si", $new_email, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->fetch_assoc()) {
                    $error = "Email sudah digunakan";
                    $message_type = 'error';
                } else {
                    // Update email
                    $stmt = $conn->prepare("UPDATE users SET email = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("si", $new_email, $user_id);
                    
                    if ($stmt->execute()) {
                        $message = "Email berhasil diubah";
                        $message_type = 'success';
                        // Refresh data user
                        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                    } else {
                        $error = "Gagal mengubah email";
                        $message_type = 'error';
                    }
                }
            } else {
                $error = "Password tidak benar";
                $message_type = 'error';
            }
        }
    }
    
    // Ubah nomor telepon
    if (isset($_POST['change_phone'])) {
        $new_phone = trim($_POST['new_phone']);
        $password = $_POST['phone_password'];
        
        // Validasi nomor telepon
        if (empty($new_phone)) {
            $error = "Nomor telepon tidak boleh kosong";
            $message_type = 'error';
        } elseif (!preg_match('/^[0-9+\(\)\-\s]+$/', $new_phone)) {
            $error = "Format nomor telepon tidak valid";
            $message_type = 'error';
        } else {
            // Verifikasi password
            $stmt = $conn->prepare("SELECT pass FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            
            if ($user_data && password_verify($password, $user_data['pass'])) {
                // Update nomor telepon
                $stmt = $conn->prepare("UPDATE users SET phone = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $new_phone, $user_id);
                
                if ($stmt->execute()) {
                    $message = "Nomor telepon berhasil diubah";
                    $message_type = 'success';
                    // Refresh data user
                    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                } else {
                    $error = "Gagal mengubah nomor telepon";
                    $message_type = 'error';
                }
            } else {
                $error = "Password tidak benar";
                $message_type = 'error';
            }
        }
    }
    
    // Delete Akun User - 
    if (isset($_POST['delete_account'])) {
        $confirm_delete = trim($_POST['confirm_delete']);
        $password = $_POST['delete_password'];
        
        // Validasi konfirmasi penghapusan
        if ($confirm_delete !== 'HAPUS') {
            $error = "Ketik 'HAPUS' untuk konfirmasi penghapusan akun";
            $message_type = 'error';
        } elseif (empty($password)) {
            $error = "Password harus diisi";
            $message_type = 'error';
        } else {
            // Verifikasi password
            $stmt = $conn->prepare("SELECT pass FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            
            if ($user_data && password_verify($password, $user_data['pass'])) {
                // Hapus akun dengan proper prepared statement
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    // Set session flag untuk account deleted
                    $_SESSION['account_deleted'] = true;
                    $_SESSION['delete_message'] = "Akun Anda berhasil dihapus secara permanen";
                    $message_type = 'account_deleted';
                } else {
                    $error = "Gagal menghapus akun. Silakan coba lagi";
                    $message_type = 'error';
                }
            } else {
                $error = "Password yang Anda masukkan salah";
                $message_type = 'error';
            }
        }
    }
}

include_once __DIR__ . "/../includes/header.php";

?>

<!-- Main Content -->
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-sky-500 px-6 py-4">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Pengaturan Akun</h1>
            <p class="text-sky-100 mt-1">Kelola keamanan dan preferensi akun Anda</p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Change Password Section -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-blue-500 mb-4">
                        <i class="fas fa-lock mr-2"></i>Ubah Password
                    </h2>
                    <form method="POST" class="space-y-4" id="changePasswordForm">
                        <div>
                            <label for="current_password" class="block font-medium text-black mb-2">
                                Password Saat Ini
                            </label>
                            <input type="password" name="current_password" id="current_password" required
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="new_password" class="block font-medium text-black mb-2">
                                Password Baru
                            </label>
                            <input type="password" name="new_password" id="new_password" required minlength="6"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            <p class="text-sm text-gray-600 mt-1">Minimal 6 karakter</p>
                        </div>
                        <div>
                            <label for="confirm_password" class="block font-medium text-black mb-2">
                                Konfirmasi Password Baru
                            </label>
                            <input type="password" name="confirm_password" id="confirm_password" required
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <button type="submit" name="change_password"
                            class="w-full bg-sky-500 text-white py-2 px-4 rounded-lg hover:bg-sky-600 transition-colors font-medium cursor-pointer">
                            <i class="fas fa-save mr-2"></i>Ubah Password
                        </button>
                    </form>
                </div>

                <!-- Change Email Section -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-blue-500 mb-4">
                        <i class="fas fa-envelope mr-2"></i>Ubah Email
                    </h2>
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-black">
                            <i class="fas fa-info-circle mr-1"></i>
                            Email saat ini:
                            <strong><?= isset($user['email']) && $user['email'] ? htmlspecialchars($user['email']) : 'Belum diatur' ?></strong>
                        </p>
                    </div>
                    <form method="POST" class="space-y-4" id="changeEmailForm">
                        <div>
                            <label for="new_email" class="block font-medium text-black mb-2">
                                Email Baru
                            </label>
                            <input type="email" name="new_email" id="new_email" required
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="email_password" class="block font-medium text-black mb-2">
                                Password Anda
                            </label>
                            <input type="password" name="email_password" id="email_password" required
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <button type="submit" name="change_email"
                            class="w-full bg-sky-500 text-white py-2 px-4 rounded-lg hover:bg-sky-600 transition-colors font-medium cursor-pointer">
                            <i class="fas fa-save mr-2"></i>Ubah Email
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8 mt-8">
                <!-- Change Phone Section -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-blue-500 mb-4">
                        <i class="fas fa-phone mr-2"></i>Ubah Nomor Telepon
                    </h2>
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-black">
                            <i class="fas fa-info-circle mr-1"></i>
                            Nomor saat ini:
                            <strong><?= isset($user['phone']) && $user['phone'] ? htmlspecialchars($user['phone']) : 'Belum diatur' ?></strong>
                        </p>
                    </div>
                    <form method="POST" class="space-y-4" id="changePhoneForm">
                        <div>
                            <label for="new_phone" class="block font-medium text-black mb-2">
                                Nomor Telepon Baru
                            </label>
                            <input type="tel" name="new_phone" id="new_phone" required
                                placeholder="contoh: 081234567890"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            <p class="text-sm text-gray-600 mt-1">Format: 08xxx</p>
                        </div>
                        <div>
                            <label for="phone_password" class="block font-medium text-black mb-2">
                                Password Anda
                            </label>
                            <input type="password" name="phone_password" id="phone_password" required
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <button type="submit" name="change_phone"
                            class="w-full bg-sky-500 text-white py-2 px-4 rounded-lg hover:bg-sky-600 transition-colors font-medium cursor-pointer">
                            <i class="fas fa-save mr-2"></i>Ubah Nomor Telepon
                        </button>
                    </form>
                </div>

                <!-- Delete Account Section -->
                <div class="bg-red-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-red-600 mb-4">
                        <i class="fas fa-trash-alt mr-2 text-red"></i>Hapus Akun
                    </h2>
                    <div class="mb-4">
                        <p class="text-red-600 text-sm mb-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan!
                        </p>
                        <p class="text-gray-700 text-sm">
                            Semua data akun Anda akan dihapus secara permanen, termasuk riwayat pesanan dan
                            informasi profil.
                        </p>
                    </div>

                    <form method="POST" class="space-y-4" id="deleteAccountForm">
                        <div>
                            <label for="confirm_delete" class="block font-medium text-black mb-2">
                                Ketik "HAPUS" untuk konfirmasi
                            </label>
                            <input type="text" name="confirm_delete" id="confirm_delete" required placeholder="HAPUS"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="delete_password" class="block font-medium text-black mb-2">
                                Password Anda
                            </label>
                            <input type="password" name="delete_password" id="delete_password" required
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        </div>
                        <button type="submit" name="delete_account"
                            class="w-full bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors font-medium cursor-pointer">
                            <i class="fas fa-trash-alt mr-2"></i>Hapus Akun Permanen
                        </button>
                    </form>
                </div>
            </div>

            <!-- Additional Settings -->
            <div class="mt-8 bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-blue-500 mb-4">
                    <i class="fas fa-shield-alt mr-2"></i>Informasi Keamanan
                </h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-lg border">
                        <h3 class="font-medium text-blue-500 mb-2">Tips Keamanan</h3>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>• Gunakan password yang kuat dan unik</li>
                            <li>• Jangan bagikan informasi login Anda</li>
                            <li>• Logout setelah selesai menggunakan</li>
                            <li>• Periksa aktivitas akun secara berkala</li>
                        </ul>
                    </div>
                    <div class="bg-white p-4 rounded-lg border">
                        <h3 class="font-medium text-blue-500 mb-2">Dukungan</h3>
                        <p class="text-sm text-gray-700 mb-2">
                            Butuh bantuan dengan akun Anda?
                        </p>
                        <a href="javascript:void(0)" class="text-sky-500 hover:text-sky-600 text-sm font-medium">
                            <i class="fas fa-envelope mr-1"></i>Hubungi Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script SweetAlert -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($message_type === 'account_deleted'): ?>
    // Jika akun berhasil dihapus, tampilkan pesan dan redirect ke logout
    Swal.fire({
        title: 'Akun Berhasil Dihapus!',
        text: '<?= addslashes($_SESSION['delete_message'] ?? "Akun Anda telah berhasil dihapus secara permanen") ?>',
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0ea5e9',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect ke logout untuk membersihkan session
            window.location.href = '<?= BASE_URL ?>auth/logout.php';
        }
    });
    <?php elseif ($message && $message_type === 'success'): ?>
    Swal.fire({
        title: 'Berhasil!',
        text: '<?= addslashes($message) ?>',
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0ea5e9'
    });
    <?php elseif ($error && $message_type === 'error'): ?>
    Swal.fire({
        title: 'Error!',
        text: '<?= addslashes($error) ?>',
        icon: 'error',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0ea5e9'
    });
    <?php endif; ?>

    // Fungsi untuk konfirmasi penghapusan akun
    function confirmDelete(e) {
        e.preventDefault();

        const confirmText = document.getElementById('confirm_delete').value.trim();
        const password = document.getElementById('delete_password').value;
        const form = document.getElementById('deleteAccountForm');

        // Validasi input
        if (confirmText !== 'HAPUS') {
            Swal.fire({
                title: 'Konfirmasi Tidak Valid',
                text: 'Ketik "HAPUS" (huruf kapital) untuk konfirmasi penghapusan akun',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return;
        }

        // Kirim permintaan AJAX untuk memverifikasi password
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= BASE_URL ?>auth/verifyPassword.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (this.status === 200) {
                const response = JSON.parse(this.responseText);
                if (response.valid) {
                    // Jika password valid, lanjutkan dengan konfirmasi penghapusan
                    showDeleteConfirmation(form);
                } else {
                    // Jika password tidak valid, tampilkan pesan error dari PHP
                    Swal.fire({
                        title: 'Password Salah',
                        text: '<?= addslashes($error ?? "Password yang Anda masukkan salah") ?>',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0ea5e9'
                    });
                }
            }
        };
        xhr.send('password=' + encodeURIComponent(password));
    }

    // Fungsi untuk menampilkan konfirmasi penghapusan akhir
    function showDeleteConfirmation(form) {
        Swal.fire({
            title: 'Apakah Anda Benar-Benar Yakin?',
            html: `
                    <div class="text-left">
                        <p class="mb-3"><strong>Akun Anda akan dihapus secara permanen!</strong></p>
                        <p class="text-red-600 text-sm mb-2">Yang akan hilang:</p>
                        <ul class="text-sm text-gray-700 list-disc list-inside space-y-1">
                            <li>Data profil dan informasi akun</li>
                            <li>Riwayat pesanan dan transaksi</li>
                            <li>Semua data terkait akun</li>
                        </ul>
                        <p class="text-red-600 text-sm mt-3"><strong>Tindakan ini TIDAK DAPAT dibatalkan!</strong></p>
                    </div>
                `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus Permanen!',
            cancelButtonText: 'Batalkan',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading
                Swal.fire({
                    title: 'Menghapus Akun...',
                    text: 'Mohon tunggu, sedang memproses penghapusan akun',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit form
                form.submit();
            }
        });
    }

    // Event listener untuk form delete
    document.getElementById('deleteAccountForm').addEventListener('submit', confirmDelete);

    // Form validation dengan SweetAlert
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword.length < 6) {
            e.preventDefault();
            Swal.fire({
                title: 'Password Terlalu Pendek',
                text: '<?= addslashes($error ?? "Password baru harus minimal 6 karakter") ?>',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }

        if (newPassword !== confirmPassword) {
            e.preventDefault();
            Swal.fire({
                title: 'Password Tidak Cocok',
                text: '<?= addslashes($error ?? "Konfirmasi password tidak sesuai dengan password baru") ?>',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }
    });

    document.getElementById('changeEmailForm').addEventListener('submit', function(e) {
        const currentEmail = '<?= isset($user['email']) ? addslashes($user['email']) : '' ?>';
        const newEmail = document.getElementById('new_email').value;

        if (currentEmail === newEmail) {
            e.preventDefault();
            Swal.fire({
                title: 'Email Sama',
                text: '<?= addslashes($error ?? "Email baru tidak boleh sama dengan email saat ini") ?>',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }
    });

    // Clear form setelah berhasil
    <?php if ($message_type === 'success'): ?>
    // Reset form yang berhasil
    setTimeout(function() {
        document.querySelectorAll('form input[type="password"]').forEach(input => {
            input.value = '';
        });
    }, 1000);
    <?php endif; ?>
});
</script>

</body>
</html>