<?php 
include_once __DIR__ . '/../config/baseURL.php';
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - Jadoel Food</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>src/css/output.css">
    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/images/Logo.png" type="image/x-icon" />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- Load SweetAlert2 dari npm -->
    <script src="<?= BASE_URL ?>node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-lg">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-sky-500">Create Your Account</h1>
            <p class="text-gray-600 text-sm">Silakan isi data untuk membuat akun</p>
        </div>
        <form action="<?= BASE_URL ?>functions/userRegister.php" method="POST" enctype="multipart/form-data"
            class="space-y-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text text-gray-700">Full name</span>
                </label>
                <input type="text" name="full_name" required class="input input-bordered w-full bg-white text-gray-900"
                    placeholder="Jhon Doe" />
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text text-gray-700">Username</span>
                </label>
                <input type="text" name="username" required class="input input-bordered w-full bg-white text-gray-900"
                    placeholder="your username" />
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text text-gray-700">Password</span>
                </label>
                <input type="password" name="pass" required class="input input-bordered w-full bg-white text-gray-900"
                    placeholder="••••••••" />
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text text-gray-700">Repeat Password</span>
                </label>
                <input type="password" name="repeat_pass" required
                    class="input input-bordered w-full bg-white text-gray-900" placeholder="••••••••" />
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text text-gray-700">Email</span>
                </label>
                <input type="email" name="email" required class="input input-bordered w-full bg-white text-gray-900"
                    placeholder="youremail@gmail.com" />
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text text-gray-700">Phone Number</span>
                </label>
                <input type="text" name="phone" required class="input input-bordered w-full bg-white text-gray-900"
                    inputmode="numeric" placeholder="your phone number" />
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text text-gray-700">Profil Image</span>
                </label>
                <input type="file" name="profil_image" accept="image/*"
                    class="file-input file-input-bordered w-full bg-white text-gray-900" />
            </div>
            <div class="form-control mt-4">
                <button type="submit" name="regist"
                    class="btn w-full bg-sky-500 hover:bg-sky-700 text-white border-none">Sign Up</button>
            </div>
        </form>
        <p class="text-center text-sm text-gray-600">
            Already Have Account?
            <a href="<?= BASE_URL ?>auth/login.php" class="text-sky-500 hover:underline">Login Here</a>
        </p>
    </div>

    <!-- Script SweetAlert -->
    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
    <?php
        $status = htmlspecialchars($_GET['status'], ENT_QUOTES);
        $message = htmlspecialchars($_GET['message'], ENT_QUOTES);
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: '<?= $status ?>',
            title: '<?= $status === "success" ? "Sukses" : "Gagal" ?>',
            text: '<?= $message ?>',
            showConfirmButton: true,
            timer: 3000,
            timerProgressBar: true
        }).then((result) => {
            // Jika registrasi berhasil, redirect ke login setelah alert ditutup
            <?php if ($status === 'success'): ?>
            if (result.dismiss === Swal.DismissReason.timer || result.isConfirmed) {
                window.location.href = '<?= BASE_URL ?>auth/login.php';
            }
            <?php endif; ?>
        });
    });
    </script>
    <?php endif; ?>

</body>

</html>