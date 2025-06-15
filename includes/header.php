<?php 
include_once __DIR__ . '/../config/connect.php';
include_once __DIR__ . '/../config/baseURL.php';

?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth ligth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Jadoel Food</title>
    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/images/Logo.png" type="image/x-icon" />
    <link href="<?= BASE_URL ?>src/css/output.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/7c1699d806.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert2 CDN -->
    <script src="<?= BASE_URL ?>src/js/sweetalert2.all.min.js"></script>
    <style>
    [x-cloak] {
        display: none !important;
    }

    .scroll-section {
        height: calc(100vh - 4rem);
        scroll-snap-align: start;
    }

    @media (max-width: 768px) {
        .scroll-section {
            height: calc(100vh - 6rem);
        }
    }

    .hero-text {
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    /* Custom styles untuk dropdown agar tidak trigger hover */
    .dropdown:hover .dropdown-content {
        display: none !important;
    }

    .dropdown.dropdown-open .dropdown-content,
    .dropdown:focus-within .dropdown-content,
    .dropdown-content:focus-within {
        display: block !important;
    }

    /* Perbaikan untuk dropdown yang dibuka dengan click */
    .dropdown[tabindex]:focus .dropdown-content,
    .dropdown>*:focus .dropdown-content {
        display: block !important;
    }
    </style>
</head>

<body class="flex flex-col min-h-screen overflow-y-scroll scroll-snap-y mandatory"
    x-data="{ isMenuOpen: false, isCartOpen: false }">
    <div id="app" data-baseurl="<?php echo BASE_URL; ?>"></div>

    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0">
                    <i class="fa-solid fa-utensils text-sky-500 text-xl lg:text-2xl"></i>
                    <a href="#home" class="text-sky-500 font-bold text-lg lg:text-xl ml-2">Jadoel Food</a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="<?= BASE_URL ?>#home"
                        class="text-sky-500 hover:text-sky-700 px-3 py-2 text-sm lg:text-base"><i
                            class="fas fa-home mr-1"></i>Home</a>
                    <a href="<?= BASE_URL ?>#about"
                        class="text-sky-500 hover:text-sky-700 px-3 py-2 text-sm lg:text-base"><i
                            class="fas fa-info-circle mr-1"></i>About</a>
                    <a href="<?= BASE_URL ?>#products"
                        class="text-sky-500 hover:text-sky-700 px-3 py-2 text-sm lg:text-base"><i
                            class="fas fa-box mr-1"></i>Products</a>
                    <a href="<?= BASE_URL ?>#contact"
                        class="text-sky-500 hover:text-sky-700 px-3 py-2 text-sm lg:text-base"><i
                            class="fas fa-envelope mr-1"></i>Contact</a>
                </div>

                <!-- Right Section -->
                <div class="flex items-center space-x-4">
                    <!-- Cart -->
                    <div class="relative">
                        <button @click="isCartOpen = !isCartOpen" class="text-sky-500 p-2">
                            <i class="fas fa-shopping-cart text-lg lg:text-xl"></i>
                            <span id="cart-count"
                                class="absolute -top-2 -right-2 bg-sky-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                <?= (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0 ?>
                            </span>
                        </button>
                        <!-- Cart Dropdown -->
                        <div x-show="isCartOpen" @click.away="isCartOpen = false" x-cloak
                            class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-200">
                            <div class="px-4 py-2">
                                <div id="cart-items" class="max-h-64 overflow-y-auto"></div>
                                <div class="mt-4 border-t pt-2">
                                    <p class="text-right font-semibold text-gray-700">Total: Rp.<span
                                            id="cart-total">0</span></p>
                                    <a href="<?= BASE_URL ?>public/checkout.php"
                                        class="w-full bg-sky-500 text-white px-4 py-2 rounded-lg hover:bg-sky-600 mt-2 block text-center">
                                        Checkout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Auth Section -->
                    <?php if (!isset($_SESSION['login']) || $_SESSION['login'] !== true): ?>
                    <!-- Login Button -->
                    <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-sign-in-alt mr-1"></i>Login
                    </a>
                    <?php else: ?>
                    <!-- User Profile Dropdown -->
                    <?php 
          $user_photo = $_SESSION['user']['photo'] ?? $_SESSION['profile_image'] ?? '';
          $user_name = $_SESSION['user']['name'] ?? $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
          $user_email = $_SESSION['user']['email'] ?? $_SESSION['email'] ?? '';
          
          // Tentukan path foto profil
          $profile_image_url = '';
          $use_default_image = true;
          
          if (!empty($user_photo)) {
              // Cek foto profil user yang sudah diupload
              $user_profile_path = __DIR__ . '/../uploads/profiles/' . $user_photo;
              if (file_exists($user_profile_path)) {
                  $profile_image_url = BASE_URL . 'uploads/profiles/' . $user_photo;
                  $use_default_image = false;
              }
          }
          
          // Jika tidak ada foto user atau file tidak ditemukan, gunakan default
          if ($use_default_image) {
              $default_profile_path = __DIR__ . '/../assets/images/profil.jpg';
              if (file_exists($default_profile_path)) {
                  $profile_image_url = BASE_URL . 'assets/images/profil.jpg';
                  $use_default_image = false; // Karena kita akan pakai gambar default
              }
          }
          ?>

                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                            <div class="w-10 h-10 rounded-full overflow-hidden">
                                <?php if (!$use_default_image && !empty($profile_image_url)): ?>
                                <img src="<?= $profile_image_url ?>" alt="<?= htmlspecialchars($user_name) ?>"
                                    class="w-full h-full object-cover" />
                                <?php else: ?>
                                <div
                                    class="w-full h-full bg-sky-500 text-white flex items-center justify-center text-sm font-semibold">
                                    <?= strtoupper(substr($user_name, 0, 1)) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <ul tabindex="0"
                            class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52 mt-3">
                            <!-- User Info Header -->
                            <li class="menu-title">
                                <div class="flex items-center space-x-3 px-2 py-2">
                                    <div class="w-10 h-10 rounded-full overflow-hidden">
                                        <?php if (!$use_default_image && !empty($profile_image_url)): ?>
                                        <img src="<?= $profile_image_url ?>" alt="<?= htmlspecialchars($user_name) ?>"
                                            class="w-full h-full object-cover" />
                                        <?php else: ?>
                                        <div
                                            class="w-full h-full bg-sky-500 text-white flex items-center justify-center text-sm font-semibold">
                                            <?= strtoupper(substr($user_name, 0, 1)) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-base-content truncate">
                                            <?= htmlspecialchars($user_name) ?>
                                        </p>
                                        <?php if (!empty($user_email)): ?>
                                        <p class="text-xs opacity-60 truncate">
                                            <?= htmlspecialchars($user_email) ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <hr class="my-1">
                            </li>

                            <!-- Menu Items -->
                            <li>
                                <a href="<?= BASE_URL ?>public/profile.php">
                                    <i class="fas fa-user"></i>
                                    Profile
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>public/settings.php">
                                    <i class="fas fa-cog"></i>
                                    Settings
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>public/orders.php">
                                    <i class="fas fa-clipboard-list"></i>
                                    My Order
                                </a>
                            </li>
                            <li>
                                <hr class="my-1">
                            </li>

                            <!-- Logout -->
                            <li>
                                <a href="#" onclick="confirmLogout()" class="text-error">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Mobile Menu Button -->
                    <button @click="isMenuOpen = !isMenuOpen" class="md:hidden text-sky-500 p-2">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="fixed inset-0 z-50" x-show="isMenuOpen" x-cloak>
        <div class="absolute inset-0 bg-black/50" @click="isMenuOpen = false"></div>
        <div class="relative bg-white w-64 h-full ml-auto transform transition-transform duration-300"
            :class="isMenuOpen ? 'translate-x-0' : 'translate-x-full'">
            <div class="p-4">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-xl font-bold text-sky-500">Menu</h2>
                    <button @click="isMenuOpen = false" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- User Profile in Mobile Menu (if logged in) -->
                <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                <div class="mb-6 pb-4 border-b">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-200">
                            <?php if (!$use_default_image && !empty($profile_image_url)): ?>
                            <img src="<?= $profile_image_url ?>" alt="<?= htmlspecialchars($user_name) ?>"
                                class="w-full h-full object-cover" />
                            <?php else: ?>
                            <div
                                class="w-full h-full bg-sky-500 text-white flex items-center justify-center text-lg font-semibold">
                                <?= strtoupper(substr($user_name, 0, 1)) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">
                                <?= htmlspecialchars($user_name) ?>
                            </p>
                            <?php if (!empty($user_email)): ?>
                            <p class="text-sm text-gray-500 truncate">
                                <?= htmlspecialchars($user_email) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <nav class="space-y-2">
                    <a href="<?= BASE_URL ?>#home" @click="isMenuOpen = false"
                        class="btn btn-ghost justify-start w-full text-sky-500">
                        <i class="fas fa-home"></i>Home
                    </a>
                    <a href="<?= BASE_URL ?>#about" @click="isMenuOpen = false"
                        class="btn btn-ghost justify-start w-full text-sky-500">
                        <i class="fas fa-info-circle"></i>About
                    </a>
                    <a href="<?= BASE_URL ?>#products" @click="isMenuOpen = false"
                        class="btn btn-ghost justify-start w-full text-sky-500">
                        <i class="fas fa-box"></i>Products
                    </a>
                    <a href="<?= BASE_URL ?>#contact" @click="isMenuOpen = false"
                        class="btn btn-ghost justify-start w-full text-sky-500">
                        <i class="fas fa-envelope"></i>Contact
                    </a>
                    <a href="<?= BASE_URL ?>public/orders.php" @click="isMenuOpen = false"
                        class="btn btn-ghost justify-start w-full text-sky-500">
                        <i class="fas fa-info-circle"></i>My Orders
                    </a>

                    <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                    <!-- User Menu Items in Mobile -->
                    <div class="divider"></div>
                    <a href="<?= BASE_URL ?>public/profile.php" @click="isMenuOpen = false"
                        class="btn btn-ghost justify-start w-full text-sky-500">
                        <i class="fas fa-user"></i>Profile
                    </a>
                    <a href="<?= BASE_URL ?>public/settings.php" @click="isMenuOpen = false"
                        class="btn btn-ghost justify-start w-full text-sky-500">
                        <i class="fas fa-cog"></i>Settings
                    </a>
                    <a href="#" onclick="confirmLogout()" class="btn btn-ghost justify-start w-full text-error">
                        <i class="fas fa-sign-out-alt"></i>Logout
                    </a>
                    <?php else: ?>
                    <!-- Login Button in Mobile Menu -->
                    <div class="divider"></div>
                    <a href="<?= BASE_URL ?>auth/login.php" @click="isMenuOpen = false" class="btn btn-primary w-full">
                        <i class="fas fa-sign-in-alt"></i>Login
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>

    <!-- JavaScript untuk Logout Confirmation -->
    <script>
    function confirmLogout() {
        Swal.fire({
            title: 'Konfirmasi Logout',
            text: 'Apakah Anda yakin ingin logout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0EA5E9',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading
                Swal.fire({
                    title: 'Logging out...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Redirect ke logout.php
                setTimeout(() => {
                    window.location.href = '<?= BASE_URL ?>auth/logout.php';
                }, 1000);
            }
        });
    }
    </script>

    <!-- cart.js -->
     <script src="<?= BASE_URL ?>src/js/cart.js"></script>