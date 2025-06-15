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

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $product_id = $_POST['product_id'];
        
        switch ($_POST['action']) {
            case 'toggle_availability':
                // Cek stok sebelum mengubah status
                $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $product = $result->fetch_assoc();
                
                if ($product['stock'] <= 0) {
                    $error = "Tidak bisa mengubah status produk dengan stok 0";
                    $message_type = 'error';
                } else {
                    $stmt = $conn->prepare("UPDATE products SET is_available = !is_available WHERE id = ?");
                    $stmt->bind_param("i", $product_id);
                    if ($stmt->execute()) {
                        $message = "Status ketersediaan produk berhasil diubah";
                        $message_type = 'success';
                    } else {
                        $error = "Gagal mengubah status produk";
                        $message_type = 'error';
                    }
                }
                break;
                
            case 'delete_product':
                // Ambil info gambar untuk dihapus
                $stmt = $conn->prepare("SELECT product_image FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $product = $result->fetch_assoc();
                
                // Hapus produk dari database
                $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                
                if ($stmt->execute()) {
                    // Hapus file gambar jika ada
                    if (!empty($product['product_image'])) {
                        $image_path = __DIR__ . '/../uploads/products/' . $product['product_image'];
                        if (file_exists($image_path)) {
                            unlink($image_path);
                        }
                    }
                    $message = "Produk berhasil dihapus";
                    $message_type = 'success';
                } else {
                    $error = "Gagal menghapus produk";
                    $message_type = 'error';
                }
                break;
        }
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
    $where_conditions[] = "product_name LIKE ?";
    $search_term = "%$search%";
    $params[] = $search_term;
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM products $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_items = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Get products
$query = "SELECT * FROM products $where_clause ORDER BY id DESC LIMIT ? OFFSET ?";
$params[] = $items_per_page;
$params[] = $offset;
$param_types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Kelola Produk</h1>
                    <p class="text-sky-100 mt-1">Kelola semua produk makanan dalam sistem</p>
                </div>
                <a href="<?= BASE_URL ?>admin/add-product.php" 
                   class="bg-white text-sky-500 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors font-medium">
                    <i class="fas fa-plus mr-2"></i>Tambah Produk
                </a>
            </div>
        </div>

        <!-- Search Section -->
        <div class="p-6 border-b border-gray-200">
            <form method="GET" class="flex">
                <div class="flex-1">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" placeholder="Cari produk..."
                               value="<?= htmlspecialchars($search) ?>"
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                    </div>
                </div>
                <button type="submit" class="bg-sky-500 text-white px-6 py-2 rounded-lg hover:bg-sky-600 transition-colors ml-2">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </form>
        </div>

        <!-- Stats Section -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-box text-2xl text-blue-500 mr-3"></i>
                        <div>
                            <p class="text-sm text-gray-600">Total Produk</p>
                            <p class="text-xl font-bold text-gray-900">
                                <?php
                                $total_query = "SELECT COUNT(*) as total FROM products";
                                $total_result = $conn->query($total_query);
                                echo $total_result->fetch_assoc()['total'];
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-2xl text-green-500 mr-3"></i>
                        <div>
                            <p class="text-sm text-gray-600">Tersedia</p>
                            <p class="text-xl font-bold text-gray-900">
                                <?php
                                $available_query = "SELECT COUNT(*) as total FROM products WHERE is_available = 1";
                                $available_result = $conn->query($available_query);
                                echo $available_result->fetch_assoc()['total'];
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-times-circle text-2xl text-red-500 mr-3"></i>
                        <div>
                            <p class="text-sm text-gray-600">Habis</p>
                            <p class="text-xl font-bold text-gray-900">
                                <?php
                                $unavailable_query = "SELECT COUNT(*) as total FROM products WHERE is_available = 0";
                                $unavailable_result = $conn->query($unavailable_query);
                                echo $unavailable_result->fetch_assoc()['total'];
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-cubes text-2xl text-yellow-500 mr-3"></i>
                        <div>
                            <p class="text-sm text-gray-600">Total Stok</p>
                            <p class="text-xl font-bold text-gray-900">
                                <?php
                                $stock_query = "SELECT SUM(stock) as total FROM products";
                                $stock_result = $conn->query($stock_query);
                                echo $stock_result->fetch_assoc()['total'] ?? 0;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($products as $product): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <?php if (!empty($product['product_image'])): ?>
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($product['product_image']) ?>" 
                                             alt="<?= htmlspecialchars($product['product_name']) ?>">
                                    <?php else: ?>
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-box text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($product['product_name']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">Rp <?= number_format($product['price'], 0, ',', '.') ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?= $product['stock'] ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form method="POST" class="inline">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="action" value="toggle_availability">
                                <button type="submit" class="px-2 py-1 rounded-full text-xs font-medium 
                                    <?= $product['is_available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>
                                    <?= $product['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : '' ?>"
                                    <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <?= $product['is_available'] ? 'Tersedia' : 'Habis' ?>
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <!-- Edit Button -->
                                <a href="<?= BASE_URL ?>admin/edit-product.php?id=<?= $product['id'] ?>" 
                                   class="text-blue-600 hover:text-blue-900"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Delete Button -->
                                <form method="POST" class="inline" onsubmit="confirmDelete(event)">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="action" value="delete_product">
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
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
                <span class="font-medium"><?= $total_items ?></span> produk
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

<!-- JavaScript for Delete Confirmation -->
<script>
function confirmDelete(event) {
    event.preventDefault();
    const form = event.target;
    
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Produk yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0EA5E9',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                text: 'Produk sedang dihapus',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            form.submit();
        }
    });
}

// Show success/error messages
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($message_type === 'success' && !empty($message)): ?>
    Swal.fire({
        title: 'Berhasil!',
        text: '<?= addslashes($message) ?>',
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0ea5e9'
    });
    <?php elseif ($message_type === 'error' && !empty($error)): ?>
    Swal.fire({
        title: 'Error!',
        text: '<?= addslashes($error) ?>',
        icon: 'error',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0ea5e9'
    });
    <?php endif; ?>
});
</script>

</body>
</html>