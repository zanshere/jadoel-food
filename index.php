<?php 
include_once __DIR__ . "/config/connect.php";
include_once __DIR__ . "/config/baseURL.php";
?>

<!-- Header -->
<?php include "includes/header.php"; ?>

<!-- Main Content -->
<main class="flex-1">
    <!-- Carousel Section -->
    <section id="home" class="scroll-section relative">
        <div x-data="carousel()" x-init="start()" class="h-full">
            <div class="relative w-full h-full">
                <!-- Carousel Items -->
                <template x-for="(image, index) in images" :key="index">
                    <div class="absolute inset-0 transition-opacity duration-1000"
                        :class="{ 'opacity-100': current === index, 'opacity-0': current !== index }">
                        <img :src="image.src" :alt="image.alt" class="w-full h-full object-cover">
                    </div>
                </template>

                <!-- Hero Content -->
                <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                    <div class="text-center px-4">
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 md:mb-6 hero-text">
                            Selamat Datang di <br>Jadoel Food
                        </h1>
                        <a href="#products" class="inline-block bg-sky-500 text-white px-6 py-2 md:px-8 md:py-3 rounded-full 
              hover:bg-sky-600 transition-all text-sm md:text-base shadow-lg">
                            Jelajahi Produk Kami
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="scroll-section bg-gray-50 py-12 md:py-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center">
            <div class="grid md:grid-cols-2 gap-8 items-center">
                <div class="rounded-lg overflow-hidden shadow-lg">
                    <img src="<?= BASE_URL ?>assets/images/Logo.png" alt="About Us"
                        class="w-full h-64 sm:h-80 md:h-96 object-cover">
                </div>
                <div class="mt-6 md:mt-0">
                    <h2 class="text-2xl md:text-4xl font-bold text-sky-500 mb-4">Tentang Kami</h2>
                    <p class="text-gray-600 text-base md:text-lg">
                        Melestarikan cita rasa autentik sejak 1985 dengan bahan pilihan dan resep turun temurun
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="scroll-section bg-white py-12 md:py-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center">
            <div class="w-full">
                <h2 class="text-2xl md:text-4xl font-bold text-center text-sky-500 mb-8 md:mb-12">Produk Kami</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                // Ambil produk yang tersedia dari database
                $query = "SELECT * FROM products WHERE is_available = 1 AND stock > 0 ORDER BY id DESC";
                $result = $conn->query($query);
                
                if ($result->num_rows > 0):
                    while ($product = $result->fetch_assoc()):
                ?>
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow overflow-hidden">
                        <?php if (!empty($product['product_image'])): ?>
                        <img src="<?= BASE_URL ?>uploads/products/<?= htmlspecialchars($product['product_image']) ?>"
                            alt="<?= htmlspecialchars($product['product_name']) ?>"
                            class="w-full h-48 md:h-64 object-cover">
                        <?php else: ?>
                        <div class="w-full h-48 md:h-64 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-box text-4xl text-gray-400"></i>
                        </div>
                        <?php endif; ?>
                        <div class="p-4 md:p-6">
                            <h3 class="text-lg md:text-xl font-semibold text-sky-500 mb-2">
                                <?= htmlspecialchars($product['product_name']) ?></h3>
                            <div class="flex justify-between items-center">
                                <span class="text-xl md:text-2xl font-bold text-sky-500">Rp
                                    <?= number_format($product['price'], 0, ',', '.') ?></span>
                                <button
                                    class="bg-sky-500 text-white px-4 md:px-6 py-2 rounded-full text-sm md:text-base transition-all hover:bg-sky-700 duration-300 ease-in-out btn-beli"
                                    data-id="<?= $product['id'] ?>"
                                    data-nama="<?= htmlspecialchars($product['product_name']) ?>"
                                    data-harga="<?= $product['price'] ?>"
                                    data-gambar="<?= !empty($product['product_image']) ? BASE_URL . 'uploads/products/' . htmlspecialchars($product['product_image']) : '' ?>">
                                    <i class="fas fa-cart-plus mr-2"></i>Beli
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php
                    endwhile;
                else:
                ?>
                    <div class="col-span-3 text-center py-8">
                        <p class="text-gray-500">Tidak ada produk yang tersedia saat ini</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="scroll-section bg-gray-50 py-12 md:py-0">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center">
            <div class="w-full">
                <h2 class="text-2xl md:text-4xl font-bold text-center text-sky-500 mb-8 md:mb-12">Hubungi Kami</h2>
                <p class="text-black text-center mb-10">Hubungi via email untuk memesan atau pertanyaan lebih lanjut.
                </p>
                <form action="https://api.web3forms.com/submit" method="POST"
                    class="bg-white shadow-md rounded-lg p-6 space-y-4">
                    <!-- Replace with your Access Key -->
                    <input type="hidden" name="access_key" value="f1a451f3-2b87-4cc8-a772-7c036d7659d1">
                    <div>
                        <label for="name" class="block font-medium text-sky-500">Nama</label>
                        <input type="text" name="name" id="name"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring focus:border-sky-500">
                    </div>
                    <div>
                        <label for="email" class="block font-medium text-sky-500">Email</label>
                        <input type="email" name="email" id="email"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring focus:border-sky-500">
                    </div>
                    <div>
                        <label for="message" class="block font-medium text-sky-500">Pesan</label>
                        <textarea name="message" id="message" rows="4"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring focus:border-sky-500"></textarea>
                    </div>
                    <!-- Honeypot Spam Protection -->
                    <input type="checkbox" name="botcheck" class="hidden" style="display: none;">
                    <div class="text-center">
                        <button type="submit"
                            class="bg-sky-500 text-white px-6 py-2 rounded-full hover:bg-sky-700 transition-all">
                            Kirim Pesan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<!-- Scripts -->
<script src="<?= BASE_URL ?>src/js/carousel.js"></script>
<script src="<?= BASE_URL ?>src/js/cart.js"></script>

<!-- Footer -->
<?php include "includes/footer.php"; ?>