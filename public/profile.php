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
$user_photo = $_SESSION['user']['photo'] ?? $_SESSION['profile_image'] ?? '';

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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ubah Nama
    if (isset($_POST['change_name'])) {
        $new_name = trim($_POST['new_name']);
        $password = $_POST['name_password'];
        
        // Validasi nama
        if (empty($new_name)) {
            $error = "Nama tidak boleh kosong";
            $message_type = 'error';
        } elseif (strlen($new_name) < 2) {
            $error = "Nama harus minimal 2 karakter";
            $message_type = 'error';
        } elseif (strlen($new_name) > 100) {
            $error = "Nama maksimal 100 karakter";
            $message_type = 'error';
        } elseif (!preg_match('/^[a-zA-Z\s]+$/', $new_name)) {
            $error = "Nama hanya boleh mengandung huruf dan spasi";
            $message_type = 'error';
        } else {
            // Verifikasi password
            $stmt = $conn->prepare("SELECT pass FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            
            if ($user_data && password_verify($password, $user_data['pass'])) {
                // Update nama
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $new_name, $user_id);
                
                if ($stmt->execute()) {
                    $message = "Nama berhasil diubah";
                    $message_type = 'success';
                    // Refresh data user
                    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                    
                    // Update session data
                    $_SESSION['user']['name'] = $new_name;
                } else {
                    $error = "Gagal mengubah nama";
                    $message_type = 'error';
                }
            } else {
                $error = "Password tidak benar";
                $message_type = 'error';
            }
        }
    }
    
    // Ubah Username
    if (isset($_POST['change_username'])) {
        $new_username = trim($_POST['new_username']);
        $password = $_POST['username_password'];
        
        // Validasi username
        if (empty($new_username)) {
            $error = "Username tidak boleh kosong";
            $message_type = 'error';
        } elseif (strlen($new_username) < 3) {
            $error = "Username harus minimal 3 karakter";
            $message_type = 'error';
        } elseif (strlen($new_username) > 30) {
            $error = "Username maksimal 30 karakter";
            $message_type = 'error';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $new_username)) {
            $error = "Username hanya boleh mengandung huruf, angka, dan underscore";
            $message_type = 'error';
        } else {
            // Verifikasi password
            $stmt = $conn->prepare("SELECT pass FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            
            if ($user_data && password_verify($password, $user_data['pass'])) {
                // Cek apakah username sudah digunakan
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->bind_param("si", $new_username, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->fetch_assoc()) {
                    $error = "Username sudah digunakan";
                    $message_type = 'error';
                } else {
                    // Update username
                    $stmt = $conn->prepare("UPDATE users SET username = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("si", $new_username, $user_id);
                    
                    if ($stmt->execute()) {
                        $message = "Username berhasil diubah";
                        $message_type = 'success';
                        // Refresh data user
                        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                        
                        // Update session data
                        $_SESSION['user']['username'] = $new_username;
                    } else {
                        $error = "Gagal mengubah username";
                        $message_type = 'error';
                    }
                }
            } else {
                $error = "Password tidak benar";
                $message_type = 'error';
            }
        }
    }
    
    // Ubah Foto Profil
    if (isset($_POST['change_photo'])) {
        $password = $_POST['photo_password'];
        
        // Verifikasi password
        $stmt = $conn->prepare("SELECT pass FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
        if ($user_data && password_verify($password, $user_data['pass'])) {
            // Proses upload foto
            if (isset($_FILES['profil_image']) && $_FILES['profil_image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                $file_type = $_FILES['profil_image']['type'];
                $file_size = $_FILES['profil_image']['size'];
                $file_name = $_FILES['profil_image']['name'];
                $file_tmp = $_FILES['profil_image']['tmp_name'];
                
                // Validasi tipe file
                if (!in_array($file_type, $allowed_types)) {
                    $error = "Tipe file tidak didukung. Hanya JPG, JPEG, PNG, dan GIF yang diizinkan";
                    $message_type = 'error';
                } elseif ($file_size > $max_size) {
                    $error = "Ukuran file terlalu besar. Maksimal 2MB";
                    $message_type = 'error';
                } else {
                    // Generate nama file unik
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                    $upload_path = __DIR__ . '/../uploads/profiles/';
                    
                    // Buat direktori jika belum ada
                    if (!file_exists($upload_path)) {
                        mkdir($upload_path, 0755, true);
                    }
                    
                    $full_path = $upload_path . $new_filename;
                    
                    // Upload file
                    if (move_uploaded_file($file_tmp, $full_path)) {
                        // Hapus foto lama jika ada
                        if (!empty($user['profil_image']) && file_exists($upload_path . $user['profil_image'])) {
                            unlink($upload_path . $user['profil_image']);
                        }
                        
                        // Update database
                        $stmt = $conn->prepare("UPDATE users SET profil_image = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->bind_param("si", $new_filename, $user_id);
                        
                        if ($stmt->execute()) {
                            $message = "Foto profil berhasil diubah";
                            $message_type = 'success';
                            // Refresh data user
                            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $user = $result->fetch_assoc();
                            
                            // Update session data
                            $_SESSION['user']['photo'] = $new_filename;
                            $_SESSION['profile_image'] = $new_filename;
                        } else {
                            $error = "Gagal menyimpan foto profil ke database";
                            $message_type = 'error';
                        }
                    } else {
                        $error = "Gagal mengupload foto profil";
                        $message_type = 'error';
                    }
                }
            } else {
                $error = "Silakan pilih foto profil yang akan diupload";
                $message_type = 'error';
            }
        } else {
            $error = "Password tidak benar";
            $message_type = 'error';
        }
    }
    
    // Hapus Foto Profil
    if (isset($_POST['remove_photo'])) {
        $password = $_POST['remove_password'];
        
        // Verifikasi password
        $stmt = $conn->prepare("SELECT pass FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
        if ($user_data && password_verify($password, $user_data['pass'])) {
            if (!empty($user['profil_image'])) {
                $upload_path = __DIR__ . '/../uploads/profiles/';
                $photo_path = $upload_path . $user['profil_image']; // Perbaikan di sini
                
                // Hapus file foto
                if (file_exists($photo_path)) {
                    unlink($photo_path);
                }
                
                // Update database
                $stmt = $conn->prepare("UPDATE users SET profil_image = NULL, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                
                if ($stmt->execute()) {
                    $message = "Foto profil berhasil dihapus";
                    $message_type = 'success';
                    // Refresh data user
                    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                    
                    // Update session data
                    $_SESSION['user']['photo'] = null;
                    $_SESSION['profile_image'] = null;
                } else {
                    $error = "Gagal menghapus foto profil dari database";
                    $message_type = 'error';
                }
            } else {
                $error = "Tidak ada foto profil yang dapat dihapus";
                $message_type = 'error';
            }
        } else {
            $error = "Password tidak benar";
            $message_type = 'error';
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
            <h1 class="text-2xl md:text-3xl font-bold text-white">Profil Saya</h1>
            <p class="text-sky-100 mt-1">Kelola informasi profil dan foto Anda</p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Profile Overview -->
            <div class="bg-gradient-to-r from-sky-50 to-blue-50 rounded-lg p-6 mb-8">
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <div class="relative">
                        <?php if (!empty($user['profil_image'])): ?>
                        <img src="<?= BASE_URL ?>uploads/profiles/<?= htmlspecialchars($user['profil_image']) ?>"
                            alt="Profile Photo"
                            class="w-24 h-24 md:w-32 md:h-32 rounded-full object-cover border-4 border-white shadow-lg"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div
                            class="w-24 h-24 md:w-32 md:h-32 rounded-full bg-gradient-to-br from-sky-400 to-blue-500 items-center justify-center border-4 border-white shadow-lg hidden">
                            <i class="fas fa-user text-white text-3xl md:text-4xl"></i>
                        </div>
                        <?php else: ?>
                        <div
                            class="w-24 h-24 md:w-32 md:h-32 rounded-full bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center border-4 border-white shadow-lg">
                            <i class="fas fa-user text-white text-3xl md:text-4xl"></i>
                        </div>
                        <?php endif; ?>
                        <div class="absolute -bottom-2 -right-2 bg-sky-500 rounded-full p-2 shadow-lg">
                            <i class="fas fa-camera text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="text-center md:text-left">
                        <h2 class="text-2xl font-bold text-gray-800">
                            <?= htmlspecialchars($user['full_name'] ?? 'Nama Belum Diatur') ?>
                        </h2>
                        <p class="text-gray-600 text-lg">@<?= htmlspecialchars($user['username'] ?? 'username') ?></p>
                        <p class="text-sky-600 font-medium mt-1">
                            <i
                                class="fas fa-envelope mr-2"></i><?= htmlspecialchars($user['email'] ?? 'Email Belum Diatur') ?>
                        </p>
                        <p class="text-gray-500 text-sm mt-2">
                            <i class="fas fa-calendar mr-1"></i>
                            Bergabung sejak <?= date('d F Y', strtotime($user['create_at'])) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Change Name Section -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-blue-500 mb-4">
                        <i class="fas fa-user mr-2"></i>Ubah Nama
                    </h2>
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-black">
                            <i class="fas fa-info-circle mr-1"></i>
                            Nama saat ini:
                            <strong><?= htmlspecialchars($user['full_name'] ?? 'Belum diatur') ?></strong>
                        </p>
                    </div>
                    <form method="POST" class="space-y-4" id="changeNameForm">
                        <div>
                            <label for="new_name" class="block font-medium text-black mb-2">
                                Nama Lengkap Baru
                            </label>
                            <input type="text" name="new_name" id="new_name" required
                                placeholder="Masukkan nama lengkap"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            <p class="text-sm text-gray-600 mt-1">Minimal 2 karakter, hanya huruf dan spasi</p>
                        </div>
                        <div>
                            <label for="name_password" class="block font-medium text-black mb-2">
                                Password Anda
                            </label>
                            <input type="password" name="name_password" id="name_password" required
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <button type="submit" name="change_name"
                            class="w-full bg-sky-500 text-white py-2 px-4 rounded-lg hover:bg-sky-600 transition-colors font-medium cursor-pointer">
                            <i class="fas fa-save mr-2"></i>Ubah Nama
                        </button>
                    </form>
                </div>

                <!-- Change Username Section -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-blue-500 mb-4">
                        <i class="fas fa-at mr-2"></i>Ubah Username
                    </h2>
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-black">
                            <i class="fas fa-info-circle mr-1"></i>
                            Username saat ini:
                            <strong>@<?= htmlspecialchars($user['username'] ?? 'belum_diatur') ?></strong>
                        </p>
                    </div>
                    <form method="POST" class="space-y-4" id="changeUsernameForm">
                        <div>
                            <label for="new_username" class="block font-medium text-black mb-2">
                                Username Baru
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">@</span>
                                <input type="text" name="new_username" id="new_username" required
                                    placeholder="username_baru"
                                    class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 pl-8 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            </div>
                            <p class="text-sm text-gray-600 mt-1">3-30 karakter, huruf, angka, dan underscore</p>
                        </div>
                        <div>
                            <label for="username_password" class="block font-medium text-black mb-2">
                                Password Anda
                            </label>
                            <input type="password" name="username_password" id="username_password" required
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <button type="submit" name="change_username"
                            class="w-full bg-sky-500 text-white py-2 px-4 rounded-lg hover:bg-sky-600 transition-colors font-medium cursor-pointer">
                            <i class="fas fa-save mr-2"></i>Ubah Username
                        </button>
                    </form>
                </div>
            </div>

            <!-- Photo Management Section -->
            <div class="mt-8 bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-blue-500 mb-4">
                    <i class="fas fa-camera mr-2"></i>Kelola Foto Profil
                </h2>

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Upload Photo -->
                    <div>
                        <h3 class="font-medium text-black mb-3">Upload Foto Baru</h3>
                        <form method="POST" enctype="multipart/form-data" class="space-y-4" id="changePhotoForm">
                            <div>
                                <label for="profil_image" class="block font-medium text-black mb-2">
                                    Pilih Foto Profil
                                </label>
                                <input type="file" name="profil_image" id="profil_image" required
                                    accept="image/jpeg,image/jpg,image/png,image/gif"
                                    class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                                <p class="text-sm text-gray-600 mt-1">JPG, JPEG, PNG, GIF - Maksimal 2MB</p>
                            </div>

                            <!-- Preview Image -->
                            <div id="imagePreview" class="hidden">
                                <p class="text-sm font-medium text-black mb-2">Preview:</p>
                                <img id="previewImg" src="" alt="Preview"
                                    class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                            </div>

                            <div>
                                <label for="photo_password" class="block font-medium text-black mb-2">
                                    Password Anda
                                </label>
                                <input type="password" name="photo_password" id="photo_password" required
                                    class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            </div>
                            <button type="submit" name="change_photo"
                                class="w-full bg-sky-500 text-white py-2 px-4 rounded-lg hover:bg-sky-600 transition-colors font-medium cursor-pointer">
                                <i class="fas fa-upload mr-2"></i>Upload Foto
                            </button>
                        </form>
                    </div>

                    <!-- Remove Photo -->
                    <?php if (!empty($user['profil_image'])): ?>
                    <div>
                        <h3 class="font-medium text-red-600 mb-3">Hapus Foto Profil</h3>
                        <div class="mb-4 p-3 bg-red-50 rounded-lg">
                            <p class="text-sm text-red-700">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Foto profil saat ini akan dihapus permanen
                            </p>
                        </div>
                        <form method="POST" class="space-y-4" id="removePhotoForm">
                            <div>
                                <label for="remove_password" class="block font-medium text-black mb-2">
                                    Password Anda
                                </label>
                                <input type="password" name="remove_password" id="remove_password" required
                                    class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            </div>
                            <button type="submit" name="remove_photo"
                                class="w-full bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors font-medium cursor-pointer">
                                <i class="fas fa-trash mr-2"></i>Hapus Foto
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="flex items-center justify-center bg-gray-100 rounded-lg p-8">
                        <div class="text-center text-gray-500">
                            <i class="fas fa-image text-4xl mb-3"></i>
                            <p class="text-sm">Belum ada foto profil</p>
                            <p class="text-xs text-gray-400 mt-1">Upload foto untuk melengkapi profil</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Profile Tips -->
            <div class="mt-8 bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-blue-500 mb-4">
                    <i class="fas fa-lightbulb mr-2"></i>Tips Profil
                </h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-lg border">
                        <h3 class="font-medium text-blue-500 mb-2">Nama & Username</h3>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>• Gunakan nama asli untuk kredibilitas</li>
                            <li>• Username mudah diingat dan unik</li>
                            <li>• Hindari karakter khusus pada username</li>
                            <li>• Periksa ejaan sebelum menyimpan</li>
                        </ul>
                    </div>
                    <div class="bg-white p-4 rounded-lg border">
                        <h3 class="font-medium text-blue-500 mb-2">Foto Profil</h3>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>• Gunakan foto yang jelas dan profesional</li>
                            <li>• Ukuran file maksimal 2MB</li>
                            <li>• Format JPG, PNG, atau GIF</li>
                            <li>• Foto wajah lebih personal dan terpercaya</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($message && $message_type === 'success'): ?>
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

    // Image preview functionality
    document.getElementById('profil_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');

        if (file) {
            // Validasi ukuran file (2MB)
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file maksimal 2MB',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0ea5e9'
                });
                e.target.value = '';
                preview.classList.add('hidden');
                return;
            }

            // Validasi tipe file
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    title: 'Tipe File Tidak Didukung',
                    text: 'Hanya file JPG, JPEG, PNG, dan GIF yang diizinkan',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0ea5e9'
                });
                e.target.value = '';
                preview.classList.add('hidden');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            preview.classList.add('hidden');
        }
    });

    // Form validation
    document.getElementById('changeNameForm').addEventListener('submit', function(e) {
        const newName = document.getElementById('new_name').value.trim();
        const currentName = '<?= addslashes($user['name'] ?? '') ?>';

        if (newName.length < 2) {
            e.preventDefault();
            Swal.fire({
                title: 'Nama Terlalu Pendek',
                text: 'Nama harus minimal 2 karakter',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }

        if (newName.length > 100) {
            e.preventDefault();
            Swal.fire({
                title: 'Nama Terlalu Panjang',
                text: 'Nama maksimal 100 karakter',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }

        if (!/^[a-zA-Z\s]+$/.test(newName)) {
            e.preventDefault();
            Swal.fire({
                title: 'Format Nama Tidak Valid',
                text: 'Nama hanya boleh mengandung huruf dan spasi',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }

        if (newName === currentName) {
            e.preventDefault();
            Swal.fire({
                title: 'Nama Sama',
                text: 'Nama baru tidak boleh sama dengan nama saat ini',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }
    });

    document.getElementById('changeUsernameForm').addEventListener('submit', function(e) {
        const newUsername = document.getElementById('new_username').value.trim();
        const currentUsername = '<?= addslashes($user['username'] ?? '') ?>';

        if (newUsername.length < 3) {
            e.preventDefault();
            Swal.fire({
                title: 'Username Terlalu Pendek',
                text: 'Username harus minimal 3 karakter',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }

        if (newUsername.length > 30) {
            e.preventDefault();
            Swal.fire({
                title: 'Username Terlalu Panjang',
                text: 'Username maksimal 30 karakter',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }

        if (!/^[a-zA-Z0-9_]+$/.test(newUsername)) {
            e.preventDefault();
            Swal.fire({
                title: 'Format Username Tidak Valid',
                text: 'Username hanya boleh mengandung huruf, angka, dan underscore',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }

        if (newUsername === currentUsername) {
            e.preventDefault();
            Swal.fire({
                title: 'Username Sama',
                text: 'Username baru tidak boleh sama dengan username saat ini',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }
    });

    // Confirm photo removal
    document.getElementById('removePhotoForm').addEventListener('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: 'Foto profil akan dihapus secara permanen',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Clear password fields after successful submission
    <?php if ($message_type === 'success'): ?>
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