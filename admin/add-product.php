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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $product_name = trim($_POST['product_name']);
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        // Validasi input
        if (empty($product_name)) {
            $error = "Nama produk harus diisi";
            $message_type = 'error';
        } elseif (empty($price) || $price <= 0) {
            $error = "Harga harus diisi dan lebih dari 0";
            $message_type = 'error';
        } elseif (empty($stock) || $stock < 0) {
            $error = "Stok harus diisi dan tidak boleh negatif";
            $message_type = 'error';
        } else {
            // Handle file upload
            $product_image = null;
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../uploads/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_info = pathinfo($_FILES['product_image']['name']);
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array(strtolower($file_info['extension']), $allowed_extensions)) {
                    $new_filename = uniqid() . '_' . time() . '.' . $file_info['extension'];
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                        $product_image = $new_filename;
                    } else {
                        $error = "Gagal mengupload gambar";
                        $message_type = 'error';
                    }
                } else {
                    $error = "Format gambar tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF";
                    $message_type = 'error';
                }
            }
            
            // Insert ke database jika tidak ada error
            if (!$error) {
                $stmt = $conn->prepare("INSERT INTO products (product_name, product_image, price, stock, is_available) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdii", $product_name, $product_image, $price, $stock, $is_available);
                
                if ($stmt->execute()) {
                    $message = "Produk berhasil ditambahkan";
                    $message_type = 'success';
                    // Reset form
                    $_POST = array();
                } else {
                    $error = "Gagal menambahkan produk: " . $conn->error;
                    $message_type = 'error';
                }
            }
        }
    }
}

// Header
include __DIR__ . "/../includes/adminHeader.php";
?>

