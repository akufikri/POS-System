@extends('layouts.app')

@section('title', __('ui.shifts'))

@section('content')

<section id="shifts" class="space-y-6">
    <x-ui.filter-bar 
        :action="route('shifts.index')" 
        :search-placeholder="__('ui.search')"
        :search-value="request('search')"
        :show-per-page="true">
        
        <x-slot:title>{{ __('ui.shift_history') }}</x-slot:title>
        <x-slot:subtitle>{{ __('ui.manage_shifts') }}</x-slot:subtitle>
    </x-ui.filter-bar>

    <x-ui.table>
        <x-slot:thead>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.cashier') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.start_time') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.starting_cash') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.ending_cash') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.net_profit') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.difference') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-center">{{ __('ui.action') }}</th>
        </x-slot:thead>

        @forelse($shifts as $shift)
        @php
            $diff = $shift->cash_difference ?? 0;
            $diffClass = $diff === 0 ? 'text-[#16a34a]' : ($diff > 0 ? 'text-[#ca8a04]' : 'text-[#ef4444]');
        @endphp
        <tr class="hover:bg-[#f5f5f5]/50 transition-colors group">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#f5e3bd] flex items-center justify-center text-[#c9a96e] text-xs font-bold border border-[#edcc94]/30">
                        {{ strtoupper(substr($shift->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-medium text-[#010101]">{{ $shift->user->name ?? '-' }}</span>
                        @if(!$shift->closed_at)
                            <span class="text-[10px] font-bold text-[#16a34a] uppercase tracking-widest">{{ __('ui.active') }}</span>
                        @endif
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col">
                    <span class="text-sm text-[#010101]">{{ $shift->opened_at->format('d M Y') }}</span>
                    <span class="text-xs text-[#6b7280]">{{ $shift->opened_at->format('H:i') }} — {{ $shift->closed_at ? $shift->closed_at->format('H:i') : '...' }}</span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#010101] text-right">
                Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#010101] text-right">
                @if ($shift->closing_cash)
                    Rp {{ number_format($shift->closing_cash, 0, ',', '.') }}
                @else
                    <span class="text-[#6b7280]">-</span>
                @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-[#010101] text-right">
                Rp {{ number_format($shift->orders->sum('net_amount'), 0, ',', '.') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right {{ $diffClass }}">
                @if ($shift->closed_at)
                    <div class="flex items-center justify-end gap-1">
                        {{ $diff > 0 ? '+' : '' }}Rp {{ number_format($diff, 0, ',', '.') }}
                        <iconify-icon icon="{{ $diff === 0 ? 'solar:check-circle-linear' : ($diff > 0 ? 'solar:arrow-up-circle-linear' : 'solar:arrow-down-circle-linear') }}" class="text-lg"></iconify-icon>
                    </div>
                @else
                    <span class="text-[#6b7280]">-</span>
                @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
                <button class="w-8 h-8 inline-flex items-center justify-center text-[#6b7280] hover:text-[#c9a96e] hover:bg-[#f5e3bd]/30 rounded-lg transition-all"
                    data-id="{{ $shift->id }}"
                    title="{{ __('ui.view_detail') }}"
                    onclick="viewShiftDetail(this)">
                    <iconify-icon icon="solar:eye-linear" stroke-width="1.5" class="text-xl"></iconify-icon>
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="px-6 py-12 text-center text-[#6b7280] text-sm">
                -
            </td>
        </tr>
        @endforelse
    </x-ui.table>

    @if ($shifts->hasPages())
    <div class="flex items-center justify-center py-4">
        {{ $shifts->links() }}
    </div>
    @endif
</section>

{{-- Shift detail modal --}}
<dialog id="modal-shift-detail" class="modal">
    <div class="modal-box w-full max-w-2xl bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-xl p-0 overflow-hidden">
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-[#e5e5e5]/50 flex items-center justify-between bg-[#f5f5f5]/30">
            <h3 class="text-lg font-semibold text-[#010101]">{{ __('ui.shift_detail') }} <span id="shift-detail-id" class="text-[#6b7280] font-normal text-sm ml-2 tracking-tight"></span></h3>
            <button type="button" onclick="AppModal.close('modal-shift-detail')" class="text-[#6b7280] hover:text-[#010101] transition-colors p-1">
                <iconify-icon icon="solar:close-circle-linear" stroke-width="1.5" class="text-2xl"></iconify-icon>
            </button>
        </div>

        {{-- Loading State --}}
        <div id="shift-detail-loading" class="p-12 text-center">
            <div class="inline-block w-8 h-8 border-2 border-[#e5e5e5] border-t-[#c9a96e] rounded-full animate-spin"></div>
            <p class="text-sm text-[#6b7280] mt-3 font-medium">{{ __('ui.loading_order_details') }}...</p>
        </div>

        {{-- Modal Body --}}
        <div id="shift-detail-content" class="p-6 hidden">
            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="bg-[#f5f5f5]/50 p-5 rounded-2xl border border-[#e5e5e5]/30">
                    <p class="text-[10px] uppercase font-bold text-[#6b7280] tracking-wider mb-2">Total {{ __('ui.transactions') }}</p>
                    <p class="text-3xl font-bold text-[#010101]" id="shift-tx-count"></p>
                </div>
                <div class="bg-[#f5e3bd]/20 p-5 rounded-2xl border border-[#edcc94]/20">
                    <p class="text-[10px] uppercase font-bold text-[#c9a96e] tracking-wider mb-2">Total {{ __('ui.revenue') }}</p>
                    <p class="text-3xl font-bold text-[#010101]" id="shift-revenue"></p>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <h4 class="text-xs font-bold text-[#010101] uppercase tracking-widest mb-4 flex items-center gap-2">
                        <iconify-icon icon="solar:wallet-money-linear" class="text-lg"></iconify-icon>
                        {{ __('ui.payment_method') }}
                    </h4>
                    <div class="rounded-xl border border-[#e5e5e5] overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#f5f5f5]/50 border-b border-[#e5e5e5]">
                                    <th class="px-4 py-2.5 text-[10px] font-bold text-[#6b7280] uppercase tracking-wider">{{ __('ui.method') }}</th>
                                    <th class="px-4 py-2.5 text-[10px] font-bold text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.total') }}</th>
                                </tr>
                            </thead>
                            <tbody id="shift-payments-body" class="divide-y divide-[#e5e5e5] text-sm"></tbody>
                        </table>
                    </div>
                </div>

                <div class="pt-6 border-t border-[#e5e5e5]/50">
                    <h4 class="text-xs font-bold text-[#010101] uppercase tracking-widest mb-4 flex items-center gap-2">
                        <iconify-icon icon="solar:safe-2-linear" class="text-lg"></iconify-icon>
                        {{ __('ui.cash_reconciliation') }}
                    </h4>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="p-4 bg-[#f5f5f5]/30 rounded-xl border border-[#e5e5e5]/30">
                            <p class="text-[10px] text-[#6b7280] uppercase font-bold tracking-wider mb-1">{{ __('ui.starting_cash') }}</p>
                            <p class="text-sm font-semibold text-[#010101]" id="shift-opening-cash"></p>
                        </div>
                        <div class="p-4 bg-[#f5f5f5]/30 rounded-xl border border-[#e5e5e5]/30">
                            <p class="text-[10px] text-[#6b7280] uppercase font-bold tracking-wider mb-1">{{ __('ui.ending_cash') }}</p>
                            <p class="text-sm font-semibold text-[#010101]" id="shift-closing-cash"></p>
                        </div>
                        <div class="p-4 bg-[#f5f5f5]/30 rounded-xl border border-[#e5e5e5]/30">
                            <p class="text-[10px] text-[#6b7280] uppercase font-bold tracking-wider mb-1">{{ __('ui.difference') }}</p>
                            <p class="text-sm font-bold" id="shift-difference"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop bg-[#010101]/20 backdrop-blur-[2px]">
        <button class="cursor-default outline-none text-transparent">close</button>
    </form>
</dialog>

@endsection

@push('scripts')
<script>
window.viewShiftDetail = function(btn) {
    var shiftId = $(btn).data('id');
    $('#shift-detail-id').text('#' + shiftId);
    $('#shift-detail-loading').removeClass('hidden');
    $('#shift-detail-content').addClass('hidden');

    AppModal.open('modal-shift-detail');

    AppAjax.get('/shifts/' + shiftId, function(data) {
        $('#shift-detail-loading').addClass('hidden');
        $('#shift-detail-content').removeClass('hidden');

        $('#shift-tx-count').text(data.total_transactions);
        $('#shift-revenue').text('Rp ' + data.total_revenue.toLocaleString('id-ID'));
        $('#shift-opening-cash').text('Rp ' + data.shift.opening_cash.toLocaleString('id-ID'));
        $('#shift-closing-cash').text(data.shift.closing_cash ? 'Rp ' + data.shift.closing_cash.toLocaleString('id-ID') : '-');

        var diff = data.shift.cash_difference || 0;
        var diffClass = diff === 0 ? 'text-[#16a34a]' : (diff > 0 ? 'text-[#ca8a04]' : 'text-[#ef4444]');
        $('#shift-difference').html('<span class="' + diffClass + '">' + (diff > 0 ? '+' : '') + 'Rp ' + diff.toLocaleString('id-ID') + '</span>');

        var paymentsHtml = '';
        for (var method in data.payments_by_method) {
            paymentsHtml += '<tr><td class="px-4 py-2.5 font-medium text-[#010101] uppercase">' + method + '</td><td class="px-4 py-2.5 text-right font-semibold text-[#010101]">Rp ' + data.payments_by_method[method].toLocaleString('id-ID') + '</td></tr>';
        }
        if (!paymentsHtml) paymentsHtml = '<tr><td colspan="2" class="px-4 py-8 text-center text-[#6b7280] italic">No payments recorded</td></tr>';
        $('#shift-payments-body').html(paymentsHtml);
    });
};
</script>
@endpush
