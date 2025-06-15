<?php 
include_once __DIR__ . '/../config/connect.php';
include_once __DIR__ . '/../config/baseURL.php';

// Pastikan user adalah admin
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$user_photo = $_SESSION['user']['photo'] ?? $_SESSION['profile_image'] ?? '';
$user_name = $_SESSION['user']['name'] ?? $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin';
$user_email = $_SESSION['user']['email'] ?? $_SESSION['email'] ?? '';

// Tentukan path foto profil
$profile_image_url = '';
$use_default_image = true;

if (!empty($user_photo)) {
    $user_profile_path = __DIR__ . '/../uploads/profiles/' . $user_photo;
    if (file_exists($user_profile_path)) {
        $profile_image_url = BASE_URL . 'uploads/profiles/' . $user_photo;
        $use_default_image = false;
    }
}

if ($use_default_image) {
    $default_profile_path = __DIR__ . '/../assets/images/profil.jpg';
    if (file_exists($default_profile_path)) {
        $profile_image_url = BASE_URL . 'assets/images/profil.jpg';
        $use_default_image = false;
    }
}

$order_count = 0; // Nilai default
$order_count_query = "SELECT COUNT(*) as total FROM orders"; // Ambil data pada tabel orders

$stmt = $conn->prepare($order_count_query);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $order_result = $result->fetch_assoc();
        $order_count = $order_result['total'] ?? 0;
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel - Jadoel Food</title>
    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/images/Logo.png" type="image/x-icon" />
    <!-- Tailwindcss -->
    <link href="<?= BASE_URL ?>src/css/output.css" rel="stylesheet" />
    <!-- FontAwesome Icons -->
    <script src="https://kit.fontawesome.com/7c1699d806.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="<?= BASE_URL ?>node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <style>
    [x-cloak] {
        display: none !important;
    }

    .sidebar-transition {
        transition: transform 0.3s ease-in-out;
    }

    @media (max-width: 1024px) {
        .sidebar-mobile {
            transform: translateX(-100%);
        }

        .sidebar-mobile.open {
            transform: translateX(0);
        }
    }

    .menu-item-active {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
    }

    .menu-item-active i {
        color: white;
    }

    .sidebar-header {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    }

    .sidebar-overlay {
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }
    
    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 40;
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    </style>
</head>