<!-- Main Content -->
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-sky-500 px-6 py-4">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Tambah Produk Baru</h1>
            <p class="text-sky-100 mt-1">Tambahkan produk makanan baru ke dalam sistem</p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <form method="POST" enctype="multipart/form-data" class="space-y-6" id="addProductForm">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Informasi Produk -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-blue-500 mb-4">
                            <i class="fas fa-info-circle mr-2"></i>Informasi Produk
                        </h2>
                        
                        <!-- Nama Produk -->
                        <div class="mb-4">
                            <label for="product_name" class="block font-medium text-black mb-2">
                                Nama Produk <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="product_name" id="product_name" required
                                value="<?= isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : '' ?>"
                                placeholder="Contoh: Nasi Gudeg Yogya"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>

                        <!-- Stok -->
                        <div class="mb-4">
                            <label for="stock" class="block font-medium text-black mb-2">
                                Stok <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="stock" id="stock" required min="0"
                                value="<?= isset($_POST['stock']) ? $_POST['stock'] : '' ?>"
                                placeholder="100"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>

                        <!-- Ketersediaan -->
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_available" id="is_available" 
                                    <?php (!isset($_POST['add_product']) || isset($_POST['is_availabe']) || isset($_POST['stock']) || $_POST['stock'] > 0) ? 'checked' : '' ?>
                                    class="form-checkbox h-5 w-5 text-sky-500 rounded border-gray-300 focus:ring-sky-500">
                                <span class="ml-3 text-gray-900 font-medium">Produk tersedia</span>
                            </label>
                            <p id="stockWarning" class="text-sm text-red-600 mt-1 hidden">⚠️ Stok habis, produk akan ditandai tidak tersedia</p>
                        </div>
                    </div>

                    <!-- Gambar dan Harga -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-blue-500 mb-4">
                            <i class="fas fa-image mr-2"></i>Gambar & Harga
                        </h2>

                        <!-- Upload Gambar -->
                        <div class="mb-4">
                            <label for="product_image" class="block font-medium text-black mb-2">
                                Gambar Produk
                            </label>
                            <input type="file" name="product_image" id="product_image" accept="image/*"
                                class="w-full border border-gray-300 text-gray-900 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            <p class="text-sm text-gray-600 mt-1">Format: JPG, JPEG, PNG, GIF (Max: 5MB)</p>
                            
                            <!-- Preview Image -->
                            <div id="imagePreview" class="mt-3 hidden">
                                <img id="previewImg" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border border-gray-300">
                            </div>
                        </div>

                        <!-- Harga -->
                        <div class="mb-4">
                            <label for="price" class="block font-medium text-black mb-2">
                                Harga <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600">Rp</span>
                                <input type="number" name="price" id="price" required min="0" step="1000"
                                    value="<?= isset($_POST['price']) ? $_POST['price'] : '' ?>"
                                    placeholder="15000"
                                    class="w-full border border-gray-300 text-gray-900 rounded-lg pl-12 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Masukkan harga dalam Rupiah</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <button type="reset" 
                        class="bg-gray-500 text-white py-2 px-6 rounded-lg hover:bg-gray-600 transition-colors font-medium">
                        <i class="fas fa-undo mr-2"></i>Reset
                    </button>
                    <button type="submit" name="add_product"
                        class="bg-sky-500 text-white py-2 px-6 rounded-lg hover:bg-sky-600 transition-colors font-medium">
                        <i class="fas fa-plus mr-2"></i>Tambah Produk
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Tips Section -->
        <div class="mt-8 bg-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-600 mb-3">
                <i class="fas fa-lightbulb mr-2"></i>Tips Menambah Produk
            </h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <h4 class="font-medium text-blue-600 mb-2">Gambar Produk</h4>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>• Gunakan gambar dengan kualitas tinggi</li>
                        <li>• Pastikan makanan terlihat menarik</li>
                        <li>• Pencahayaan yang baik sangat penting</li>
                        <li>• Ukuran file maksimal 5MB</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-blue-600 mb-2">Status Produk</h4>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>• Centang "Produk tersedia" jika stok ada</li>
                        <li>• Jika stok 0, otomatis akan ditandai habis</li>
                        <li>• Status bisa diubah manual di halaman kelola produk</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation dan submit handling
    <?php if ($message_type === 'success'): ?>
    Swal.fire({
        title: 'Berhasil!',
        text: '<?= addslashes($message) ?>',
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0ea5e9'
    }).then(() => {
        // Reset form setelah sukses
        document.getElementById('addProductForm').reset();
        document.getElementById('imagePreview').classList.add('hidden');
    });
    <?php elseif ($error && $message_type === 'error'): ?>
    Swal.fire({
        title: 'Error!',
        text: '<?= addslashes($error) ?>',
        icon: 'error',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0ea5e9'
    });
    <?php endif; ?>

    // Image preview functionality
    const imageInput = document.getElementById('product_image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validasi ukuran file (5MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file maksimal adalah 5MB',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0ea5e9'
                });
                imageInput.value = '';
                imagePreview.classList.add('hidden');
                return;
            }

            // Validasi tipe file
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    title: 'Format File Tidak Didukung',
                    text: 'Gunakan format JPG, JPEG, PNG, atau GIF',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0ea5e9'
                });
                imageInput.value = '';
                imagePreview.classList.add('hidden');
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.classList.add('hidden');
        }
    });

    // Form validation sebelum submit
    document.getElementById('addProductForm').addEventListener('submit', function(e) {
        const product_name = document.getElementById('product_name').value.trim();
        const price = document.getElementById('price').value;
        const stock = document.getElementById('stock').value;

        if (!product_name) {
            e.preventDefault();
            Swal.fire({
                title: 'Nama Produk Wajib',
                text: 'Silakan isi nama produk',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }

        if (!price || price <= 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Harga Tidak Valid',
                text: 'Silakan isi harga yang valid (lebih dari 0)',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }

        if (!stock || stock < 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Stok Tidak Valid',
                text: 'Silakan isi stok yang valid (tidak boleh negatif)',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
            return false;
        }

        // Show loading
        Swal.fire({
            title: 'Menambahkan Produk...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });

    // Format harga input
    const priceInput = document.getElementById('price');
    priceInput.addEventListener('input', function(e) {
        // Remove non-numeric characters except for decimal point
        let value = e.target.value.replace(/[^\d]/g, '');
        e.target.value = value;
    });

    // Auto-uncheck jika stok = 0
    const stockInput = document.getElementById('stock');
    const isAvailableCheckbox = document.getElementById('is_available');
    const stockWarning = document.getElementById('stockWarning');

    stockInput.addEventListener('input', function() {
        if (this.value <= 0) {
            isAvailableCheckbox.checked = false;
            stockWarning.classList.remove('hidden');
        } else {
            stockWarning.classList.add('hidden');
        }
    });
});
</script>

</body>
</html>