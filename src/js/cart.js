let cart = [];
let total = 0;

  // Fungsi untuk menambahkan item ke keranjang
  function tambahKeKeranjang(nama, harga) {
    // Tambah ke array cart
    cart.push({ nama, harga });

    // Tambah ke elemen HTML
    const cartItemsContainer = document.getElementById('cart-items');
    const item = document.createElement('div');
    item.classList.add('flex', 'justify-between', 'py-1', 'border-b', 'text-sm', 'text-black');
    item.innerHTML = `<span>${nama}</span><span>Rp${harga.toLocaleString()}</span>`;
    cartItemsContainer.appendChild(item);

    // Update total dan jumlah item
    total += harga;
    document.getElementById('cart-total').textContent = total.toLocaleString();
    document.getElementById('cart-count').textContent = cart.length;
  }

  // Tambahkan event listener untuk semua tombol Beli
  document.addEventListener('DOMContentLoaded', function () {
    const beliButtons = document.querySelectorAll('.btn-beli');
    beliButtons.forEach(btn => {
      btn.addEventListener('click', function () {
        const nama = this.getAttribute('data-nama');
        const harga = parseInt(this.getAttribute('data-harga'));
        tambahKeKeranjang(nama, harga);
      });
    });
  });