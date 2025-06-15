<?php
include_once __DIR__ . "/../config/connect.php";
include_once __DIR__ . "/../config/baseURL.php";

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

// Ambil email user dari session (asumsi email disimpan di session saat login)
if (!isset($_SESSION['email'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$user_email = $_SESSION['email'];

// Ambil data pesanan user berdasarkan email
// Aktifkan mode exception untuk mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$orders = [];

try {
    $query = "
        SELECT o.*, 
               COUNT(oi.id) as total_items
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.email = ? 
        GROUP BY o.id 
        ORDER BY o.create_at DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    $orders = [];
    $error_message = "Gagal memuat data pesanan: " . $e->getMessage();
}

// Function untuk format status
function getStatusBadge($status) {
    $status = strtolower($status);
    $badges = [
        'pending' => '<span class="badge bg-yellow-100 text-yellow-800">Pending</span>',
        'process' => '<span class="badge bg-blue-100 text-blue-800">Processing</span>',
        'delivery' => '<span class="badge bg-purple-100 text-purple-800">On Delivery</span>',
        'completed' => '<span class="badge bg-green-100 text-green-800">Completed</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-gray-100 text-gray-800">Unknown</span>';
}

// Header
include_once __DIR__ . "/../includes/header.php";
?>

<!-- Main Content -->
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                Riwayat Pesanan Saya
            </h1>
            <p class="text-gray-600 mt-2">Berikut adalah daftar pesanan yang telah Anda lakukan</p>
        </div>

        <!-- Search Section -->
        <div class="mb-6">
            <div class="relative">
                <input type="text" placeholder="Cari pesanan..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-black focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-50 text-blue-500 mr-3">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Pesanan</p>
                        <p class="text-xl font-bold text-sky-500"><?= count($orders) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-50 text-yellow-500 mr-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Pending</p>
                        <p class="text-xl font-bold text-sky-500">
                            <?= count(array_filter($orders, fn($o) => strtolower($o['status']) === 'pending')) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-50 text-purple-500 mr-3">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Delivery</p>
                        <p class="text-xl font-bold text-sky-500">
                            <?= count(array_filter($orders, fn($o) => strtolower($o['status']) === 'delivery')) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-50 text-green-500 mr-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Completed</p>
                        <p class="text-xl font-bold text-sky-500">
                            <?= count(array_filter($orders, fn($o) => strtolower($o['status']) === 'completed')) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error shadow-lg mb-6">
                <div>
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?= $error_message ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <!-- Empty State -->
            <div class="card bg-white shadow-lg">
                <div class="card-body text-center py-16">
                    <i class="fas fa-shopping-bag text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-600 mb-2">Belum Ada Pesanan</h3>
                    <p class="text-gray-500 mb-6">Anda belum pernah melakukan pemesanan</p>
                    <a href="<?= BASE_URL ?>#products" class="btn btn-primary">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Mulai Belanja
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="text-center">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alamat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status Pembayaran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($orders as $order): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">#<?= $order['id'] ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?= htmlspecialchars($order['address']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Kode Pos: <?= $order['postal_code'] ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?= $order['payment_method'] ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= getStatusBadge($order['status']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($order['create_at'])) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= date('H:i', strtotime($order['create_at'])) ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Auto refresh script -->
<script>
// Auto refresh every 30 seconds for order status updates
setInterval(() => {
    const hasPendingOrders = document.querySelector('[data-status="pending"], [data-status="processing"], [data-status="delivery"]');
    if (hasPendingOrders) {
        location.reload();
    }
}, 30000);
</script>

<?php
// Footer
include_once __DIR__ . "/../includes/footer.php";
?>