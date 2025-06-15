<?php
session_start();
include_once __DIR__ . '/../config/baseURL.php';

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman login
header("Location: " . BASE_URL . "auth/login.php");
exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Jadoel Food</title>
    <!-- Load SweetAlert2 dari npm -->
    <script src="<?= BASE_URL ?>node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
</head>
<body>
    <script>
        // Tampilkan SweetAlert sukses logout
        Swal.fire({
            title: 'Berhasil Logout!',
            text: 'Anda telah berhasil logout dari sistem',
            icon: 'success',
            confirmButtonColor: '#0EA5E9',
            confirmButtonText: 'OK'
        }).then(() => {
            // Redirect ke halaman login
            window.location.href = '<?= BASE_URL ?>auth/login.php';
        });
    </script>
</body>
</html>