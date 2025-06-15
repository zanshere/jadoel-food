// Fungsi untuk memeriksa stok produk
async function checkStock(productId) {
    const baseUrl = document.getElementById('app').getAttribute('data-baseurl');
    
    try {
        const response = await fetch(`${baseUrl}functions/checkStock.php?id=${productId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Cache-Control': 'no-cache'
            }
        });
        
        // Periksa content type response
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const textResponse = await response.text();
            console.error('Non-JSON response:', textResponse);
            throw new Error('Server returned invalid response format');
        }
        
        // Parse JSON response
        let data;
        try {
            const responseText = await response.text();
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            throw new Error('Invalid JSON response from server');
        }
        
        if (!response.ok) {
            throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
        }
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to check stock');
        }
        
        return {
            stock: parseInt(data.stock) || 0,
            success: true,
            product_name: data.product_name || ''
        };
        
    } catch (error) {
        console.error('Error checking stock:', error);
        throw new Error(`Stock check failed: ${error.message}`);
    }
}

// Fungsi untuk menambahkan produk ke keranjang
async function addToCart(productId, productName, productPrice, productImage) {
    try {
        // Tampilkan loading
        const loadingAlert = Swal.fire({
            title: 'Memproses...',
            text: 'Memeriksa stok produk',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Periksa stok terlebih dahulu
        const stockResponse = await checkStock(productId);
        
        // Tutup loading
        loadingAlert.close();
        
        if (stockResponse.stock <= 0) {
            Swal.fire({
                title: 'Stok Habis',
                text: `Maaf, stok ${productName} sudah habis`,
                icon: 'error',
                confirmButtonColor: '#0ea5e9'
            });
            return;
        }

        // Inisialisasi keranjang jika belum ada
        if (!sessionStorage.getItem('cart')) {
            sessionStorage.setItem('cart', JSON.stringify({}));
        }
        
        // Ambil data keranjang
        let cart = JSON.parse(sessionStorage.getItem('cart'));
        
        // Jika produk sudah ada di keranjang
        if (cart[productId]) {
            // Periksa apakah jumlah melebihi stok
            if (cart[productId].quantity >= stockResponse.stock) {
                Swal.fire({
                    title: 'Stok Tidak Cukup',
                    html: `Stok ${productName} hanya tersisa ${stockResponse.stock}.<br>Jumlah di keranjang sudah maksimal.`,
                    icon: 'warning',
                    confirmButtonColor: '#0ea5e9'
                });
                return;
            }

            // Konfirmasi penambahan jumlah
            const result = await Swal.fire({
                title: 'Produk Sudah Ada',
                html: `${productName} sudah ada di keranjang (${cart[productId].quantity} buah).<br>Stok tersedia: ${stockResponse.stock}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Tambah 1 Lagi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#0ea5e9',
                cancelButtonColor: '#6b7280'
            });

            if (result.isConfirmed) {
                cart[productId].quantity += 1;
                sessionStorage.setItem('cart', JSON.stringify(cart));
                updateCartDisplay();
                
                Swal.fire({
                    title: 'Berhasil',
                    text: `Jumlah ${productName} di keranjang ditambah menjadi ${cart[productId].quantity}`,
                    icon: 'success',
                    confirmButtonColor: '#0ea5e9',
                    timer: 2000,
                    timerProgressBar: true
                });
            }
        } else {
            // Tambahkan produk baru dengan jumlah 1
            cart[productId] = {
                name: productName,
                price: parseFloat(productPrice),
                quantity: 1,
                image: productImage || ''
            };
            
            sessionStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
            
            Swal.fire({
                title: 'Berhasil!',
                text: `${productName} telah ditambahkan ke keranjang`,
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9',
                timer: 2000,
                timerProgressBar: true
            });
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        Swal.fire({
            title: 'Error',
            text: `Gagal menambahkan produk ke keranjang: ${error.message}`,
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    }
}

// Fungsi untuk update session PHP
async function updatePHPSessionCart(cart) {
    const baseUrl = document.getElementById('app').getAttribute('data-baseurl');
    
    try {
        const response = await fetch(`${baseUrl}public/update-cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ cart })
        });
        
        // Periksa content type response
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const textResponse = await response.text();
            console.error('Non-JSON response from update-cart:', textResponse);
            throw new Error('Server returned invalid response format');
        }
        
        // Parse JSON response
        let data;
        try {
            const responseText = await response.text();
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON Parse Error in updatePHPSessionCart:', parseError);
            throw new Error('Invalid JSON response from server');
        }
        
        if (!response.ok) {
            throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
        }
        
        if (!data || data.status !== 'success') {
            throw new Error(data?.message || 'Invalid response data');
        }
        
        return data;
    } catch (error) {
        console.error('Error updating cart:', error);
        throw new Error(`Cart sync failed: ${error.message}`);
    }
}

// Fungsi untuk update tampilan keranjang
function updateCartDisplay() {
    const cart = JSON.parse(sessionStorage.getItem('cart')) || {};
    const cartItems = document.getElementById('cart-items');
    const cartCount = document.getElementById('cart-count');
    const cartTotal = document.getElementById('cart-total');
    
    // Kosongkan konten keranjang
    if (cartItems) cartItems.innerHTML = '';
    
    let totalItems = 0;
    let totalPrice = 0;
    
    // Isi ulang keranjang
    for (const [productId, item] of Object.entries(cart)) {
        totalItems += parseInt(item.quantity) || 0;
        totalPrice += (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 0);
        
        if (cartItems) {
            const cartItem = document.createElement('div');
            cartItem.className = 'flex justify-between items-center py-2 border-b';
            cartItem.innerHTML = `
                <div class="flex items-center space-x-3">
                    ${item.image ? 
                        `<img src="${item.image}" alt="${item.name}" class="w-10 h-10 rounded object-cover">` : 
                        `<div class="w-10 h-10 rounded bg-gray-200 flex items-center justify-center ">
                            <i class="fas fa-box text-gray-300"></i>
                        </div>`
                    }
                    <div>
                        <p class="text-sm font-medium text-sky-500">${item.name}</p>
                        <p class="text-xs text-sky-500">${item.quantity} x Rp ${parseFloat(item.price).toLocaleString('id-ID')}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button class="text-red-500 text-xs decrease-item hover:text-red-700 p-1" data-id="${productId}">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span class="text-sm min-w-[20px] text-center text-sky-500">${item.quantity}</span>
                    <button class="text-green-500 text-xs increase-item hover:text-green-700 p-1" data-id="${productId}">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            `;
            cartItems.appendChild(cartItem);
        }
    }
    
    // Update total
    if (cartCount) cartCount.textContent = totalItems;
    if (cartTotal) cartTotal.textContent = totalPrice.toLocaleString('id-ID');
    
    // Update session PHP (async, tidak perlu menunggu)
    updatePHPSessionCart(cart).catch(error => {
        console.warn('Failed to sync cart with server:', error.message);
        // Tidak perlu alert untuk error sync ini
    });
}

// Event handler untuk keranjang
function handleCartEvents(e) {
    // Tombol beli
    if (e.target.closest('.btn-beli')) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const button = e.target.closest('.btn-beli');
        const productId = button.getAttribute('data-id');
        const productName = button.getAttribute('data-nama');
        const productPrice = parseFloat(button.getAttribute('data-harga'));
        const productImage = button.getAttribute('data-gambar');
        
        // Validasi data produk
        if (!productId || !productName || isNaN(productPrice)) {
            Swal.fire({
                title: 'Error',
                text: 'Data produk tidak valid',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
            return;
        }
        
        addToCart(productId, productName, productPrice, productImage);
    }
    
    // Tombol increase item
    if (e.target.closest('.increase-item')) {
        const button = e.target.closest('.increase-item');
        const productId = button.getAttribute('data-id');
        const cart = JSON.parse(sessionStorage.getItem('cart'));
        
        if (!cart[productId]) return;
        
        // Tampilkan loading kecil
        const originalIcon = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        checkStock(productId).then(stockResponse => {
            if (cart[productId].quantity >= stockResponse.stock) {
                Swal.fire({
                    title: 'Stok Tidak Cukup',
                    text: `Stok hanya tersisa ${stockResponse.stock}`,
                    icon: 'warning',
                    confirmButtonColor: '#0ea5e9'
                });
                return;
            }
            
            cart[productId].quantity += 1;
            sessionStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
        }).catch(error => {
            console.error('Error checking stock:', error);
            Swal.fire({
                title: 'Error',
                text: 'Gagal memeriksa stok produk',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        }).finally(() => {
            button.innerHTML = originalIcon;
            button.disabled = false;
        });
    }
    
    // Tombol decrease item
    if (e.target.closest('.decrease-item')) {
        const button = e.target.closest('.decrease-item');
        const productId = button.getAttribute('data-id');
        const cart = JSON.parse(sessionStorage.getItem('cart'));
        
        if (!cart[productId]) return;
        
        if (cart[productId].quantity > 1) {
            cart[productId].quantity -= 1;
        } else {
            delete cart[productId];
        }
        
        sessionStorage.setItem('cart', JSON.stringify(cart));
        updateCartDisplay();
    }
}

// Inisialisasi keranjang
function initCart() {
    // Hapus event listener lama jika ada
    document.removeEventListener('click', handleCartEvents);
    
    // Pasang event listener baru
    document.addEventListener('click', handleCartEvents);
    
    // Update tampilan awal
    updateCartDisplay();
}

// Jalankan saat DOM siap
document.addEventListener('DOMContentLoaded', function() {
    // Cegah inisialisasi ganda
    if (!window.cartInitialized) {
        initCart();
        window.cartInitialized = true;
    }
    
    // Event listener untuk tombol checkout
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn && !checkoutBtn.hasAttribute('data-listener-added')) {
        checkoutBtn.setAttribute('data-listener-added', 'true');
        
        checkoutBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const cart = JSON.parse(sessionStorage.getItem('cart')) || {};
            if (Object.keys(cart).length === 0) {
                Swal.fire({
                    title: 'Keranjang Kosong',
                    text: 'Silakan tambahkan produk terlebih dahulu',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0ea5e9'
                });
                return;
            }
            
            // Tampilkan loading
            const swalInstance = Swal.fire({
                title: 'Menyiapkan Checkout...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            try {
                // Update cart ke server
                const updateResult = await updatePHPSessionCart(cart);
                
                if (updateResult.status !== 'success') {
                    throw new Error(updateResult.message || 'Gagal menyinkronkan keranjang');
                }
                
                // Redirect ke checkout
                const baseUrl = document.getElementById('app').getAttribute('data-baseurl');
                window.location.href = `${baseUrl}public/checkout.php`;
                
            } catch (error) {
                swalInstance.close();
                Swal.fire({
                    title: 'Gagal',
                    text: error.message || 'Gagal memproses checkout. Silakan coba lagi.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444'
                });
            }
        });
    }
});