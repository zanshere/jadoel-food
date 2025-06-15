<?php
include_once __DIR__ . "/../config/connect.php";
include_once __DIR__ . "/../config/baseURL.php";

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['role'] !== 'admin') {   
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$message = '';
$error = '';
$message_type = '';

// Check for status update message from session
if (isset($_SESSION['status_update_message'])) {
    $message = $_SESSION['status_update_message'];
    $message_type = $_SESSION['status_update_message_type'];
    unset($_SESSION['status_update_message']);
    unset($_SESSION['status_update_message_type']);
}

// Handle status update (this can be removed since we're using updateOrderStatus.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ?, update_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $message = "Status pesanan berhasil diperbarui";
        $message_type = 'success';
    } else {
        $error = "Gagal memperbarui status pesanan: " . $conn->error;
        $message_type = 'error';
    }
}

// Pagination settings
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(username LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= 'sss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM orders $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_items = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Get orders
$query = "SELECT * FROM orders $where_clause ORDER BY create_at DESC LIMIT ? OFFSET ?";
$params[] = $items_per_page;
$params[] = $offset;
$param_types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Header
include __DIR__ . "/../includes/adminHeader.php";
?>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
        <div class="bg-sky-500 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Kelola Pesanan</h1>
                    <p class="text-sky-100 mt-1">Kelola semua pesanan pelanggan</p>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="p-6 border-b border-gray-200">
            <form method="GET" class="flex">
                <div class="flex-1">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" placeholder="Cari pesanan..."
                            value="<?= htmlspecialchars($search) ?>"
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                    </div>
                </div>
                <button type="submit"
                    class="bg-sky-500 text-white px-6 py-2 rounded-lg hover:bg-sky-600 transition-colors ml-2">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </form>
        </div>

        <!-- Stats Section -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-shopping-cart text-2xl text-blue-500 mr-3"></i>
                        <div>
                            <p class="text-sm text-gray-600">Total Pesanan</p>
                            <p class="text-xl font-bold text-gray-900">
                                <?php
                                $total_query = "SELECT COUNT(*) as total FROM orders";
                                $total_result = $conn->query($total_query);
                                echo $total_result->fetch_assoc()['total'];
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-clock text-2xl text-yellow-500 mr-3"></i>
                        <div>
                            <p class="text-sm text-gray-600">Pending</p>
                            <p class="text-xl font-bold text-gray-900">
                                <?php
                                $pending_query = "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'";
                                $pending_result = $conn->query($pending_query);
                                echo $pending_result->fetch_assoc()['total'];
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-truck text-2xl text-purple-500 mr-3"></i>
                        <div>
                            <p class="text-sm text-gray-600">Proses</p>
                            <p class="text-xl font-bold text-gray-900">
                                <?php
                                $process_query = "SELECT COUNT(*) as total FROM orders WHERE status = 'Process'";
                                $process_result = $conn->query($process_query);
                                echo $process_result->fetch_assoc()['total'];
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-2xl text-green-500 mr-3"></i>
                        <div>
                            <p class="text-sm text-gray-600">Selesai</p>
                            <p class="text-xl font-bold text-gray-900">
                                <?php
                                $completed_query = "SELECT COUNT(*) as total FROM orders WHERE status = 'Completed'";
                                $completed_result = $conn->query($completed_query);
                                echo $completed_result->fetch_assoc()['total'];
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pelanggan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Alamat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pembayaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">#<?= $order['id'] ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($order['username']) ?>
                            </div>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($order['email']) ?></div>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($order['phone']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <?= htmlspecialchars($order['address']) ?>,
                                <?= htmlspecialchars($order['city']) ?>,
                                <?= $order['postal_code'] ?>
                            </div>
                            <?php if (!empty($order['note'])): ?>
                            <div class="text-sm text-gray-500 mt-1">
                                <strong>Catatan:</strong> <?= htmlspecialchars($order['note']) ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?= strtoupper($order['payment_method']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 rounded-full text-sm font-medium 
                                <?= $order['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                <?= $order['status'] === 'Process' ? 'bg-purple-100 text-purple-800' : '' ?>
                                <?= $order['status'] === 'Delivery' ? 'bg-blue-100 text-blue-800' : '' ?>
                                <?= $order['status'] === 'Completed' ? 'bg-green-100 text-green-800' : '' ?>">
                                <?= $order['status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?= date('d M Y', strtotime($order['create_at'])) ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?= date('H:i', strtotime($order['create_at'])) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="<?= BASE_URL ?>admin/order-detail.php?id=<?= $order['id'] ?>"
                                class="text-sky-600 hover:text-sky-900 mr-3" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Menampilkan <span class="font-medium"><?= $offset + 1 ?></span> sampai
                <span class="font-medium"><?= min($offset + $items_per_page, $total_items) ?></span> dari
                <span class="font-medium"><?= $total_items ?></span> pesanan
            </div>
            <div class="flex space-x-2">
                <a href="?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>"
                    class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 <?= $page <= 1 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    Previous
                </a>
                <a href="?page=<?= min($total_pages, $page + 1) ?>&search=<?= urlencode($search) ?>"
                    class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 <?= $page >= $total_pages ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    Next
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show success/error messages
    <?php if (!empty($message) && !empty($message_type)): ?>
    Swal.fire({
        title: '<?= $message_type === 'success' ? 'Berhasil!' : 'Error!' ?>',
        text: '<?= addslashes($message) ?>',
        icon: '<?= $message_type ?>',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0ea5e9',
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'rounded-lg'
        }
    });
    <?php endif; ?>
});

// Cek pesan dari localStorage
document.addEventListener('DOMContentLoaded', function() {
    const savedMessage = localStorage.getItem('statusUpdateMessage');
    const savedMessageType = localStorage.getItem('statusUpdateMessageType');

    if (savedMessage) {
        Swal.fire({
            title: savedMessageType === 'success' ? 'Berhasil!' : 'Error!',
            text: savedMessage,
            icon: savedMessageType || 'success',
            confirmButtonText: 'OK'
        });

        // Hapus dari localStorage setelah ditampilkan
        localStorage.removeItem('statusUpdateMessage');
        localStorage.removeItem('statusUpdateMessageType');
    }

    // Pesan dari session (yang sudah ada)
    <?php if (!empty($message) && !empty($message_type)): ?>
    Swal.fire({
        title: '<?= $message_type === 'success' ? 'Berhasil!' : 'Error!' ?>',
        text: '<?= addslashes($message) ?>',
        icon: '<?= $message_type ?>',
        confirmButtonText: 'OK'
    });
    <?php endif; ?>
});
</script>

</body>

</html>