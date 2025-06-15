<?php
include_once __DIR__ . "/../config/connect.php";
include_once __DIR__ . "/../config/baseURL.php";

$message = null;
$type = null;

if (isset($_POST['login'])) {
    $login_id = trim($_POST['login_id']); // Username atau email
    $password = $_POST['password'] ?? '';

    if (!$login_id || !$password) {
        $message = "Username/email dan password wajib diisi.";
        $type = "error";
    } else {
        // Ambil user berdasarkan username atau email, termasuk role
        $stmt = $conn->prepare("SELECT id, full_name, username, pass, email, phone, profil_image, role FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $login_id, $login_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['pass'])) {
                // Login berhasil, simpan ke session
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['phone'] = $user['phone'];
                $_SESSION['profile_image'] = $user['profil_image'];
                $_SESSION['role'] = strtolower($user['role']); // Normalize to lowercase

                // Struktur session yang sesuai dengan header.php
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['full_name'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'photo' => $user['profil_image'],
                    'role' => strtolower($user['role'])
                ];

                // Tentukan URL redirect berdasarkan role (case insensitive)
                $user_role = strtolower($user['role']);
                $redirect_url = '';
                
                if ($user_role === 'admin') {
                    $redirect_url = BASE_URL . "admin/dashboard.php";
                    $message = "Selamat datang " . $user['username'] . ". Login berhasil!";
                } else if ($user_role === 'customer' || $user_role === 'user') {
                    $redirect_url = BASE_URL . "index.php";
                    $message = "Selamat datang " . $user['username'] . ". Login berhasil!";
                } else {
                    $message = "Role pengguna tidak valid: " . $user['role'];
                    $type = "error";
                }

                if ($redirect_url) {
                    // Redirect dengan parameter untuk SweetAlert
                    header("Location: " . BASE_URL . "auth/login.php?status=success&message=" . urlencode($message) . "&redirect=" . urlencode($redirect_url));
                    exit;
                }
            } else {
                $message = "Password salah.";
                $type = "error";
            }
        } else {
            $message = "Akun tidak ditemukan.";
            $type = "error";
        }
        $stmt->close();
    }

    // Redirect dengan parameter URL jika ada error
    if ($message && $type) {
        header("Location: " . BASE_URL . "auth/login.php?status=" . $type . "&message=" . urlencode($message));
        exit;
    }
}
?>