<?php 
include_once __DIR__ . '/../config/baseURL.php';
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Jadoel Food</title>
    <link href="<?= BASE_URL ?>src/css/output.css" rel="stylesheet" />
    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/images/Logo.png" type="image/x-icon" />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Load SweetAlert2 dari npm -->
    <script src="<?= BASE_URL ?>node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-lg">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-sky-500">Sign In to your account</h1>
            <p class="text-gray-600 text-sm">Silakan login untuk melanjutkan</p>
        </div>
        <form action="<?= BASE_URL ?>functions/userLogin.php" method="POST" class="space-y-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text text-gray-700">Username atau Email</span>
                </label>
                <input type="text" name="login_id" required placeholder="Jhon Doe atau email@example.com"
                    class="input input-bordered w-full bg-white text-gray-900" />
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text text-gray-700">Password</span>
                </label>
                <input type="password" name="password" required placeholder="••••••••"
                    class="input input-bordered w-full bg-white text-gray-900" />
            </div>
            <div class="form-control mt-4">
                <button type="submit" name="login"
                    class="btn w-full bg-sky-500 hover:bg-sky-700 text-white border-none">Sign In</button>
            </div>
        </form>
        <p class="text-center text-sm text-gray-600">
            Don't Have an Account?
            <a href="<?= BASE_URL ?>auth/register.php" class="text-sky-500 hover:underline">Sign Up Now</a>
        </p>
    </div>

    <!-- Script SweetAlert -->
    <?php if (isset($_GET['status'])): ?>
    <script>
    <?php if ($_GET['status'] === 'success' && isset($_GET['redirect'])): ?>
    // SweetAlert untuk login berhasil
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Berhasil!',
            text: '<?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Login berhasil!'; ?>',
            icon: 'success',
            confirmButtonText: 'OK',
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'rounded-lg'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?php echo htmlspecialchars(urldecode($_GET['redirect'])); ?>';
            }
        });
    });
    <?php elseif ($_GET['status'] === 'error'): ?>
    // SweetAlert untuk login gagal
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Error!',
            text: '<?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Login gagal!'; ?>',
            icon: 'error',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'rounded-lg'
            }
        });
    });
    <?php endif; ?>
    </script>
    <?php endif; ?>

    <?php if (isset($_COOKIE['account_deleted'])): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Akun Dihapus!',
            text: 'Akun Anda telah dihapus secara permanen',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#0ea5e9'
        });

        // Hapus cookie
        document.cookie = 'account_deleted=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    });
    </script>
    <?php endif; ?>

</body>

</html>