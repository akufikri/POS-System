/**
 * POS module
 * Handles cart logic, product filtering, and order submission
 */
window.AppPOS = (function () {
    var cart = [];
    var $cartList = null;
    var $cartTotal = null;
    var $productGrid = null;

    function init() {
        $cartList = $('#cart-items');
        $cartTotal = $('#cart-total');
        $productGrid = $('#product-grid');

        // Search listener
        $('#product-search').on('input', function () {
            filterProducts();
        });

        // Category filter listener
        $('.category-tab').on('click', function () {
            $('.category-tab').removeClass('bg-[#edcc94] text-[#010101]').addClass('bg-white text-[#6b7280]');
            $(this).removeClass('bg-white text-[#6b7280]').addClass('bg-[#edcc94] text-[#010101]');
            filterProducts();
        });

        // Checkout button
        $('#btn-checkout').on('click', function() {
            if (cart.length === 0) {
                AppToast.error('Keranjang masih kosong.');
                return;
            }
            openCheckoutModal();
        });
    }

    function filterProducts() {
        var query = $('#product-search').val().toLowerCase();
        var categoryId = $('.category-tab.bg-\\[\\#edcc94\\]').data('id');

        $('.product-card').each(function () {
            var $card = $(this);
            var name = $card.data('name').toLowerCase();
            var cat = $card.data('category');
            var matchesSearch = name.includes(query);
            var matchesCategory = !categoryId || cat == categoryId;

            if (matchesSearch && matchesCategory) {
                $card.removeClass('hidden');
            } else {
                $card.addClass('hidden');
            }
        });
    }

    function addToCart(productId, name, price) {
        var $card = $(`.product-card[data-id="${productId}"]`);
        var availableStock = parseInt($card.data('stock')) || 0;
        
        var existing = cart.find(item => item.product_id === productId);
        var currentQtyInCart = existing ? existing.quantity : 0;

        if (currentQtyInCart + 1 > availableStock) {
            AppToast.error('Stok tidak mencukupi.');
            return;
        }

        if (existing) {
            existing.quantity++;
        } else {
            cart.push({
                product_id: productId,
                name: name,
                price: price,
                quantity: 1,
                max_stock: availableStock
            });
        }
        renderCart();
    }

    function updateQuantity(productId, delta) {
        var item = cart.find(item => item.product_id === productId);
        if (!item) return;

        if (delta > 0 && item.quantity + delta > item.max_stock) {
            AppToast.error('Stok tidak mencukupi.');
            return;
        }

        item.quantity += delta;
        if (item.quantity <= 0) {
            cart = cart.filter(i => i.product_id !== productId);
        }
        renderCart();
    }

    function renderCart() {
        if (!$cartList) return;
        $cartList.empty();
        var total = 0;

        cart.forEach(item => {
            var subtotal = item.price * item.quantity;
            total += subtotal;

            $cartList.append(`
                <div class="flex items-center justify-between p-3 bg-white border border-[#e5e5e5] rounded-xl mb-2">
                    <div class="flex-1 min-w-0 mr-2">
                        <h4 class="text-sm font-medium text-[#010101] truncate">${item.name}</h4>
                        <p class="text-xs text-[#6b7280]">Rp ${CurrencyInput.format(item.price)}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="AppPOS.updateQuantity(${item.product_id}, -1)" class="w-7 h-7 flex items-center justify-center bg-[#f5f5f5] text-[#010101] rounded-lg hover:bg-[#e5e5e5]">-</button>
                        <span class="text-sm font-bold w-5 text-center">${item.quantity}</span>
                        <button onclick="AppPOS.updateQuantity(${item.product_id}, 1)" class="w-7 h-7 flex items-center justify-center bg-[#f5f5f5] text-[#010101] rounded-lg hover:bg-[#e5e5e5]">+</button>
                    </div>
                </div>
            `);
        });

        $cartTotal.text('Rp ' + CurrencyInput.format(total));
        $('#checkout-total-display').text('Rp ' + CurrencyInput.format(total));
    }

    function openCheckoutModal() {
        AppModal.open('modal-checkout');
        $('#payment-amount').val('').focus();
        calculateChange();
    }

    function calculateChange() {
        var total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        var payment = CurrencyInput.unformat($('#payment-amount').val());
        var change = payment - total;

        if (payment > 0 && change >= 0) {
            $('#change-amount').text('Rp ' + CurrencyInput.format(change)).removeClass('text-[#ef4444]').addClass('text-[#16a34a]');
            $('#btn-submit-order').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        } else {
            $('#change-amount').text('Rp 0').removeClass('text-[#16a34a]').addClass('text-[#ef4444]');
            $('#btn-submit-order').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
        }
    }

    function submitOrder() {
        var total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        var payment = CurrencyInput.unformat($('#payment-amount').val());

        if (payment < total) {
            AppToast.error('Pembayaran tidak mencukupi.');
            return;
        }

        var data = {
            items: cart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity
            })),
            notes: $('#order-notes').val()
        };

        var $btn = $('#btn-submit-order');
        $btn.prop('disabled', true).text('Processing...');

        AppAjax.post('/orders', data, function (response) {
            AppToast.success('Transaksi Berhasil!');
            cart = [];
            renderCart();
            AppModal.close('modal-checkout');
            
            // Refresh page to update stock displays and dashboard stats
            setTimeout(function() {
                location.reload();
            }, 1000);
            
        }, function(xhr) {
            $btn.prop('disabled', false).text('Simpan Transaksi');
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memproses transaksi.';
            AppToast.error(msg);
        });
    }

    return {
        init: init,
        addToCart: addToCart,
        updateQuantity: updateQuantity,
        calculateChange: calculateChange,
        submitOrder: submitOrder
    };
})();

$(function() {
    if ($('#pos-container').length) {
        AppPOS.init();
    }
});
