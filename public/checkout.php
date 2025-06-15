<?php 
include_once __DIR__ . "/../config/connect.php";
include_once __DIR__ . "/../config/baseURL.php";

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu";
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

// Cek apakah cart ada dan tidak kosong
if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['error'] = "Keranjang belanja Anda kosong. Silakan tambahkan produk terlebih dahulu.";
    header('Location: ' . BASE_URL);
    exit;
}

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Ambil data dari form
    $username = $_SESSION['username'] ?? '';
    $address = $_POST['address'];
    $city = $_POST['city'];
    $postal_code = $_POST['postal_code'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $note = $_POST['note'] ?? '';
    $payment_method = $_POST['payment_method'];
    
    // Validasi input
    $errors = [];
    if (empty($address)) $errors[] = "Alamat harus diisi";
    if (empty($city)) $errors[] = "Kota harus diisi";
    if (empty($postal_code)) $errors[] = "Kode pos harus diisi";
    if (empty($email)) $errors[] = "Email harus diisi";
    if (empty($phone)) $errors[] = "Telepon harus diisi";
    if (empty($payment_method)) $errors[] = "Metode pembayaran harus dipilih";
    
    if (empty($errors)) {
        // Mulai transaksi
        $conn->begin_transaction();
        
        try {
            // Insert order
            $stmt = $conn->prepare("INSERT INTO orders (username, address, city, postal_code, email, phone, note, payment_method) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssissss", $username, $address, $city, $postal_code, $email, $phone, $note, $payment_method);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Insert order items dan update stok produk
            foreach ($_SESSION['cart'] as $product_id => $item) {
                // Insert order item
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                       VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
                $stmt->execute();
                
                // Update stok produk
                $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->bind_param("ii", $item['quantity'], $product_id);
                $stmt->execute();
            }
            
            // Commit transaksi
            $conn->commit();
            
            // Kosongkan keranjang
            unset($_SESSION['cart']);
            
            // Redirect ke halaman sukses
            $_SESSION['order_success'] = true;
            $_SESSION['order_id'] = $order_id;
            header('Location: ' . BASE_URL . 'public/order-success.php');
            exit;
            
        } catch (Exception $e) {
            // Rollback transaksi jika ada error
            $conn->rollback();
            $error = "Terjadi kesalahan saat memproses pesanan: " . $e->getMessage();
        }
    }
}

// Header
include_once __DIR__ . "/../includes/header.php";
?>

