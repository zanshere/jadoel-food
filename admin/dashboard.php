<?php 
include_once __DIR__ . "/../config/connect.php";
include_once __DIR__ . "/../config/baseURL.php";

// Cek login admin
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['role'] !== 'admin') {   
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

// Query statistik
$products_count = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$orders_count = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$pending_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'")->fetch_assoc()['total'];
$process_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'Process'")->fetch_assoc()['total'];
$delivery_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'Delivery'")->fetch_assoc()['total'];
$completed_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'Completed'")->fetch_assoc()['total'];

// Query untuk aktivitas terkini
$new_orders = $conn->query("
    SELECT id, username, status, create_at 
    FROM orders 
    ORDER BY create_at DESC 
    LIMIT 2  -- Ambil 2 pesanan terbaru
");

$latest_completed = $conn->query("
    SELECT id, username, create_at 
    FROM orders 
    WHERE status = 'Completed' 
    ORDER BY create_at DESC 
    LIMIT 1  -- Ambil 1 pesanan completed terbaru
");

$new_users = $conn->query("
    SELECT username, create_at 
    FROM users 
    WHERE role = 'Customer' 
    ORDER BY create_at DESC 
    LIMIT 1  -- Ambil 1 user terbaru
");

$new_products = $conn->query("
    SELECT product_name, create_at 
    FROM products 
    ORDER BY create_at DESC 
    LIMIT 1  -- Ambil 1 produk terbaru
");

// Gabungkan semua aktivitas
$activities = [];

// Tambahkan pesanan baru
while($order = $new_orders->fetch_assoc()) {
    $activities[] = [
        'type' => 'order',
        'data' => $order,
        'time' => $order['create_at']
    ];
}

// Tambahkan pesanan completed terbaru
if ($completed = $latest_completed->fetch_assoc()) {
    // Pastikan tidak duplikat dengan pesanan baru
    $is_duplicate = false;
    foreach ($activities as $activity) {
        if ($activity['type'] == 'order' && $activity['data']['id'] == $completed['id']) {
            $is_duplicate = true;
            break;
        }
    }
    if (!$is_duplicate) {
        $activities[] = [
            'type' => 'completed',
            'data' => $completed,
            'time' => $completed['create_at']
        ];
    }
}

// Tambahkan user baru
if ($user = $new_users->fetch_assoc()) {
    $activities[] = [
        'type' => 'user',
        'data' => $user,
        'time' => $user['create_at']
    ];
}

// Tambahkan product baru
if ($product = $new_products->fetch_assoc()) {
    $activities[] = [
        'type' => 'product',
        'data' => $product,
        'time' => $product['create_at']
    ];
}

// Urutkan berdasarkan waktu terbaru
usort($activities, function($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});

// Ambil 4 aktivitas terbaru (2 pesanan + 1 completed + 1 user/produk)
$recent_activities = array_slice($activities, 0, 4);

// Urutkan berdasarkan waktu terbaru
usort($activities, function($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});

// Ambil 5 aktivitas terbaru
$recent_activities = array_slice($activities, 0, 5);

include_once __DIR__ . '/../includes/adminHeader.php';
include __DIR__ . '/../functions/timeElapsed.php';
?>

<body class="bg-gray-50">
    <!-- Main Content - Strictly No Scroll -->
    <main class="max-w-7xl mx-auto px-4 py-4 h-[calc(100vh-8rem)] overflow-hidden">
        <!-- Dashboard Header -->
        <div class="mb-4 text-center">
            <h1 class="text-2xl font-bold text-sky-500 flex items-center justify-center gap-2">
                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
            </h1>
            <p class="text-gray-600 mt-1">Selamat datang di panel admin, <?= htmlspecialchars($_SESSION['username']) ?>
            </p>
        </div>

        <!-- Content Grid -->
        <div class="flex justify-center">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 h-[calc(100%-4rem)] max-w-5xl">
                <!-- About Section -->
                <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                    <div class="flex-1 flex flex-col items-center justify-center">
                        <img src="<?= BASE_URL ?>assets/images/Logo.png" alt="Jadoel Food"
                            class="h-96 object-contain mb-4">
                        <h2 class="text-xl font-bold text-sky-500 flex items-center gap-2">
                            <i class="fas fa-store"></i> Tentang Jadoel Food
                        </h2>
                        <p class="text-gray-600 text-center text-sm mt-2">
                            <i class="fas fa-history mr-1"></i> Melestarikan cita rasa autentik sejak 1985
                        </p>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        <div class="bg-blue-50 p-2 rounded text-center">
                            <div class="text-lg font-bold text-blue-600 flex items-center justify-center gap-1">
                                <i class="fas fa-box"></i> <?= $products_count ?>
                            </div>
                            <div class="text-xs text-gray-600">Total Produk</div>
                        </div>
                        <div class="bg-green-50 p-2 rounded text-center">
                            <div class="text-lg font-bold text-green-600 flex items-center justify-center gap-1">
                                <i class="fas fa-shopping-cart"></i> <?= $orders_count ?>
                            </div>
                            <div class="text-xs text-gray-600">Total Pesanan</div>
                        </div>
                        <div class="bg-yellow-100 p-2 rounded text-center">
                            <div class="text-lg font-bold text-yellow-600 flex items-center justify-center gap-1">
                                <i class="fas fa-clock"></i> <?= $pending_orders ?>
                            </div>
                            <div class="text-xs text-gray-600">Pending</div>
                        </div>
                        <div class="bg-purple-100 p-2 rounded text-center">
                            <div class="text-lg font-bold text-purple-600 flex items-center justify-center gap-1">
                                <i class="fas fa-clock"></i> <?= $process_orders ?>
                            </div>
                            <div class="text-xs text-gray-600">Process</div>
                        </div>
                        <div class="bg-blue-100 p-2 rounded text-center">
                            <div class="text-lg font-bold text-blue-600 flex items-center justify-center gap-1">
                                <i class="fas fa-clock"></i> <?= $delivery_orders ?>
                            </div>
                            <div class="text-xs text-gray-600">Delivery</div>
                        </div>
                        <div class="bg-green-100 p-2 rounded text-center">
                            <div class="text-lg font-bold text-green-600 flex items-center justify-center gap-1">
                                <i class="fas fa-check-circle"></i> <?= $completed_orders ?>
                            </div>
                            <div class="text-xs text-gray-600">Selesai</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h2 class="text-lg font-bold text-sky-500 mb-3 flex items-center gap-2">
                        <i class="fas fa-bolt"></i> Menu Cepat
                    </h2>
                    <div class="space-y-2">
                        <a href="<?= BASE_URL ?>admin/products.php"
                            class="flex items-center p-3 rounded-lg hover:bg-sky-50 transition-colors border border-gray-200">
                            <div class="bg-sky-100 p-2 rounded-full mr-3">
                                <i class="fas fa-box text-sky-500"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">Kelola Produk</h3>
                                <p class="text-xs text-gray-500">Tambah/edit produk</p>
                            </div>
                            <div class="ml-auto">
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                        </a>

                        <a href="<?= BASE_URL ?>admin/orders.php"
                            class="flex items-center p-3 rounded-lg hover:bg-purple-50 transition-colors border border-gray-200">
                            <div class="bg-purple-100 p-2 rounded-full mr-3">
                                <i class="fas fa-shopping-cart text-purple-500"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">Kelola Pesanan</h3>
                                <p class="text-xs text-gray-500">Lihat/update pesanan</p>
                            </div>
                            <div class="ml-auto">
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                        </a>

                        <a href="<?= BASE_URL ?>admin/profile.php"
                            class="flex items-center p-3 rounded-lg hover:bg-orange-50 transition-colors border border-gray-200">
                            <div class="bg-orange-100 p-2 rounded-full mr-3">
                                <i class="fas fa-user-cog text-orange-500"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">Pengaturan Profil</h3>
                                <p class="text-xs text-gray-500">Kelola akun admin</p>
                            </div>
                            <div class="ml-auto">
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                        </a>

                        <!-- Recent Activity -->
                        <h2 class="text-lg font-bold text-sky-500 mb-3 flex items-center gap-2 mt-4">
                            <i class="fas fa-clock-rotate-left"></i> Aktivitas Terkini
                        </h2>
                        <div class="space-y-3">
                            <?php foreach($recent_activities as $activity): 
                                $icon = '';
                                $bg_color = '';
                                $text_color = '';
                                $message = '';
                                
                                switch($activity['type']) {
                                    case 'order':
                                        $icon = 'fa-shopping-cart';
                                        $bg_color = 'bg-blue-100';
                                        $text_color = 'text-blue-500';
                                        $message = "Pesanan baru #{$activity['data']['id']} ({$activity['data']['status']})";
                                        break;
                                        
                                    case 'completed':
                                        $icon = 'fa-check-circle';
                                        $bg_color = 'bg-green-100';
                                        $text_color = 'text-green-500';
                                        $message = "Pesanan selesai #{$activity['data']['id']}";
                                        break;
                                        
                                    case 'user':
                                        $icon = 'fa-user-plus';
                                        $bg_color = 'bg-indigo-100';
                                        $text_color = 'text-indigo-500';
                                        $message = "User baru: {$activity['data']['username']}";
                                        break;
                                        
                                    case 'product':
                                        $icon = 'fa-box-open';
                                        $bg_color = 'bg-orange-100';
                                        $text_color = 'text-orange-500';
                                        $message = "Produk baru: {$activity['data']['product_name']}";
                                        break;
                                }
                                
                                $time_ago = time_elapsed_string($activity['time']);
                            ?>
                            <div class="flex items-start">
                                <div class="<?= $bg_color ?> p-2 rounded-full mr-3">
                                    <i class="fas <?= $icon ?> <?= $text_color ?> text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">
                                        <?= $message ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <?= $time_ago ?>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
</body>
</html>