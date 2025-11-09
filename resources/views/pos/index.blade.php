@extends('layout.main')

@section('title', 'Point of Sale')

@push('page-styles')
{{-- CSS Khusus untuk halaman POS --}}
<style>
    /* Membuat layout POS full-height */
    .pos-container {
        display: flex;
        height: calc(100vh - 56px); /* 56px adalah tinggi header */
    }

    /* Bagian daftar produk */
    .product-list {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1.5rem;
        position: relative; /* Diperlukan untuk alert placeholder */
    }

    .product-card {
        cursor: pointer;
        transition: transform 0.1s ease-in-out, box-shadow 0.1s ease-in-out;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Bagian keranjang/tagihan */
    .cart-section {
        flex-basis: 400px;
        flex-shrink: 0;
        background-color: #f8f9fa;
        border-left: 1px solid #dee2e6;
        display: flex;
        flex-direction: column;
    }

    .cart-items {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1rem;
    }

    .cart-summary {
        padding: 1rem;
        border-top: 1px solid #dee2e6;
    }

    .quantity-controls button {
        width: 30px;
    }
    .quantity-controls input {
        width: 40px;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="pos-container">
    {{-- KIRI: DAFTAR PRODUK --}}
    <div class="product-list">
        
        {{-- !!! PERUBAHAN 1: Tambahkan Placeholder Alert !!! --}}
        <div id="alert-placeholder" class="position-sticky" style="top: 10px; z-index: 1050;"></div>

        {{-- Header & Search --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4">Products</h2>
            <div class="w-50">
                <input type="text" class="form-control" placeholder="Search products...">
            </div>
        </div>

        {{-- Filter Kategori --}}
        <div class="mb-4">
            <button class="btn btn-outline-secondary btn-sm">All</button>
            {{-- Nanti Anda bisa buat ini dinamis --}}
        </div>

        {{-- Grid Produk (DINAMIS DARI CONTROLLER) --}}
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4" id="product-grid">
            
            {{-- Loop data dari FrontPosController --}}
            @forelse ($products as $product)
                <div class="col product-card-clickable" 
                     data-product-id="{{ $product['id'] }}" 
                     data-product-name="{{ addslashes(string: $product['name']) }}" 
                     data-product-price="{{ $product['price'] }}">
                    
                    <div class="card h-100 product-card">
                        {{-- Ganti placeholder dengan gambar produk jika ada --}}
                        <img  height="200px" src="{{ $product['image'] ?? 'https://via.placeholder.com/150/808080/FFFFFF?text=Fashion' }}" class="card-img-top" alt="{{ $product['name'] }}">
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1">{{ $product['name'] }}</h6>
                            <p class="card-text fw-bold">Rp {{ number_format($product['price'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-muted">No products found in the inventory.</p>
                </div>
            @endforelse
            
        </div>
    </div>

    {{-- KANAN: KERANJANG / TAGIHAN --}}
    <div class="cart-section">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <h5 class="mb-0">Current Order</h5>
            <button class="btn btn-danger btn-sm" onclick="clearCart()">Clear All</button>
        </div>

        {{-- Daftar Item di Keranjang --}}
        <div class="cart-items" id="cart-items">
            {{-- Tampilan keranjang kosong (default) --}}
        </div>

        {{-- Ringkasan dan Pembayaran --}}
        <div class="cart-summary">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal</span>
                <span id="cart-subtotal">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Tax (11%)</span>
                <span id="cart-tax">Rp 0</span>
            </div>
             <div class="d-flex justify-content-between fw-bold fs-5">
                <span>Total</span>
                <span id="cart-total">Rp 0</span>
            </div>
            <div class="d-grid gap-2 mt-3">
                <button class="btn btn-primary btn-lg" id="payment-button" onclick="processPayment()">Process Payment</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
    // --- DATA DARI CONTROLLER ---
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // --- INISIALISASI ---
    let cart = {}; 
    const TAX_RATE = 0.11; // 11%

    // --- FUNGSI UTAMA ---

    // !!! PERUBAHAN 2: Fungsi baru untuk menampilkan Bootstrap Alert !!!
    function showAlert(message, type = 'success') {
        const alertPlaceholder = $('#alert-placeholder');
        
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Hapus alert lama (jika ada) dan tambahkan yang baru
        alertPlaceholder.empty().append(alertHtml);

        // Auto-dismiss setelah 5 detik
        setTimeout(() => {
            alertPlaceholder.find('.alert').alert('close');
        }, 5000);
    }

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0
        }).format(number);
    }

    function addToCart(productId, productName, price) {
        if (cart[productId]) {
            cart[productId].quantity++;
        } else {
            cart[productId] = {
                name: productName, 
                price: price,
                quantity: 1
            };
        }
        updateCart();
    }
    
    function changeQuantity(productId, amount) {
        if (cart[productId]) {
            cart[productId].quantity += amount;
            if (cart[productId].quantity <= 0) {
                delete cart[productId];
            }
        }
        updateCart();
    }
    
    function clearCart() {
        if(confirm('Are you sure you want to clear the cart?')) {
            cart = {};
            updateCart();
        }
    }

    function updateCart() {
        const cartItemsContainer = $('#cart-items');
        cartItemsContainer.empty();
        let subtotal = 0;

        if (Object.keys(cart).length === 0) {
            cartItemsContainer.html(`
                <div class="text-center text-muted mt-4">
                    <p>Your cart is empty</p>
                    <i class="bi bi-cart-x" style="font-size: 4rem;"></i>
                </div>
            `);
        } else {
            for (const productId in cart) {
                const item = cart[productId];
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;

                const itemElement = `
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">${item.name}</h6>
                                    <small class="text-muted">${formatRupiah(item.price)}</small>
                                </div>
                                <div class="d-flex align-items-center quantity-controls">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="changeQuantity(${productId}, -1)">-</button>
                                    <input type="text" class="form-control form-control-sm mx-1" value="${item.quantity}" readonly>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="changeQuantity(${productId}, 1)">+</button>
                                </div>
                                <span class="fw-bold">${formatRupiah(itemTotal)}</span>
                            </div>
                        </div>
                    </div>
                `;
                cartItemsContainer.append(itemElement);
            }
        }
        
        const tax = subtotal * TAX_RATE;
        const total = subtotal + tax;
        
        $('#cart-subtotal').text(formatRupiah(subtotal));
        $('#cart-tax').text(formatRupiah(tax));
        $('#cart-total').text(formatRupiah(total));
    }
    
    // --- PERUBAHAN 3: Ganti alert() dengan showAlert() ---
    async function processPayment() {
        const paymentButton = $('#payment-button');
        
        if (Object.keys(cart).length === 0) {
            showAlert('Cart is empty!', 'warning'); // <-- Ganti alert()
            return;
        }

        const payload = {
            cart: cart, 
            tax_rate: TAX_RATE 
        };

        paymentButton.prop('disabled', true).text('Processing...');

        try {
            const response = await fetch("{{ route('pos.processPayment') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN, 
                    'Accept': 'application/json' 
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.ok) { 
                showAlert(result.message, 'success'); // <-- Ganti alert()
                cart = {}; 
                updateCart();
            } else {
                // Tampilkan error validasi atau server error dari API
                showAlert('Error: ' + (result.message || 'Failed to process payment.'), 'danger'); // <-- Ganti alert()
                console.error(result);
            }

        } catch (error) {
            console.error('Fetch Error:', error);
            showAlert('An unexpected error occurred. Please check console.', 'danger'); // <-- Ganti alert()
        } finally {
            paymentButton.prop('disabled', false).text('Process Payment');
        }
    }

    $(document).ready(function() {
        updateCart();

        $('#product-grid').on('click', '.product-card-clickable', function() {
            const $card = $(this); 
            const productId = $card.data('product-id');
            const productName = $card.data('product-name');
            const productPrice = $card.data('product-price');
            addToCart(productId, productName, productPrice);
        });
    });
</script>
@endpush

