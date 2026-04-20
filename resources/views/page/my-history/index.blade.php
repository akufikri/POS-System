@extends('layouts.app')

@section('title', __('ui.my_history'))

@section('content')

<section id="my-history" class="space-y-6">
    <div>
        <h2 class="text-xl font-medium tracking-tight text-[#010101]">{{ __('ui.my_transaction_history') }}</h2>
        <p class="text-sm text-[#6b7280] mt-1">{{ __('ui.view_all_processed_transactions') }}</p>
    </div>

    {{-- Today quick stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-[#ffffff] p-6 rounded-2xl border border-[#e5e5e5] shadow-sm flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-[#6b7280]">{{ __('ui.transactions_today') }}</span>
                <div class="w-8 h-8 rounded-lg bg-[#f5e3bd]/30 flex items-center justify-center text-[#c9a96e]">
                    <iconify-icon icon="solar:ticket-sale-linear" stroke-width="1.5"></iconify-icon>
                </div>
            </div>
            <div>
                <div class="text-3xl font-medium tracking-tight text-[#010101]">{{ $todayStats->tx_count ?? 0 }}</div>
            </div>
        </div>

        <div class="bg-[#ffffff] p-6 rounded-2xl border border-[#e5e5e5] shadow-sm flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-[#6b7280]">{{ __('ui.revenue_today') }}</span>
                <div class="w-8 h-8 rounded-lg bg-[#f5e3bd]/30 flex items-center justify-center text-[#c9a96e]">
                    <iconify-icon icon="solar:wallet-money-linear" stroke-width="1.5"></iconify-icon>
                </div>
            </div>
            <div>
                <div class="text-3xl font-medium tracking-tight text-[#010101]">
                    Rp {{ number_format($todayStats->revenue ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm p-4">
        <form method="GET" action="{{ route('my-history') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.date') }}</label>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-2.5 text-sm text-[#010101] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-[#edcc94] hover:bg-[#c9a96e] text-[#010101] rounded-xl text-sm font-medium transition-colors shadow-sm">
                    <iconify-icon icon="solar:filter-linear" stroke-width="1.5"></iconify-icon>
                    {{ __('ui.filter') }}
                </button>
                @if(request()->filled('date'))
                <a href="{{ route('my-history') }}" class="flex items-center gap-2 px-4 py-2.5 bg-[#f5f5f5] hover:bg-[#e5e5e5] text-[#010101] rounded-xl text-sm font-medium transition-colors border border-[#e5e5e5]">
                    <iconify-icon icon="solar:restart-linear" stroke-width="1.5"></iconify-icon>
                    {{ __('ui.reset') }}
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Transactions --}}
    <div class="bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f5f5f5]/50 border-b border-[#e5e5e5]">
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">#ID</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.time') }}</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.items') }}</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.total') }}</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-center">{{ __('ui.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e5e5]">
                    @forelse($orders as $order)
                    <tr class="hover:bg-[#f5f5f5]/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#010101]">#ORD-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#6b7280]">{{ $order->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#6b7280]">{{ $order->items->count() }} item(s)</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#010101] text-right">{{ $order->total_amount_formatted }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <button class="text-[#6b7280] hover:text-[#010101] p-1 rounded-md hover:bg-[#f5f5f5]"
                                data-id="{{ $order->id }}"
                                onclick="AppTransactions.viewDetail(this)">
                                <iconify-icon icon="solar:eye-linear" stroke-width="1.5" class="text-lg"></iconify-icon>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-[#6b7280] text-sm">
                            -
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-[#e5e5e5] flex items-center justify-center">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</section>

{{-- Reuse transaction detail modal --}}
<dialog id="modal-transaction-detail" class="modal">
    <div class="modal-box w-full max-w-lg bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-[#010101]">{{ __('ui.order_detail') }} <span id="tx-detail-id" class="text-[#6b7280] font-normal text-sm"></span></h3>
            <button type="button" onclick="AppModal.close('modal-transaction-detail')" class="text-[#6b7280] hover:text-[#010101]">
                <iconify-icon icon="solar:close-circle-linear" stroke-width="1.5" class="text-xl"></iconify-icon>
            </button>
        </div>

        <div id="tx-detail-loading" class="text-center py-12">
            <div class="inline-block w-8 h-8 border-2 border-[#e5e5e5] border-t-[#c9a96e] rounded-full animate-spin"></div>
            <p class="text-sm text-[#6b7280] mt-3">{{ __('ui.loading_order_details') }}...</p>
        </div>

        <div id="tx-detail-content" class="hidden">
            <div class="grid grid-cols-2 gap-3 mb-4 p-4 bg-[#f5f5f5]/50 rounded-xl">
                <span class="text-[#6b7280] text-xs">{{ __('ui.time') }}</span>
                <span id="tx-detail-date" class="text-[#010101] text-sm"></span>
                <span class="text-[#6b7280] text-xs">{{ __('ui.notes') }}</span>
                <span id="tx-detail-notes" class="text-[#6b7280] text-sm">-</span>
            </div>

            <table class="w-full text-left border-collapse mb-4">
                <thead>
                    <tr class="border-b border-[#e5e5e5]">
                        <th class="px-3 py-2 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.product') }}</th>
                        <th class="px-3 py-2 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-center">{{ __('ui.qty') }}</th>
                        <th class="px-3 py-2 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.price') }}</th>
                        <th class="px-3 py-2 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.subtotal') }}</th>
                    </tr>
                </thead>
                <tbody id="tx-detail-items" class="divide-y divide-[#e5e5e5]"></tbody>
            </table>

            <div class="border-t border-[#e5e5e5] pt-4">
                <div class="flex justify-between text-sm">
                    <span class="text-[#6b7280]">{{ __('ui.total') }}</span>
                    <span class="font-semibold text-[#010101]" id="tx-detail-total"></span>
                </div>
            </div>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

@push('scripts')
<script src="{{ asset('js/modules/transaction.js') }}"></script>
@endpush
@endsection
