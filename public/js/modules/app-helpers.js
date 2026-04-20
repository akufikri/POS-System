/**
 * App Helpers — Global functions for inline onclick handlers
 */

window.AppCategories = (function() {
    return {
        editCategory: function(btn) {
            var $btn = $(btn);
            var modalTitle = $('#modal-category-title');
            var idField = $('#category-id-field');
            var nameField = $('#category-name');
            var sortField = $('#category-sort');

            modalTitle.text('Edit Category');
            idField.val($btn.data('id'));
            nameField.val($btn.data('name'));
            sortField.val($btn.data('sort') || 0);

            AppModal.open('modal-category');
        },

        deleteCategory: function(btn) {
            var $btn = $(btn);
            var categoryId = $btn.data('id');
            var categoryName = $btn.data('name');

            $('#delete-category-name').text(categoryName);
            $('#btn-confirm-delete-category').data('id', categoryId);

            AppModal.open('modal-delete-category');
        }
    };
})();

window.AppProducts = (function() {
    return {
        editProduct: function(btn) {
            var $btn = $(btn);
            var data = $btn.data();

            $('#product-id-field').val(data.id);
            $('#product-name').val(data.name);
            $('#product-description').val(data.description || '');
            $('#product-price').val(data.price);
            $('#product-cost').val(data.cost);
            CurrencyInput.init('#product-price, #product-cost');
            $('#product-is_active').prop('checked', data.is_active === '1' || data.is_active === 1 || data.is_active === true);

            if (data.image_url) {
                $('#image-preview').attr('src', data.image_url).removeClass('hidden');
                $('#image-upload-text').addClass('hidden');
                $('#btn-remove-image').removeClass('hidden');
            }

            AppModal.open('modal-product');
        },

        deleteProduct: function(btn) {
            var $btn = $(btn);
            var id = $btn.data('id');
            var name = $btn.data('name');

            $('#delete-product-name').text(name);
            $('#btn-confirm-delete-product').data('id', id);

            AppModal.open('modal-delete-product');
        }
    };
})();

window.AppTransactions = (function() {
    return {
        viewDetail: function(btn) {
            var $btn = $(btn);
            var orderId = $btn.data('id');

            $('#tx-detail-id').text('#ORD-' + String(orderId).padStart(4, '0'));
            $('#tx-detail-loading').removeClass('hidden');
            $('#tx-detail-content').addClass('hidden');

            AppModal.open('modal-transaction-detail');

            AppAjax.get('/orders/' + orderId, function(data) {
                $('#tx-detail-loading').addClass('hidden');
                $('#tx-detail-content').removeClass('hidden');

                $('#tx-detail-date').text(data.order.created_at);
                $('#tx-detail-notes').text(data.order.notes || '-');

                var itemsHtml = '';
                data.order.items.forEach(function(item) {
                    itemsHtml += '<tr>' +
                        '<td class="px-3 py-2 text-sm">' + item.product_name + '</td>' +
                        '<td class="px-3 py-2 text-sm text-center">' + item.quantity + '</td>' +
                        '<td class="px-3 py-2 text-sm text-right">' + item.unit_price_formatted + '</td>' +
                        '<td class="px-3 py-2 text-sm text-right">' + item.subtotal_formatted + '</td>' +
                        '</tr>';
                });
                $('#tx-detail-items').html(itemsHtml);

                $('#tx-detail-total').text(data.order.total_amount_formatted);
            });
        }
    };
})();
