<?php 
session_start();
include_once __DIR__ . "/../config/connect.php";
include_once __DIR__ . "/../config/baseURL.php";

function validateImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return "File harus berupa gambar (jpeg, png, gif).";
    }
    if ($file['size'] > 2 * 1024 * 1024) { // max 2MB
        return "Ukuran file maksimal 2MB.";
    }
    return true;
}

$message = null;
$type = null;

if (isset($_POST['regist'])) {
    $name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $password = $_POST['pass'] ?? '';
    $repeat_password = $_POST['repeat_pass'] ?? '';
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Validasi kosong
    if (!$name || !$username || !$password || !$repeat_password || !$email || !$phone) {
        $message = "Semua field wajib diisi.";
        $type = "error";
    }
    // Validasi email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email tidak valid.";
        $type = "error";
    }
    // Validasi password minimal 8 karakter
    elseif (strlen($password) < 8) {
        $message = "Password minimal 8 karakter.";
        $type = "error";
    }
    // Validasi password sama
    elseif ($password !== $repeat_password) {
        $message = "Password dan repeat password harus sama.";
        $type = "error";
    } else {
        // Default profile image
        $profil_image = 'profil.jpg';

        // Jika ada file yang diupload
        if (isset($_FILES['profil_image']) && $_FILES['profil_image']['error'] === UPLOAD_ERR_OK) {
            $imageCheck = validateImage($_FILES['profil_image']);
            if ($imageCheck !== true) {
                $message = $imageCheck;
                $type = "error";
                header("Location: " . BASE_URL . "auth/register.php?status=" . $type . "&message=" . urlencode($message));
                exit;
            }

            $ext = pathinfo($_FILES['profil_image']['name'], PATHINFO_EXTENSION);
            $newFilename = uniqid('profile_', true) . '.' . $ext;
            $upload_dir = __DIR__ . '/../assets/uploads/profiles/';
            $target_path = $upload_dir . $newFilename;

            if (!move_uploaded_file($_FILES['profil_image']['tmp_name'], $target_path)) {
                $message = "Gagal mengupload gambar profil.";
                $type = "error";
                header("Location: " . BASE_URL . "auth/register.php?status=" . $type . "&message=" . urlencode($message));
                exit;
            }

            $profil_image = $newFilename;
        }

        // Hash password
        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        // Cek username atau email sudah dipakai belum
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = "Username atau email sudah terdaftar.";
            $type = "error";

            // Hapus file jika sudah diupload tapi gagal insert
            if (isset($newFilename) && file_exists($target_path)) {
                unlink($target_path);
            }
        } else {
            // Insert data user
            $stmt = $conn->prepare("INSERT INTO users (full_name, username, pass, email, phone, profil_image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $username, $hash_password, $email, $phone, $profil_image);
            if ($stmt->execute()) {
                $message = "Registrasi berhasil! Silahkan login";
                $type = "success";
            } else {
                $message = "Registrasi gagal. Silakan coba lagi.";
                $type = "error";

                // Hapus file jika sudah diupload tapi gagal insert
                if (isset($newFilename) && file_exists($target_path)) {
                    unlink($target_path);
                }
            }
            $stmt->close();
        }
        $stmt_check->close();
    }

    // Redirect berdasarkan hasil
    if ($type === "success") {
        // Jika berhasil, redirect ke register.php untuk menampilkan success alert
        header("Location: " . BASE_URL . "auth/register.php?status=" . $type . "&message=" . urlencode($message));
    } else {
        // Jika error, redirect ke register.php untuk menampilkan error alert
        header("Location: " . BASE_URL . "auth/register.php?status=" . $type . "&message=" . urlencode($message));
    }
    exit;
}
?>