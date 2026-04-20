/**
 * Dashboard module
 * Role-aware: owner sees full stats + revenue/profit chart
 *             cashier sees personal stats + activity chart
 */
$(function () {

    var POLL_INTERVAL = 5000;
    var pollTimer     = null;
    var revenueChart  = null;
    var IS_OWNER      = window.IS_OWNER === true;
    var I18N          = window.I18N || {};

    var COLOR_PRIMARY = '#edcc94';
    var COLOR_PROFIT  = '#4ade80';
    var COLOR_TX      = '#818cf8';
    var COLOR_GRID    = 'rgba(0,0,0,0.06)';
    var COLOR_TEXT    = 'rgba(0,0,0,0.45)';

    // ─── Chart ──────────────────────────────────────────────────────────────────
    function initChart(data) {
        var ctx = document.getElementById('revenue-chart');
        if (!ctx || !window.Chart) return;

        var datasets = IS_OWNER
            ? [
                {
                    label: I18N.revenue || 'Revenue',
                    data: data.revenue,
                    backgroundColor: COLOR_PRIMARY,
                    borderRadius: 6,
                    borderSkipped: false,
                    yAxisID: 'y',
                },
                {
                    label: I18N.profit || 'Profit',
                    data: data.profit,
                    backgroundColor: COLOR_PROFIT,
                    borderRadius: 6,
                    borderSkipped: false,
                    yAxisID: 'y',
                },
            ]
            : [
                {
                    label: I18N.revenue || 'Revenue',
                    data: data.revenue,
                    backgroundColor: COLOR_PRIMARY,
                    borderRadius: 6,
                    borderSkipped: false,
                    yAxisID: 'y',
                },
                {
                    label: I18N.transactions || 'Transactions',
                    data: data.transactions,
                    backgroundColor: COLOR_TX,
                    borderRadius: 6,
                    borderSkipped: false,
                    yAxisID: 'y2',
                },
            ];

        var scales = IS_OWNER
            ? {
                x: {
                    grid: { display: false },
                    ticks: { color: COLOR_TEXT, font: { size: 11 } },
                },
                y: {
                    grid: { color: COLOR_GRID },
                    border: { dash: [4, 4] },
                    ticks: {
                        color: COLOR_TEXT,
                        font: { size: 11 },
                        callback: rpShort,
                    },
                },
            }
            : {
                x: {
                    grid: { display: false },
                    ticks: { color: COLOR_TEXT, font: { size: 11 } },
                },
                y: {
                    position: 'left',
                    grid: { color: COLOR_GRID },
                    border: { dash: [4, 4] },
                    ticks: { color: COLOR_TEXT, font: { size: 11 }, callback: rpShort },
                },
                y2: {
                    position: 'right',
                    grid: { display: false },
                    ticks: {
                        color: COLOR_TEXT,
                        font: { size: 11 },
                        stepSize: 1,
                        callback: function (val) { return val + ' ' + (I18N.trans || 'tx'); },
                    },
                },
            };

        revenueChart = new Chart(ctx, {
            type: 'bar',
            data: { labels: data.labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#010101',
                        bodyColor: '#6b7280',
                        borderColor: '#e5e5e5',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function (ctx) {
                                var val = ctx.parsed.y;
                                if (ctx.dataset.label === I18N.transactions || ctx.dataset.label === 'Transactions' || ctx.dataset.label === 'Transaksi') {
                                    return ' ' + (I18N.transactions || 'Transactions') + ': ' + val;
                                }
                                return ' ' + ctx.dataset.label + ': Rp ' +
                                    Number(val).toLocaleString('id-ID');
                            },
                        },
                    },
                },
                scales: scales,
            },
        });
    }

    function rpShort(val) {
        if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + (I18N.jt || 'M');
        if (val >= 1000)    return 'Rp ' + (val / 1000).toFixed(0) + (I18N.rb || 'K');
        return 'Rp ' + val;
    }

    // ─── Stats update (owner) ───────────────────────────────────────────────────
    function updateOwnerStats(data) {
        $('#stat-today-revenue').text(data.revenue_formatted);
        $('#stat-today-profit').text(data.profit_formatted);
        $('#stat-today-tx').text(data.today_transactions);
        $('#stat-today-tx-desc').text(data.today_transactions + ' ' + (I18N.trans || 'transactions'));
        $('#stat-month-revenue').text(data.month_revenue_formatted);
        $('#stat-month-profit').text(data.month_profit_formatted);
        updateKasirTable(data.kasir_performance);
    }

    // ─── Stats update (cashier) ─────────────────────────────────────────────────
    function updateCashierStats(data) {
        $('#stat-today-revenue').text(data.revenue_formatted);
        $('#stat-today-tx').text(data.today_transactions);
        $('#stat-month-revenue').text(data.month_revenue_formatted);
        $('#stat-month-tx').text(data.month_transactions);
    }

    function updateKasirTable(rows) {
        var tbody = $('#kasir-table-body').empty();
        if (!rows || rows.length === 0) {
            tbody.append('<tr><td colspan="3" class="text-center text-base-content/40 py-6">' + (I18N.no_transactions_today || 'No transactions today') + '</td></tr>');
            return;
        }
        $.each(rows, function (i, k) {
            tbody.append(
                '<tr><td>' + $('<div>').text(k.name).html() + '</td>' +
                '<td class="text-right">' + k.transaction_count + '</td>' +
                '<td class="text-right">' + k.revenue_formatted + '</td></tr>'
            );
        });
    }

    // ─── Poll ───────────────────────────────────────────────────────────────────
    function poll() {
        AppAjax.get('/dashboard/summary', function (data) {
            if (IS_OWNER) {
                updateOwnerStats(data);
            } else {
                updateCashierStats(data);
            }
            $('#last-updated').text((I18N.updated || 'Updated') + ' ' + new Date().toLocaleTimeString('id-ID'));
        });
    }

    // ─── Boot ───────────────────────────────────────────────────────────────────
    if (window.CHART_DATA) initChart(window.CHART_DATA);

    $('#last-updated').text((I18N.updated || 'Updated') + ' ' + new Date().toLocaleTimeString('id-ID'));
    window.loadCurrentStats = poll;
    pollTimer = setInterval(poll, POLL_INTERVAL);

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            clearInterval(pollTimer);
        } else {
            poll();
            pollTimer = setInterval(poll, POLL_INTERVAL);
        }
    });

});
