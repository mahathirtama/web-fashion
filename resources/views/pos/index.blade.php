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

    /* Bagian daftar produk (KIRI) */
    .product-list {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1.5rem;
        position: relative;
        background-color: #fff;
    }

    .product-card {
        cursor: pointer;
        transition: transform 0.1s ease-in-out, box-shadow 0.1s ease-in-out;
        border: 1px solid #e0e0e0;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #0d6efd;
    }

    /* Bagian keranjang/tagihan (KANAN) */
    .cart-section {
        flex-basis: 400px;
        flex-shrink: 0;
        background-color: #f8f9fa;
        border-left: 1px solid #dee2e6;
        display: flex;
        flex-direction: column;
        box-shadow: -2px 0 5px rgba(0,0,0,0.05);
    }

    .cart-items {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1rem;
    }

    .cart-summary {
        padding: 1rem;
        background-color: #fff;
        border-top: 1px solid #dee2e6;
    }

    .quantity-controls button {
        width: 30px;
        height: 30px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .quantity-controls input {
        width: 40px;
        text-align: center;
        font-weight: bold;
    }

    /* --- ANIMASI & STYLE ALERT BARU --- */
    @keyframes shake {
        0% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        50% { transform: translateX(5px); }
        75% { transform: translateX(-5px); }
        100% { transform: translateX(0); }
    }

    .alert-shake {
        animation: shake 0.3s ease-in-out;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4); /* Bayangan merah */
        border-left: 5px solid #dc3545; /* Garis tebal merah di kiri */
        color: #842029;
        background-color: #f8d7da;
    }

    .alert-success-custom {
        box-shadow: 0 4px 15px rgba(25, 135, 84, 0.4); /* Bayangan hijau */
        border-left: 5px solid #198754; /* Garis tebal hijau di kiri */
        color: #0f5132;
        background-color: #d1e7dd;
    }

    /* Styling agar alert melayang di atas */
    #alert-placeholder {
        position: fixed;
        top: 80px; /* Sesuaikan dengan tinggi navbar */
        right: 20px;
        z-index: 9999;
        width: 400px;
        max-width: 90%;
    }
</style>
@endpush

@section('content')
{{-- Tempat Alert Muncul --}}
<div id="alert-placeholder"></div>

