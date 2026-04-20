/**
 * Transaction module
 * Handles: transaction detail modal via AJAX
 */
window.AppTransactions = {
    viewDetail: function (btn) {
        var $btn = $(btn);
        var id = $btn.data('id');

        // Reset & Show loading
        $('#tx-detail-id').text('#' + id);
        $('#tx-detail-loading').removeClass('hidden');
        $('#tx-detail-content').addClass('hidden');
        AppModal.open('modal-transaction-detail');

        AppAjax.get('/transactions/' + id, function (data) {
            $('#tx-detail-cashier').text(data.cashier);
            $('#tx-detail-date').text(data.created_at);
            $('#tx-detail-notes').text(data.notes || '-');
            $('#tx-detail-total').text(data.total_amount_formatted);
            $('#tx-detail-profit').text(data.profit_formatted);

            var tbody = $('#tx-detail-items').empty();
            $.each(data.items, function (i, item) {
                tbody.append(
                    '<tr>' +
                    '<td class="px-3 py-2 text-sm text-[#010101]">' + $('<div>').text(item.product_name).html() + '</td>' +
                    '<td class="px-3 py-2 text-sm text-[#010101] text-center">' + item.quantity + '</td>' +
                    '<td class="px-3 py-2 text-sm text-[#6b7280] text-right">' + item.unit_price_formatted + '</td>' +
                    '<td class="px-3 py-2 text-sm font-medium text-[#010101] text-right">' + item.subtotal_formatted + '</td>' +
                    '</tr>'
                );
            });

            $('#tx-detail-loading').addClass('hidden');
            $('#tx-detail-content').removeClass('hidden');

        }, function () {
            AppModal.close('modal-transaction-detail');
            AppToast.error('Gagal memuat detail transaksi.');
        });
    }
};
