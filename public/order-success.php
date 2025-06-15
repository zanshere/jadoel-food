<?php
include_once __DIR__ . "/../config/connect.php";
include_once __DIR__ . "/../config/baseURL.php";

// Cek apakah ada order success
if (!isset($_SESSION['order_success'])) {
    header('Location: ' . BASE_URL);
    exit;
}

$order_id = $_SESSION['order_id'];

// Header
include_once __DIR__ . "/../includes/header.php";
?>

<!-- Main Content -->
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-green-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success Card -->
        <div class="card bg-white shadow-2xl">
            <!-- Header with Animation -->
            <div class="card-body text-center p-8">
                <!-- Success Icon with Animation -->
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-success rounded-full shadow-lg animate-bounce">
                        <i class="fas fa-check text-white text-3xl"></i>
                    </div>
                </div>

                <!-- Success Message -->
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">
                    🎉 Pesanan Berhasil!
                </h1>
                <p class="text-gray-600 text-lg mb-6">
                    Terima kasih telah berbelanja di <span class="text-primary font-semibold">Jadoel Food</span>
                </p>

                <!-- Order Info Card -->
                <div class="card bg-gradient-to-r from-primary to-secondary text-white mb-6">
                    <div class="card-body">
                        <div class="flex items-center justify-center mb-3">
                            <i class="fas fa-receipt text-white text-2xl mr-3"></i>
                            <h2 class="text-xl font-bold">Pesanan Anda Telah Diterima</h2>
                        </div>
                        <div class="divider divider-neutral"></div>
                        <p class="text-lg">
                            Nomor Pesanan: 
                            <span class="badge badge-warning badge-lg font-bold ml-2">#<?= $order_id ?></span>
                        </p>
                    </div>
                </div>

                <!-- Information Section -->
                <div class="alert alert-info shadow-lg mb-6">
                    <div>
                        <i class="fas fa-info-circle text-xl"></i>
                        <div class="text-left">
                            <h3 class="font-bold">Informasi Pesanan</h3>
                            <div class="text-sm mt-1">
                                <p class="mb-1">✅ Detail pesanan telah dikirim ke email Anda</p>
                                <p>📋 Cek status pesanan di <a href="<?= BASE_URL ?>public/orders.php" class="link link-primary font-semibold">Riwayat Pesanan</a></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?= BASE_URL ?>public/orders.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-history mr-2"></i>
                        Lihat Riwayat Pesanan
                    </a>
                    <a href="<?= BASE_URL ?>#products" class="btn btn-outline btn-lg btn-info transition-all hover:text-white duration-300 ease-in-out">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Lanjut Belanja
                    </a>
                </div>

                <!-- Additional Info -->
                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-clock text-primary mr-2"></i>
                        Apa Selanjutnya?
                    </h4>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p>🍽️ Tim dapur kami akan segera memproses pesanan Anda</p>
                        <p>📱 Anda akan mendapat notifikasi untuk setiap update status</p>
                        <p>🚚 Estimasi waktu pengiriman: 30-45 menit</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thank You Message -->
        <div class="text-center mt-6">
            <p class="text-gray-600">
                <i class="fas fa-heart text-red-500 mr-1"></i>
                Terima kasih telah mempercayai Jadoel Food untuk kebutuhan kuliner Anda!
            </p>
        </div>
    </div>
</div>

<!-- Success Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show success alert
    Swal.fire({
        icon: 'success',
        title: 'Pesanan Berhasil!',
        text: 'Pesanan #<?= $order_id ?> telah berhasil dibuat',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        toast: true,
        position: 'top-end'
    });
});
</script>

<?php
// Hapus session order success
unset($_SESSION['order_success']);
unset($_SESSION['order_id']);

// Footer
include_once __DIR__ . "/../includes/footer.php";
?>