let cart = [];
let total = 0;

// Fungsi untuk render ulang seluruh cart
function renderCart() {
  const cartItemsContainer = document.getElementById('cart-items');
  cartItemsContainer.innerHTML = '';

  cart.forEach((item, index) => {
    const itemElement = document.createElement('div');
    itemElement.classList.add('flex', 'justify-between', 'items-center', 'py-2', 'border-b', 'text-sm', 'text-black');
    
    itemElement.innerHTML = `
      <div class="flex-1">
        <span>${item.nama} (${item.jumlah}x)</span>
      </div>
      <div class="flex items-center gap-2">
        <span>Rp${(item.harga * item.jumlah).toLocaleString()}</span>
        <button onclick="hapusItem(${index})" class="text-red-500 hover:text-red-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;
    
    cartItemsContainer.appendChild(itemElement);
  });

  document.getElementById('cart-total').textContent = total.toLocaleString();
  document.getElementById('cart-count').textContent = cart.reduce((sum, item) => sum + item.jumlah, 0);
}

// Fungsi menghapus item dari cart
function hapusItem(index) {
  const item = cart[index];
  total -= item.harga * item.jumlah;
  
  if (total < 0) total = 0;
  
  cart.splice(index, 1);
  renderCart();
}

// Fungsi checkout ke WhatsApp
function checkout() {
  if (cart.length === 0) {
    alert('Keranjang belanja kosong!');
    return;
  }

  const phoneNumber = '6285941395388'; // Ganti dengan nomor WhatsApp tujuan
  const itemsText = cart.map(item => 
    `${item.nama}%20(${item.jumlah}x)%20-%20Rp${(item.harga * item.jumlah).toLocaleString()}`
  ).join('%0A');

  const totalText = `Total%20Pembayaran%3A%20Rp${total.toLocaleString()}`;
  const message = `Halo%2C%20saya%20ingin%20memesan%3A%0A%0A${itemsText}%0A%0A${totalText}`;
  
  window.open(`https://wa.me/${phoneNumber}?text=${message}`, '_blank');
  
  // Reset cart setelah checkout
  cart = [];
  total = 0;
  renderCart();
}

// Fungsi untuk menambahkan item ke keranjang
function tambahKeKeranjang(nama, harga) {
  const existingItem = cart.find(item => item.nama === nama);
  
  if (existingItem) {
    existingItem.jumlah += 1;
  } else {
    cart.push({ nama, harga, jumlah: 1 });
  }

  total += harga;
  renderCart();
}

// Event listener untuk tombol beli
document.addEventListener('DOMContentLoaded', function () {
  const beliButtons = document.querySelectorAll('.btn-beli');
  beliButtons.forEach(btn => {
    btn.addEventListener('click', function () {
      const nama = this.getAttribute('data-nama');
      const harga = parseInt(this.getAttribute('data-harga'));
      tambahKeKeranjang(nama, harga);
    });
  });

  // Tambahkan event listener untuk tombol checkout
  document.getElementById('checkout-btn').addEventListener('click', checkout);
});