<!-- Main Content -->
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-sky-500 px-6 py-4">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Checkout</h1>
            <p class="text-sky-100 mt-1">Lengkapi informasi pengiriman dan pembayaran</p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                <p class="font-bold">Error</p>
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                <p class="font-bold">Error</p>
                <p><?= $error ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Informasi Pengiriman -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-blue-500 mb-4">
                            <i class="fas fa-truck mr-2"></i>Informasi Pengiriman
                        </h2>

                        <!-- Alamat -->
                        <div class="mb-4">
                            <label for="address" class="block font-medium text-black mb-2">
                                Alamat Lengkap <span class="text-red-500">*</span>
                            </label>
                            <textarea name="address" id="address" required rows="3"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        </div>

                        <!-- Kota -->
                        <div class="mb-4">
                            <label for="city" class="block font-medium text-black mb-2">
                                Kota <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="city" id="city" required
                                value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>

                        <!-- Kode Pos -->
                        <div class="mb-4">
                            <label for="postal_code" class="block font-medium text-black mb-2">
                                Kode Pos <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="postal_code" id="postal_code" required
                                value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Informasi Kontak & Pembayaran -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-blue-500 mb-4">
                            <i class="fas fa-user mr-2"></i>Informasi Kontak
                        </h2>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="block font-medium text-black mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" required
                                value="<?= htmlspecialchars($_POST['email'] ?? $_SESSION['email'] ?? '') ?>"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>

                        <!-- Telepon -->
                        <div class="mb-4">
                            <label for="phone" class="block font-medium text-black mb-2">
                                Nomor Telepon <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="phone" id="phone" required
                                value="<?= htmlspecialchars($_POST['phone'] ?? $_SESSION['phone'] ?? '') ?>"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>

                        <!-- Catatan -->
                        <div class="mb-4">
                            <label for="note" class="block font-medium text-black mb-2">
                                Catatan (Opsional)
                            </label>
                            <textarea name="note" id="note" rows="2"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
                        </div>

                        <!-- Metode Pembayaran -->
                        <div class="mb-4">
                            <label for="payment_method" class="block font-medium text-black mb-2">
                                Metode Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_method" id="payment_method" required
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                <option value="">Pilih Metode Pembayaran</option>
                                <option value="DANA"
                                    <?= ($_POST['payment_method'] ?? '') === 'DANA' ? 'selected' : '' ?>>DANA</option>
                                <option value="OVO" <?= ($_POST['payment_method'] ?? '') === 'OVO' ? 'selected' : '' ?>>
                                    OVO</option>
                                <option value="GOPAY"
                                    <?= ($_POST['payment_method'] ?? '') === 'GOPAY' ? 'selected' : '' ?>>GoPay</option>
                                <option value="LINKAJA"
                                    <?= ($_POST['payment_method'] ?? '') === 'LINKAJA' ? 'selected' : '' ?>>LinkAja
                                </option>
                                <option value="SHOPEEPAY"
                                    <?= ($_POST['payment_method'] ?? '') === 'SHOPEEPAY' ? 'selected' : '' ?>>ShopeePay
                                </option>
                                <option value="BCA" <?= ($_POST['payment_method'] ?? '') === 'BCA' ? 'selected' : '' ?>>
                                    Transfer Bank (BCA)</option>
                                <option value="BRI" <?= ($_POST['payment_method'] ?? '') === 'BRI' ? 'selected' : '' ?>>
                                    Transfer Bank (BRI)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Pesanan -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-sky-500 mb-4">
                        <i class="fas fa-shopping-cart mr-2"></i>Ringkasan Pesanan
                    </h2>

                    <div class="divide-y divide-gray-200">
                        <?php 
                        $total = 0;
                        if (!empty($_SESSION['cart'])): 
                            foreach ($_SESSION['cart'] as $product_id => $item): 
                                $total += $item['price'] * $item['quantity'];
                        ?>
                        <div class="py-3 flex justify-between items-center">
                            <div>
                                <p class="font-medium text-sky-500"><?= htmlspecialchars($item['name']) ?></p>
                                <p class="text-sm text-gray-600"><?= $item['quantity'] ?> x Rp
                                    <?= number_format($item['price'], 0, ',', '.') ?></p>
                            </div>
                            <p class="font-medium">Rp
                                <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></p>
                        </div>
                        <?php 
                            endforeach; 
                        else: 
                        ?>
                        <div class="py-3 text-center text-gray-500">
                            Keranjang belanja kosong
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($_SESSION['cart'])): ?>
                        <div class="py-3 flex justify-between items-center font-bold text-lg text-sky-500">
                            <p>Total Harga</p>
                            <p>Rp <?= number_format($total, 0, ',', '.') ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-6 border-t border-gray-200">
                    <button type="submit" name="place_order"
                        class="bg-sky-500 text-white py-2 px-6 rounded-lg hover:bg-sky-600 transition-colors font-medium">
                        <i class="fas fa-check-circle mr-2"></i>Pesan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation sebelum submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const requiredFields = ['address', 'city', 'postal_code', 'email', 'phone', 'payment_method'];
        let isValid = true;

        requiredFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                element.classList.add('border-red-500');
                isValid = false;
            } else {
                element.classList.remove('border-red-500');
            }
        });

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                title: 'Form Tidak Lengkap',
                text: 'Silakan lengkapi semua field yang wajib diisi',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
        } else {
            // Show loading
            Swal.fire({
                title: 'Memproses Pesanan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    });
});
</script>

<!-- Footer -->
<?php include_once __DIR__ . '/../includes/footer.php'; ?>