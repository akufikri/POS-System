/**
 * Product CRUD module
 * Handles: create, edit, delete via AJAX + DOM update
 */
$(function () {

    var STORE_URL = '/products';
    var isEditing = false;

    // ─── Image Preview ─────────────────────────────────────────────────
    $('#product-image').on('change', function () {
        var file = this.files[0];
        if (!file) return;

        var reader = new FileReader();
        reader.onload = function (e) {
            $('#image-preview').attr('src', e.target.result).removeClass('hidden');
            $('#image-upload-text').addClass('hidden');
            $('#btn-remove-image').removeClass('hidden');
        };
        reader.readAsDataURL(file);
    });

    // ─── Remove Image ─────────────────────────────────────────────────
    $('#btn-remove-image').on('click', function () {
        $('#product-image').val('');
        $('#image-preview').attr('src', '').addClass('hidden');
        $('#image-upload-text').removeClass('hidden');
        $(this).addClass('hidden');
    });

    // ─── Open Create Modal ──────────────────────────────────────────────
    $(document).on('click', '[onclick*="AppModal.open(\'modal-product\')"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        resetModal();
        isEditing = false;
        $('#modal-product-title').text('Add Product');
    });

    // ─── Open Edit Modal ───────────────────────────────────────────────
    $(document).on('click', '[onclick*="AppProducts.editProduct"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var data = $(this).data();
        resetModal();
        isEditing = true;

        $('#product-id-field').val(data.id);
        $('#product-name').val(data.name);
        $('#product-category_id').val(data.category_id || '');
        $('#product-description').val(data.description || '');
        $('#product-price').val(data.price);
        $('#product-cost').val(data.cost);
        $('#product-stock').val(data.stock);
        $('#stock-help').removeClass('hidden');
        CurrencyInput.init('#product-price, #product-cost');
        $('#product-is_active').prop('checked', data.is_active === '1' || data.is_active === 1 || data.is_active === true);

        if (data.image_url) {
            $('#image-preview').attr('src', data.image_url).removeClass('hidden');
            $('#image-upload-text').addClass('hidden');
            $('#btn-remove-image').removeClass('hidden');
        }

        AppModal.open('modal-product');
    });

    // ─── Form Submit (Create / Edit) ────────────────────────────────────
    $('#form-product').on('submit', function (e) {
        e.preventDefault();

        // Temporarily strip formatting for FormData
        var $currencyInputs = $(this).find('.input-currency');
        var originalValues = [];
        $currencyInputs.each(function() {
            originalValues.push($(this).val());
            $(this).val(CurrencyInput.unformat($(this).val()));
        });

        var formData = new FormData(this);

        // Restore formatting
        $currencyInputs.each(function(i) {
            $(this).val(originalValues[i]);
        });
        var productId = $('#product-id-field').val();

        // is_active checkbox — ensure value sent correctly
        formData.set('is_active', $('#product-is_active').is(':checked') ? '1' : '0');

        var $btn = $('#form-product button[type="submit"]');
        $btn.prop('disabled', true).text('Saving...');

        if (isEditing && productId) {
            AppAjax.put(STORE_URL + '/' + productId, formData, function (res) {
                AppModal.close('modal-product');
                AppToast.success('Product updated successfully');
                location.reload();
            }, function (xhr) {
                $btn.prop('disabled', false).text('Save Product');
                handleFormError(xhr);
            });
        } else {
            AppAjax.post(STORE_URL, formData, function (res) {
                AppModal.close('modal-product');
                AppToast.success('Product created successfully');
                location.reload();
            }, function (xhr) {
                $btn.prop('disabled', false).text('Save Product');
                handleFormError(xhr);
            });
        }
    });

    // ─── Open Delete Confirm ───────────────────────────────────────────
    $(document).on('click', '[onclick*="AppProducts.deleteProduct"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var id = $(this).data('id');
        var name = $(this).data('name');
        $('#delete-product-name').text(name);
        $('#btn-confirm-delete-product').data('id', id);
        AppModal.open('modal-delete-product');
    });

    // ─── Confirm Delete ─────────────────────────────────────────────────
    $('#btn-confirm-delete-product').on('click', function () {
        var id = $(this).data('id');
        var $btn = $(this);
        $btn.prop('disabled', true).text('Deleting...');

        AppAjax.delete(STORE_URL + '/' + id, function (res) {
            AppModal.close('modal-delete-product');
            AppToast.success('Product deleted successfully');
            location.reload();
        }, function (xhr) {
            $btn.prop('disabled', false).text('Delete');
            AppToast.error('Failed to delete product');
        });
    });

    // ─── Helpers ────────────────────────────────────────────────────────
    function resetModal() {
        $('#form-product')[0].reset();
        $('#product-id-field').val('');
        $('#product-category_id').val('');
        $('#image-preview').attr('src', '').addClass('hidden');
        $('#image-upload-text').removeClass('hidden');
        $('#btn-remove-image').addClass('hidden');
        $('#stock-help').addClass('hidden');
        $('#form-product button[type="submit"]').prop('disabled', false).text('Save Product');
    }

    function handleFormError(xhr) {
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            var errors = xhr.responseJSON.errors;
            var firstKey = Object.keys(errors)[0];
            AppToast.error(errors[firstKey][0]);
        } else {
            AppToast.error('Failed to save product. Please try again.');
        }
    }
});
