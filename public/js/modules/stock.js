/**
 * Stock module
 * Handles stock adjustment modal and submission
 */
window.AppStock = (function () {
    function openModal(type) {
        var $modal = $('#modal-stock');
        var $title = $('#modal-stock-title');
        var $typeInput = $('#stock-type');
        
        $typeInput.val(type);
        
        if (type === 'in') {
            $title.text(window.I18N_STOCK.stock_in || 'Stock In');
        } else {
            $title.text(window.I18N_STOCK.stock_out || 'Stock Out');
        }

        $('#form-stock')[0].reset();
        AppModal.open('modal-stock');
    }

    function submit(e) {
        e.preventDefault();
        var $form = $(e.target);
        var data = $form.serialize();

        AppAjax.post('/stock', data, function (response) {
            AppToast.success(response.message || 'Stok berhasil diperbarui.');
            AppModal.close('modal-stock');
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        }, function(xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memperbarui stok.';
            AppToast.error(msg);
        });
    }

    return {
        openModal: openModal,
        submit: submit
    };
})();