<body class="bg-gray-50 min-h-screen" x-data="{ sidebarOpen: false, currentPage: '' }"
    x-init="currentPage = window.location.pathname.split('/').pop()">

    <!-- Mobile Header (Sticky) -->
    <div class="lg:hidden sticky-header p-4 flex items-center justify-between text-white">
        <div class="flex items-center space-x-3">
            <i class="fas fa-utensils text-2xl"></i>
            <div>
                <h1 class="text-lg font-bold">Jadoel Food</h1>
                <p class="text-xs opacity-90">Admin Panel</p>
            </div>
        </div>
        <button @click="sidebarOpen = !sidebarOpen"
            class="text-white hover:text-gray-200 transition-colors">
            <i class="fas fa-bars text-2xl"></i>
        </button>
    </div>

    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" @click="sidebarOpen = false"
        class="lg:hidden fixed inset-0 z-30 bg-black bg-opacity-50" x-cloak></div>

    <!-- Main Layout -->
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="fixed lg:sticky lg:top-0 inset-y-0 left-0 z-40 w-64 bg-white shadow-xl transform transition-transform duration-300"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            style="max-height: 100vh; overflow-y: auto;">

            <!-- Sidebar Header -->
            <div class="sidebar-header p-6 text-white">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-utensils text-2xl"></i>
                    <div>
                        <h1 class="text-lg font-bold">Jadoel Food</h1>
                        <p class="text-sm opacity-90">Admin Panel</p>
                    </div>
                </div>
            </div>

            <!-- User Profile Section -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full overflow-hidden ring-2 ring-sky-100">
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
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            <?= htmlspecialchars($user_name) ?>
                        </p>
                        <p class="text-xs text-gray-500 truncate">Administrator</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 p-4 overflow-y-auto">
                <ul class="space-y-2">
                    <!-- Dashboard -->
                    <li>
                        <a href="<?= BASE_URL ?>admin/dashboard.php"
                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors group"
                            :class="currentPage === 'dashboard.php' ? 'menu-item-active' : ''"
                            @click="sidebarOpen = false">
                            <i class="fas fa-tachometer-alt text-lg text-gray-600 group-hover:text-sky-500"></i>
                            <span class="font-medium">Dashboard</span>
                        </a>
                    </li>

                    <!-- Add Product -->
                    <li>
                        <a href="<?= BASE_URL ?>admin/add-product.php"
                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors group"
                            :class="currentPage === 'add-product.php' ? 'menu-item-active' : ''"
                            @click="sidebarOpen = false">
                            <i class="fas fa-plus-circle text-lg text-gray-600 group-hover:text-sky-500"></i>
                            <span class="font-medium">Add Product</span>
                        </a>
                    </li>

                    <!-- Manage Products -->
                    <li>
                        <a href="<?= BASE_URL ?>admin/products.php"
                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors group"
                            :class="currentPage === 'products.php' ? 'menu-item-active' : ''"
                            @click="sidebarOpen = false">
                            <i class="fas fa-box text-lg text-gray-600 group-hover:text-sky-500"></i>
                            <span class="font-medium">Manage Products</span>
                        </a>
                    </li>

                    <!-- Orders -->
                    <li>
                        <a href="<?= BASE_URL ?>admin/orders.php"
                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors group relative"
                            :class="currentPage === 'orders.php' ? 'menu-item-active' : ''"
                            @click="sidebarOpen = false">
                            <i class="fas fa-shopping-bag text-lg text-gray-600 group-hover:text-sky-500"></i>
                            <span class="font-medium">Orders</span>
                            
                            <?php if ($order_count > 0): ?>
                                <!-- Badge menggunakan DaisyUI -->
                                <div class="badge badge-error badge-sm absolute right-3 top-1/2 transform -translate-y-1/2 text-white font-semibold">
                                    <?= $order_count ?>
                                </div>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Manage Users -->
                    <li>
                        <a href="<?= BASE_URL ?>admin/users.php"
                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors group"
                            :class="currentPage === 'users.php' ? 'menu-item-active' : ''" @click="sidebarOpen = false">
                            <i class="fas fa-users text-lg text-gray-600 group-hover:text-sky-500"></i>
                            <span class="font-medium">Manage Users</span>
                        </a>
                    </li>

                    <!-- Divider -->
                    <li class="my-4">
                        <div class="border-t border-gray-200"></div>
                    </li>

                    <!-- Profile -->
                    <li>
                        <a href="<?= BASE_URL ?>admin/profile.php"
                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors group"
                            :class="currentPage === 'profile.php' ? 'menu-item-active' : ''"
                            @click="sidebarOpen = false">
                            <i class="fas fa-user text-lg text-gray-600 group-hover:text-sky-500"></i>
                            <span class="font-medium">Profile</span>
                        </a>
                    </li>

                    <!-- Settings -->
                    <li>
                        <a href="<?= BASE_URL ?>admin/settings.php"
                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 transition-colors group"
                            :class="currentPage === 'settings.php' ? 'menu-item-active' : ''"
                            @click="sidebarOpen = false">
                            <i class="fas fa-cog text-lg text-gray-600 group-hover:text-sky-500"></i>
                            <span class="font-medium">Settings</span>
                        </a>
                    </li>

                    <!-- Divider -->
                    <li class="my-4">
                        <div class="border-t border-gray-200"></div>
                    </li>

                    <!-- Logout -->
                    <li>
                        <a href="#" onclick="confirmLogout()"
                            class="flex items-center space-x-3 p-3 rounded-lg hover:bg-red-50 text-red-600 transition-colors group">
                            <i class="fas fa-sign-out-alt text-lg text-red-600"></i>
                            <span class="font-medium">Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Sidebar Footer -->
            <div class="pt-45 border-t border-gray-200">
                <div class="text-center">
                    <p class="text-xs text-gray-500">© 2024 Jadoel Food</p>
                    <p class="text-xs text-gray-400">Admin Dashboard</p>
                </div>
            </div>
        </aside>

        <!-- Page Content -->
        <div class="flex-1 min-w-0">
            <div class="p-4 lg:p-6">
                <!-- Content from including files will be inserted here -->

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
                            Swal.fire({
                                title: 'Logging out...',
                                text: 'Mohon tunggu sebentar',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            setTimeout(() => {
                                window.location.href = '<?= BASE_URL ?>auth/logout.php';
                            }, 1000);
                        }
                    });
                }

                // Auto-close mobile sidebar when clicking menu items
                document.addEventListener('DOMContentLoaded', function() {
                    const menuLinks = document.querySelectorAll('.drawer-side a');
                    const drawerToggle = document.getElementById('drawer-toggle');

                    menuLinks.forEach(link => {
                        link.addEventListener('click', function() {
                            if (window.innerWidth < 1024) { // lg breakpoint
                                drawerToggle.checked = false;
                            }
                        });
                    });
                });
                </script>