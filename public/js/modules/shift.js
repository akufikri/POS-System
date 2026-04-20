/**
 * Shift module
 */
$(function () {

    var currentShift = null;

    // ─── Load current shift ──────────────────────────────────────────────
    function loadCurrentShift() {
        AppAjax.get('/shifts/api/current', function (data) {
            currentShift = data.shift;
            renderShiftStatus();
        });
    }

    // ─── Render shift status ─────────────────────────────────────────────
    function renderShiftStatus() {
        var $container = $('#shift-status');
        var s = window.SHIFT_STRINGS;

        if (!currentShift) {
            $container.html(`
                <div class="text-center py-8">
                    <div class="text-6xl mb-4">💼</div>
                    <p class="text-[#6b7280] mb-6">${s.no_active_shift}</p>
                    <button onclick="openShiftModal()" class="inline-flex items-center gap-2 px-6 py-3 bg-[#edcc94] hover:bg-[#c9a96e] text-[#010101] rounded-xl text-sm font-medium transition-colors shadow-sm">
                        <iconify-icon icon="solar:play-circle-linear" stroke-width="1.5"></iconify-icon>
                        ${s.open_shift}
                    </button>
                </div>
            `);
        } else {
            var openedAt = new Date(currentShift.opened_at);
            var diffMs = new Date() - openedAt;
            var durationMin = Math.floor(diffMs / 1000 / 60);
            var durationText = durationMin > 0 ? durationMin + ' min' : s.just_now;

            $container.html(`
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-[#6b7280]">${s.shift_active_since}</p>
                            <p class="font-semibold text-lg text-[#010101]">${openedAt.toLocaleString('id-ID')}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-[#dcfce7]/30 text-[#16a34a] text-xs font-medium rounded-full">
                            <iconify-icon icon="solar:check-circle-linear" stroke-width="1.5" class="text-sm"></iconify-icon>
                            ${s.active} (${durationText})
                        </span>
                    </div>

                    <div class="border-t border-[#e5e5e5] pt-4"></div>

                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="bg-[#f5f5f5]/50 p-4 rounded-xl">
                            <p class="text-xs text-[#6b7280] mb-1">${s.opening_cash}</p>
                            <p class="text-lg font-semibold text-[#010101]">Rp ${numberFormat(currentShift.opening_cash)}</p>
                        </div>
                        <div class="bg-[#f5f5f5]/50 p-4 rounded-xl">
                            <p class="text-xs text-[#6b7280] mb-1">${s.transactions}</p>
                            <p class="text-lg font-semibold text-[#010101]" id="shift-tx-count">-</p>
                        </div>
                        <div class="bg-[#f5f5f5]/50 p-4 rounded-xl">
                            <p class="text-xs text-[#6b7280] mb-1">${s.revenue}</p>
                            <p class="text-lg font-semibold text-[#010101]" id="shift-revenue">-</p>
                        </div>
                    </div>

                    <div class="flex justify-center mt-4">
                        <button onclick="openCloseShiftModal()" class="inline-flex items-center gap-2 px-6 py-3 bg-[#fee2e2] hover:bg-[#fecaca] text-[#ef4444] rounded-xl text-sm font-medium transition-colors">
                            <iconify-icon icon="solar:stop-circle-linear" stroke-width="1.5"></iconify-icon>
                            ${s.close_shift}
                        </button>
                    </div>
                </div>
            `);

            loadShiftSummary();
        }
    }

    // ─── Load shift summary ─────────────────────────────────────────────
    function loadShiftSummary() {
        if (!currentShift) return;

        AppAjax.get('/shifts/' + currentShift.id, function (data) {
            $('#shift-tx-count').text(data.total_transactions);
            $('#shift-revenue').text('Rp ' + numberFormat(data.total_revenue));
        });
    }

    // ─── Open shift modal ───────────────────────────────────────────────────
    window.openShiftModal = function () {
        var s = window.SHIFT_STRINGS;
        var html = `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#010101] mb-2">${s.opening_cash} (Rp)</label>
                    <input type="text" id="opening-cash" class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-3 text-sm text-[#010101] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors input-currency" inputmode="numeric" value="0" placeholder="0">
                    <p class="text-xs text-[#6b7280] mt-1">${s.manage_work_shift}</p>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="AppModal.close('modal-shift')" class="flex-1 py-3 bg-[#f5f5f5] hover:bg-[#e5e5e5] text-[#010101] rounded-xl text-sm font-medium transition-colors border border-[#e5e5e5]">${s.cancel}</button>
                    <button type="button" onclick="submitOpenShift()" class="flex-1 py-3 bg-[#edcc94] hover:bg-[#c9a96e] text-[#010101] rounded-xl text-sm font-medium transition-colors">${s.open_shift}</button>
                </div>
            </div>
        `;

        $('#modal-shift .modal-box').html('<h3 class="text-lg font-semibold text-[#010101] mb-6">' + s.open_shift + '</h3>' + html);
        CurrencyInput.init('#opening-cash');
        AppModal.open('modal-shift');
    };

    // ─── Submit open shift ───────────────────────────────────────────────────
    window.submitOpenShift = function () {
        var s = window.SHIFT_STRINGS;
        var openingCash = CurrencyInput.unformat($('#opening-cash').val());

        AppAjax.post('/shifts', { opening_cash: openingCash }, function (data) {
            AppToast.success(s.shift_opened_successfully);
            AppModal.close('modal-shift');
            loadCurrentShift();
        });
    };

    // ─── Open close shift modal ────────────────────────────────────────────
    window.openCloseShiftModal = function () {
        if (!currentShift) return;
        var s = window.SHIFT_STRINGS;

        AppAjax.get('/shifts/' + currentShift.id, function (data) {
            var html = `
                <div class="space-y-4">
                    <div class="flex items-start gap-3 p-4 bg-[#dbeafe]/30 rounded-xl">
                        <iconify-icon icon="solar:info-circle-linear" stroke-width="1.5" class="text-[#2563eb] text-xl shrink-0 mt-0.5"></iconify-icon>
                        <div>
                            <p class="text-sm text-[#010101]">${s.expected_cash}</p>
                            <p class="font-semibold text-[#010101]">Rp ${numberFormat(data.expected_cash)}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#010101] mb-2">${s.actual_cash_in_drawer}</label>
                        <input type="text" id="actual-cash" class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-3 text-sm text-[#010101] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors input-currency" inputmode="numeric" placeholder="${s.enter_actual_cash_amount}">
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="button" onclick="AppModal.close('modal-shift')" class="flex-1 py-3 bg-[#f5f5f5] hover:bg-[#e5e5e5] text-[#010101] rounded-xl text-sm font-medium transition-colors border border-[#e5e5e5]">${s.cancel}</button>
                        <button type="button" onclick="submitCloseShift()" class="flex-1 py-3 bg-[#fee2e2] hover:bg-[#fecaca] text-[#ef4444] rounded-xl text-sm font-medium transition-colors">${s.close_shift}</button>
                    </div>
                </div>
            `;

            $('#modal-shift .modal-box').html('<h3 class="text-lg font-semibold text-[#010101] mb-6">' + s.close_shift + '</h3>' + html);
            CurrencyInput.init('#actual-cash');
            AppModal.open('modal-shift');
        });
    };

    // ─── Submit close shift ─────────────────────────────────────────────────
    window.submitCloseShift = function () {
        var s = window.SHIFT_STRINGS;
        var closingCash = CurrencyInput.unformat($('#actual-cash').val());

        if (isNaN(closingCash)) {
            AppToast.error(s.please_enter_actual_cash_amount);
            return;
        }

        AppAjax.post('/shifts/' + currentShift.id + '/close', { closing_cash: closingCash }, function (data) {
            var diffText = data.difference > 0 ? '+Rp ' + numberFormat(data.difference) : 'Rp ' + numberFormat(data.difference);
            var diffColor = data.difference === 0 ? 'text-[#16a34a]' : (data.difference > 0 ? 'text-[#ca8a04]' : 'text-[#ef4444]');

            AppToast.success(s.shift_closed_successfully + '. ' + s.difference + ': <span class="' + diffColor + '">' + diffText + '</span>', 5000);
            AppModal.close('modal-shift');
            loadCurrentShift();
        });
    };

    // ─── Helper ─────────────────────────────────────────────────────────────
    function numberFormat(num) {
        if (num === null || num === undefined) return '0';
        return num.toLocaleString('id-ID');
    }

    // ─── Init ─────────────────────────────────────────────────────────────────
    loadCurrentShift();
    
    if ($('#shift-history-body').length > 0) {
        loadShiftHistory();
    }

    function loadShiftHistory() {
        AppAjax.get('/shifts/api/list', function (data) {
            var tbody = $('#shift-history-body');
            var html = '';

            // Handle both array and paginated object
            var shifts = data.shifts.data || data.shifts || [];

            shifts.forEach(function(shift) {
                var diff = shift.cash_difference || 0;
                var diffClass = diff === 0 ? 'text-[#16a34a]' : (diff > 0 ? 'text-[#ca8a04]' : 'text-[#ef4444]');
                var diffIcon = diff === 0 ? 'check-circle' : (diff > 0 ? 'arrow-up' : 'arrow-down');

                html += '<tr class="hover:bg-[#f5f5f5]/30 transition-colors">' +
                    '<td class="px-6 py-4 text-sm text-[#010101]">' + (shift.user ? shift.user.name : (shift.user_name || '-')) + '</td>' +
                    '<td class="px-6 py-4 text-sm text-[#6b7280]">' + (new Date(shift.opened_at).toLocaleString('id-ID')) + '</td>' +
                    '<td class="px-6 py-4 text-sm text-[#6b7280]">' + (shift.closed_at ? new Date(shift.closed_at).toLocaleString('id-ID') : '-') + '</td>' +
                    '<td class="px-6 py-4 text-sm text-[#010101] text-right">Rp ' + numberFormat(shift.opening_cash) + '</td>' +
                    '<td class="px-6 py-4 text-sm text-[#010101] text-right">' + (shift.closing_cash ? 'Rp ' + numberFormat(shift.closing_cash) : '-') + '</td>' +
                    '<td class="px-6 py-4 text-sm font-medium text-right ' + diffClass + '">' +
                    '<span class="inline-flex items-center gap-1">' +
                    (diff !== 0 ? '<iconify-icon icon="solar:' + diffIcon + '-linear" stroke-width="1.5" class="text-sm"></iconify-icon>' : '') +
                    (diff > 0 ? '+' : '') + 'Rp ' + numberFormat(diff) +
                    '</span>' +
                    '</td>' +
                    '</tr>';
            });

            tbody.html(html || '<tr><td colspan="6" class="px-6 py-8 text-center text-[#6b7280] text-sm">No shift records yet.</td></tr>');
        });
    }
});
