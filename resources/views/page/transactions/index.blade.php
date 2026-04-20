@extends('layouts.app')

@section('title', __('ui.transactions'))

@section('content')

<section id="orders" class="space-y-6">
    <x-ui.filter-bar 
        :action="route('transactions.index')" 
        :search-placeholder="__('ui.search')"
        :search-value="request('search')">
        
        <x-slot:title>{{ __('ui.recent_orders') }}</x-slot:title>
        <x-slot:subtitle>{{ __('ui.live_transaction_feed') }}</x-slot:subtitle>

        {{-- Date Filter --}}
        <div class="relative flex-1 min-w-[200px] sm:flex-none">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#6b7280] flex items-center pointer-events-none">
                <iconify-icon icon="solar:calendar-date-linear" class="text-xl"></iconify-icon>
            </span>
            <input type="date" name="date" value="{{ request('date') }}"
                onchange="this.form.submit()"
                class="w-full bg-[#fcfcfc] border border-[#e5e5e5] rounded-xl pl-12 pr-4 py-2 text-sm text-[#010101] font-medium focus:outline-none focus:border-[#c9a96e] transition-all cursor-pointer shadow-sm">
        </div>

        {{-- Export Button --}}
        <button type="button" 
            onclick="AppModal.open('modal-export')"
            class="flex items-center gap-2 px-5 py-2.5 bg-[#010101] hover:bg-[#262626] text-white rounded-xl text-sm font-bold shadow-lg shadow-[#010101]/10 active:scale-[0.98]">
            <iconify-icon icon="solar:export-linear" class="text-lg"></iconify-icon>
            <span>{{ __('ui.export_file') }}</span>
        </button>
    </x-ui.filter-bar>

    <x-ui.table>
        <x-slot:thead>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.order_id') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.time') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.cashier') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.total') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.profit') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-center">{{ __('ui.action') }}</th>
        </x-slot:thead>

        @forelse($orders as $order)
        <tr class="hover:bg-[#f5f5f5]/30 transition-colors group">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#010101]">#ORD-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#6b7280]">{{ $order->created_at->format('H:i') }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 rounded-full bg-[#f5e3bd] flex items-center justify-center text-[#c9a96e] text-[10px] font-medium">
                        {{ strtoupper(substr($order->cashier->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="text-sm text-[#010101]">{{ $order->cashier->name ?? '-' }}</span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#010101] text-right">{{ $order->total_amount_formatted }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#c9a96e] text-right">
                Rp {{ number_format($order->profit, 0, ',', '.') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
                <button class="text-[#6b7280] hover:text-[#010101] p-1.5 rounded-xl hover:bg-white border border-transparent hover:border-[#e5e5e5] transition-all"
                    data-id="{{ $order->id }}"
                    onclick="AppTransactions.viewDetail(this)">
                    <iconify-icon icon="solar:eye-linear" stroke-width="1.5" class="text-lg"></iconify-icon>
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="px-6 py-12 text-center text-[#6b7280] text-sm">
                {{ __('ui.no_transactions') }}
                @if(request()->hasAny(['date', 'search'])) {{ __('ui.on_this_filter') }} @endif
            </td>
        </tr>
        @endforelse

        @if($orders->hasPages())
            <x-slot:pagination>
                {{ $orders->links() }}
            </x-slot:pagination>
        @endif
    </x-ui.table>
</section>

{{-- Modal: Transaction Detail --}}
<dialog id="modal-transaction-detail" class="modal">
    <div class="modal-box w-full max-w-lg bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-[#010101]">{{ __('ui.view_detail') }} <span id="tx-detail-id" class="text-[#6b7280] font-normal text-sm"></span></h3>
            <button type="button" onclick="AppModal.close('modal-transaction-detail')" class="text-[#6b7280] hover:text-[#010101]">
                <iconify-icon icon="solar:close-circle-linear" stroke-width="1.5" class="text-xl"></iconify-icon>
            </button>
        </div>

        <div id="tx-detail-loading" class="text-center py-12">
            <div class="inline-block w-8 h-8 border-2 border-[#e5e5e5] border-t-[#c9a96e] rounded-full animate-spin"></div>
            <p class="text-sm text-[#6b7280] mt-3">{{ __('ui.loading') }}...</p>
        </div>

        <div id="tx-detail-content" class="hidden">
            <div class="grid grid-cols-2 gap-3 text-sm mb-4 p-4 bg-[#f5f5f5]/50 rounded-xl">
                <span class="text-[#6b7280]">{{ __('ui.cashier') }}</span>
                <span id="tx-detail-cashier" class="font-medium text-[#010101]"></span>
                <span class="text-[#6b7280]">{{ __('ui.time') }}</span>
                <span id="tx-detail-date" class="text-[#010101]"></span>
                <span class="text-[#6b7280]">{{ __('ui.notes') }}</span>
                <span id="tx-detail-notes" class="text-[#6b7280]">-</span>
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

            <div class="border-t border-[#e5e5e5] pt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-[#6b7280]">{{ __('ui.total') }}</span>
                    <span class="font-semibold text-[#010101]" id="tx-detail-total"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#6b7280]">{{ __('ui.profit') }}</span>
                    <span class="font-medium text-[#c9a96e]" id="tx-detail-profit"></span>
                </div>
            </div>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

@endsection

@push('modals')
{{-- Modal: Export Options --}}
<dialog id="modal-export" class="modal">
    <div class="modal-box w-full max-w-xl bg-white rounded-3xl border border-[#e5e5e5] shadow-2xl p-0 overflow-hidden">
        <div class="p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-bold text-[#010101]">{{ __('ui.export_file') }}</h3>
                    <p class="text-sm text-[#6b7280] mt-1">{{ __('ui.export_preferences') }}</p>
                </div>
                <button type="button" onclick="AppModal.close('modal-export')" class="w-10 h-10 flex items-center justify-center rounded-full bg-[#f5f5f5] text-[#6b7280] hover:text-[#ef4444] transition-colors">
                    <iconify-icon icon="solar:close-circle-linear" class="text-2xl"></iconify-icon>
                </button>
            </div>

            <form action="{{ route('transactions.export') }}" method="GET" class="space-y-8">
                <input type="hidden" name="date" value="{{ request('date') }}">
                
                {{-- Format Selection --}}
                <div class="space-y-4">
                    <label class="text-sm font-bold text-[#010101] uppercase tracking-wider flex items-center gap-2">
                        <iconify-icon icon="solar:file-text-linear" class="text-[#c9a96e] text-lg"></iconify-icon>
                        {{ __('ui.choose_format') }}
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach([
                            'EXCEL' => ['icon' => 'solar:document-bold-duotone', 'color' => 'text-[#22c55e]'],
                            'CSV' => ['icon' => 'solar:document-add-bold-duotone', 'color' => 'text-[#0ea5e9]'],
                            'PDF' => ['icon' => 'solar:document-text-bold-duotone', 'color' => 'text-[#ef4444]'],
                            'TXT' => ['icon' => 'solar:notes-bold-duotone', 'color' => 'text-[#64748b]']
                        ] as $format => $data)
                        <label class="cursor-pointer group">
                            <input type="radio" name="format" value="{{ $format }}" class="peer hidden" {{ $loop->first ? 'checked' : '' }}>
                            <div class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 border-[#f5f5f5] peer-checked:border-[#c9a96e] peer-checked:bg-[#f5e3bd]/10 transition-all hover:bg-[#f5f5f5] h-full">
                                <iconify-icon icon="{{ $data['icon'] }}" class="text-4xl mb-2 {{ $data['color'] }} group-hover:scale-110 transition-transform duration-300"></iconify-icon>
                                <span class="text-xs font-bold text-[#6b7280] peer-checked:text-[#010101] tracking-wide">{{ $format }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                    <div class="grid grid-cols-1 gap-3">
                        @foreach([
                            ['id' => 'summary', 'name' => 'Summary Report', 'desc' => 'Total sales, profit, and tax overview.', 'icon' => 'solar:chart-square-linear'],
                            ['id' => 'detailed', 'name' => 'Detailed Transaction', 'desc' => 'Full item breakdown for each order.', 'icon' => 'solar:list-arrow-down-linear'],
                            ['id' => 'accounting', 'name' => 'Accounting Ledger', 'desc' => 'Standardized format for financial software.', 'icon' => 'solar:wallet-linear']
                        ] as $template)
                        <label class="group cursor-pointer flex items-center gap-4 p-4 rounded-2xl border-2 border-[#f5f5f5] transition-all hover:bg-[#f5f5f5] has-[:checked]:border-[#c9a96e] has-[:checked]:bg-[#f5e3bd]/10">
                            <input type="radio" name="template" value="{{ $template['id'] }}" class="hidden" {{ $loop->first ? 'checked' : '' }}>
                            <div class="w-12 h-12 rounded-xl bg-white border border-[#e5e5e5] flex items-center justify-center text-[#6b7280] group-has-[:checked]:text-[#c9a96e] transition-colors">
                                <iconify-icon icon="{{ $template['icon'] }}" class="text-2xl"></iconify-icon>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-[#010101]">{{ $template['name'] }}</h4>
                                <p class="text-xs text-[#6b7280] mt-0.5">{{ $template['desc'] }}</p>
                            </div>
                            <div class="w-6 h-6 rounded-full border-2 border-[#e5e5e5] flex items-center justify-center transition-all group-has-[:checked]:border-[#c9a96e] group-has-[:checked]:bg-[#c9a96e]">
                                <iconify-icon icon="solar:check-read-linear" class="text-white text-xs opacity-0 group-has-[:checked]:opacity-100 transition-opacity"></iconify-icon>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                --}}

                <div class="flex gap-3 pt-4">
                    <button type="button" class="flex-1 py-4 px-6 bg-[#f5f5f5] hover:bg-[#e5e5e5] text-[#010101] rounded-2xl text-sm font-bold transition-all" onclick="AppModal.close('modal-export')">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-4 px-6 bg-[#010101] hover:bg-[#262626] text-white rounded-2xl text-sm font-bold transition-all shadow-lg shadow-[#010101]/20 active:scale-[0.98]" onclick="setTimeout(() => AppModal.close('modal-export'), 100)">
                        Download File
                    </button>
                </div>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop bg-[#010101]/30">
        <button class="cursor-default outline-none text-transparent">close</button>
    </form>
</dialog>
@endpush

@push('scripts')
<script src="{{ asset('js/modules/transaction.js') }}"></script>
@endpush