<div class="pos-container">
    {{-- KIRI: DAFTAR PRODUK --}}
    <div class="product-list">
        
        {{-- Header & Search --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-0 fw-bold">Products</h2>
                <small class="text-muted">Choose products to add to cart</small>
            </div>
            <div class="w-50">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="search-input" class="form-control border-start-0 ps-0" placeholder="Search products by name...">
                </div>
            </div>
        </div>

        {{-- Grid Produk --}}
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3" id="product-grid">
            
            @forelse ($products as $product)
                <div class="col product-item" data-name="{{ strtolower($product['name']) }}">
                    {{-- Card Produk --}}
                    <div class="card h-100 product-card product-card-clickable" 
                         data-product-id="{{ $product['id'] }}" 
                         data-product-name="{{ addslashes($product['name']) }}" 
                         data-product-price="{{ $product['selling_price'] }}">
                        
                        {{-- Gambar Produk --}}
                        <img style="height: 160px; object-fit: cover;" 
                             src="{{ $product['image'] }}" 
                             class="card-img-top bg-light" 
                             alt="{{ $product['name'] }}">
                        
                        <div class="card-body p-3 d-flex flex-column">
                            <h6 class="card-title mb-1 text-truncate" title="{{ $product['name'] }}">{{ $product['name'] }}</h6>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">Rp {{ number_format($product['selling_price'], 0, ',', '.') }}</span>
                                <small class="text-muted" style="font-size: 0.75rem">Stock: {{ $product['stock'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="text-muted">
                        <i class="bi bi-box-seam display-4"></i>
                        <p class="mt-3">No products found in the inventory.</p>
                    </div>
                </div>
            @endforelse
            
        </div>
    </div>

    {{-- KANAN: KERANJANG / TAGIHAN --}}
    <div class="cart-section">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-white">
            <h5 class="mb-0 fw-bold"><i class="bi bi-cart3 me-2"></i>Current Order</h5>
            <button class="btn btn-outline-danger btn-sm" onclick="clearCart()">
                <i class="bi bi-trash"></i> Clear
            </button>
        </div>

        {{-- Daftar Item di Keranjang --}}
        <div class="cart-items" id="cart-items">
            {{-- Item akan dirender via JS --}}
        </div>

        {{-- Ringkasan dan Pembayaran --}}
        <div class="cart-summary">
            <div class="d-flex justify-content-between mb-1 text-secondary">
                <span>Subtotal</span>
                <span id="cart-subtotal">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between mb-3 text-secondary">
                <span>Tax (11%)</span>
                <span id="cart-tax">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between fw-bold fs-4 mb-3 text-dark border-top pt-2">
                <span>Total</span>
                <span id="cart-total">Rp 0</span>
            </div>
            
            <div class="d-grid gap-2">
                <button class="btn btn-primary btn-lg py-3 fw-bold" id="payment-button" onclick="processPayment()">
                    <i class="bi bi-credit-card me-2"></i> Process Payment
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
    // --- KONFIGURASI ---
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const TAX_RATE = 0.11; // 11%

    // --- STATE ---
    let cart = {}; 

    // --- FUNGSI FORMAT RUPIAH ---
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0
        }).format(number);
    }

    // --- FUNGSI ALERT CUSTOM (Bagus & Animasi) ---
    function showAlert(message, type = 'success') {
        const alertPlaceholder = $('#alert-placeholder');
        
        let customClass = '';
        let icon = '';

        // Tentukan style berdasarkan tipe
        if (type === 'danger') {
            customClass = 'alert-shake'; // Class animasi getar
            icon = '<i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>';
        } else if (type === 'success') {
            customClass = 'alert-success-custom';
            icon = '<i class="bi bi-check-circle-fill fs-4 me-3"></i>';
        } else {
             // Default warning/info
            customClass = 'alert-warning';
            icon = '<i class="bi bi-info-circle-fill fs-4 me-3"></i>';
        }

        const alertHtml = `
            <div class="alert ${customClass} alert-dismissible fade show border-0 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    ${icon}
                    <div>
                        <strong class="d-block text-uppercase" style="font-size: 0.8rem; opacity: 0.8;">
                            ${type === 'danger' ? 'Transaction Failed' : 'Notification'}
                        </strong>
                        <span class="fw-bold">${message}</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Tampilkan Alert
        alertPlaceholder.empty().append(alertHtml);

        // Auto tutup (Error lebih lama: 6 detik, Sukses: 3 detik)
        const timeout = type === 'danger' ? 6000 : 3000;
        setTimeout(() => {
            alertPlaceholder.find('.alert').alert('close');
        }, timeout);
    }

    // --- MANAJEMEN KERANJANG ---
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
        if (Object.keys(cart).length === 0) return;
        
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
                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                    <i class="bi bi-cart-x display-1 mb-3 opacity-25"></i>
                    <p class="fw-bold">Your cart is empty</p>
                    <small>Select products to start selling</small>
                </div>
            `);
            $('#payment-button').prop('disabled', true);
        } else {
            $('#payment-button').prop('disabled', false);

            for (const productId in cart) {
                const item = cart[productId];
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;

                const itemElement = `
                    <div class="card mb-2 border-0 shadow-sm">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-truncate" style="max-width: 160px;">${item.name}</h6>
                                <span class="fw-bold text-primary">${formatRupiah(itemTotal)}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">${formatRupiah(item.price)} x</small>
                                <div class="d-flex align-items-center quantity-controls bg-light rounded border">
                                    <button class="btn btn-sm text-secondary" onclick="changeQuantity(${productId}, -1)">-</button>
                                    <input type="text" class="form-control-plaintext bg-transparent p-0" value="${item.quantity}" readonly>
                                    <button class="btn btn-sm text-secondary" onclick="changeQuantity(${productId}, 1)">+</button>
                                </div>
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

    // --- PROSES PEMBAYARAN (AJAX) ---
    async function processPayment() {
        const paymentButton = $('#payment-button');
        
        if (Object.keys(cart).length === 0) {
            showAlert('Cart is empty!', 'warning');
            return;
        }

        // Format cart ke Array untuk Backend
        const cartArray = Object.keys(cart).map(key => {
            return {
                product_id: key,
                quantity: cart[key].quantity
            };
        });

        const payload = {
            cart: cartArray,
            tax_rate: TAX_RATE 
        };

        // Loading State
        const originalBtnText = paymentButton.html();
        paymentButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

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
                // ✅ SUKSES (200/201)
                showAlert('Transaksi Berhasil Disimpan!', 'success');
                cart = {}; 
                updateCart();
                
                // Opsional: Print struk disini jika perlu
                
            } else {
                // ❌ ERROR / STOK KURANG (422)
                let errorMessage = 'Terjadi kesalahan.';

                // 1. Cek pesan error langsung (biasanya stok kurang dari controller kita tadi)
                if (result.message) {
                    errorMessage = result.message;
                }
                
                // 2. Cek error validasi Laravel (misal quantity negatif)
                if (result.errors) {
                    const firstErrorKey = Object.keys(result.errors)[0];
                    errorMessage = result.errors[firstErrorKey][0];
                }

                // Tampilkan Alert Merah + Shake
                showAlert(errorMessage, 'danger');
                console.error('Payment Error:', result);
            }

        } catch (error) {
            console.error('Fetch Error:', error);
            showAlert('Gagal menghubungi server. Periksa koneksi internet.', 'danger');
        } finally {
            // Reset Tombol
            paymentButton.prop('disabled', false).html(originalBtnText);
        }
    }

    // --- DOCUMENT READY ---
    $(document).ready(function() {
        updateCart();

        // Event Listener: Klik Produk Masuk Keranjang
        $('#product-grid').on('click', '.product-card-clickable', function() {
            const $card = $(this); 
            const productId = $card.data('product-id');
            const productName = $card.data('product-name');
            const productPrice = $card.data('product-price');
            
            // Efek visual klik
            $card.css('transform', 'scale(0.95)');
            setTimeout(() => $card.css('transform', ''), 100);

            addToCart(productId, productName, productPrice);
        });

        // Event Listener: Search Produk (Simple Client Side Search)
        $('#search-input').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('.product-item').filter(function() {
                $(this).toggle($(this).data('name').indexOf(value) > -1)
            });
        });
    });
</script>
@endpush