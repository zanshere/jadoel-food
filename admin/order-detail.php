<?php 
include_once __DIR__ . "/../config/connect.php";
include_once __DIR__ . "/../config/baseURL.php";

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

// Ambil ID pesanan dari URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Dapatkan detail pesanan
$order_query = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$order_query->bind_param("i", $order_id);
$order_query->execute();
$order = $order_query->get_result()->fetch_assoc();

if (!$order) {
    $_SESSION['error'] = "Pesanan tidak ditemukan";
    header('Location: ' . BASE_URL . 'admin/orders.php');
    exit;
}

// Dapatkan item pesanan
$items_query = $conn->prepare("
    SELECT oi.*, p.product_name, p.product_image 
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$items_query->bind_param("i", $order_id);
$items_query->execute();
$items = $items_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Hitung total
$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Header
include __DIR__ . "/../includes/adminHeader.php";
?>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
        <div class="bg-sky-500 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Detail Pesanan #<?= $order['id'] ?></h1>
                    <div class="flex items-center mt-2">
                        <span class="text-sky-100 mr-2">Status:</span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium 
                            <?= $order['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                            <?= $order['status'] === 'Process' ? 'bg-purple-100 text-purple-800' : '' ?>
                            <?= $order['status'] === 'Delivery' ? 'bg-blue-100 text-blue-800' : '' ?>
                            <?= $order['status'] === 'Completed' ? 'bg-green-100 text-green-800' : '' ?>">
                            <?= $order['status'] ?>
                        </span>
                    </div>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>admin/orders.php" 
                       class="text-white hover:text-gray-200 flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Order Info -->
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-4">Informasi Pelanggan</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Nama:</span> <?= htmlspecialchars($order['username']) ?></p>
                    <p><span class="font-medium">Email:</span> <?= htmlspecialchars($order['email']) ?></p>
                    <p><span class="font-medium">Telepon:</span> <?= htmlspecialchars($order['phone']) ?></p>
                </div>
            </div>
            
            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-4">Alamat Pengiriman</h2>
                <div class="space-y-2">
                    <p><?= htmlspecialchars($order['address']) ?></p>
                    <p><?= htmlspecialchars($order['city']) ?>, <?= $order['postal_code'] ?></p>
                    <?php if (!empty($order['note'])): ?>
                    <p class="mt-2"><span class="font-medium">Catatan:</span> <?= htmlspecialchars($order['note']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-4">Informasi Pesanan</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Tanggal:</span> <?= date('d M Y H:i', strtotime($order['create_at'])) ?></p>
                    <p><span class="font-medium">Metode Pembayaran:</span> <?= strtoupper($order['payment_method']) ?></p>
                    <p><span class="font-medium">Status:</span> 
                        <span class="px-2 py-1 rounded-full text-sm 
                            <?= $order['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                            <?= $order['status'] === 'Process' ? 'bg-purple-100 text-purple-800' : '' ?>
                            <?= $order['status'] === 'Delivery' ? 'bg-blue-100 text-blue-800' : '' ?>
                            <?= $order['status'] === 'Completed' ? 'bg-green-100 text-green-800' : '' ?>">
                            <?= $order['status'] ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <div>
                <h2 class="text-lg font-bold text-gray-800 mb-4">Update Status</h2>
                <form method="POST" action="<?= BASE_URL ?>functions/updateOrderStatus.php">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <div class="flex items-center space-x-2">
                        <select name="status" class="form-select block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm rounded-md">
                            <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Process" <?= $order['status'] === 'Process' ? 'selected' : '' ?>>Process</option>
                            <option value="Delivery" <?= $order['status'] === 'Delivery' ? 'selected' : '' ?>>Delivery</option>
                            <option value="Completed" <?= $order['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                        <button type="submit" name="update_status" class="bg-sky-500 text-white px-4 py-2 rounded-md hover:bg-sky-600 transition-colors">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Item Pesanan</h2>
        </div>
        
        <div class="divide-y divide-gray-200">
            <?php foreach ($items as $item): ?>
            <div class="p-6 flex flex-col md:flex-row">
                <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                    <?php if (!empty($item['product_image'])): ?>
                    <img src="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($item['product_image']) ?>" 
                         alt="<?= htmlspecialchars($item['product_name']) ?>" 
                         class="w-20 h-20 object-cover rounded-lg">
                    <?php else: ?>
                    <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-gray-400 text-xl"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex-grow">
                    <h3 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($item['product_name']) ?></h3>
                    <p class="text-gray-600"><?= $item['quantity'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                    <p class="text-gray-600 mt-1">Subtotal: Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Order Summary -->
            <div class="p-6 bg-gray-50">
                <div class="flex justify-between items-center border-t border-gray-200 pt-4">
                    <span class="text-lg font-bold text-gray-900">Total</span>
                    <span class="text-lg font-bold text-sky-600">Rp <?= number_format($total, 0, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusForm = document.querySelector('form[action="<?= BASE_URL ?>functions/updateOrderStatus.php"]');
    
    if (statusForm) {
        statusForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const orderId = this.querySelector('input[name="order_id"]').value;
            const newStatus = this.querySelector('select[name="status"]').value;
            
            // Konfirmasi perubahan
            const confirmResult = await Swal.fire({
                title: 'Konfirmasi',
                html: `Ubah status pesanan #${orderId} menjadi <b>${newStatus}</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0ea5e9',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Ubah',
                cancelButtonText: 'Batal'
            });
            
            if (confirmResult.isConfirmed) {
                // Tampilkan loading
                Swal.fire({
                    title: 'Memproses...',
                    html: 'Sedang menyimpan perubahan',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                
                // Buat formData dan tambahkan update_status secara eksplisit
                const formData = new FormData();
                formData.append('order_id', orderId);
                formData.append('status', newStatus);
                formData.append('update_status', '1'); // Ini yang penting!
                
                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Simpan pesan di localStorage sebelum redirect
                        localStorage.setItem('statusUpdateMessage', result.message || 'Status berhasil diupdate');
                        localStorage.setItem('statusUpdateMessageType', 'success');
                        window.location.href = result.redirect || '<?= BASE_URL ?>admin/orders.php';
                    } else {
                        throw new Error(result.error || 'Invalid response from server');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Terjadi kesalahan saat mengupdate status',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });
    }
});
</script>

</body>
</html>