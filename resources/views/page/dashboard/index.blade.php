@extends('layouts.app')

@section('title', __('ui.dashboard'))

@php
    $todayDate = now()->format('Y-m-d');
    $yesterdayDate = now()->subDay()->format('Y-m-d');
@endphp

@section('content')

    {{-- ═══ OWNER VIEW ════════════════════════════════════════════════════════ --}}
    @if($isOwner)

    <section id="dashboard" class="space-y-8">
        {{-- Header --}}
        <div class="flex items-end justify-between">
            <div>
                <h1 class="text-2xl font-medium tracking-tight text-[#010101]">{{ __('ui.todays_overview') }}</h1>
                <p class="text-sm text-[#6b7280] mt-1">{{ __('ui.realtime_performance_for') }} {{ now()->format('M j, Y') }}</p>
            </div>
            <div class="relative hidden sm:block">
                <button id="date-filter-btn" class="flex items-center gap-2 px-4 py-2 bg-white border border-[#e5e5e5] rounded-xl text-sm font-medium text-[#010101] hover:bg-[#f5f5f5] transition-colors shadow-sm">
                    <iconify-icon icon="solar:calendar-linear" stroke-width="1.5"></iconify-icon>
                    <span id="current-date-label">{{ __('ui.today') }}</span>
                    <iconify-icon icon="solar:alt-arrow-down-linear" stroke-width="1.5" class="text-[#6b7280]"></iconify-icon>
                </button>
                <div id="date-filter-dropdown" class="absolute right-0 top-full mt-2 w-48 bg-white border border-[#e5e5e5] rounded-xl shadow-lg hidden z-10">
                    <div class="py-1">
                        <a href="/dashboard?date={{ $todayDate }}" class="block px-4 py-2 text-sm text-[#010101] hover:bg-[#f5f5f5]">{{ __('ui.today') }}</a>
                        <a href="/dashboard?date={{ $yesterdayDate }}" class="block px-4 py-2 text-sm text-[#010101] hover:bg-[#f5f5f5]">{{ __('ui.yesterday') }}</a>
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-[#010101] hover:bg-[#f5f5f5]">{{ __('ui.all_time') }}</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            {{-- Revenue --}}
            <div class="bg-[#ffffff] p-6 rounded-2xl border border-[#e5e5e5] shadow-sm flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-[#6b7280]">{{ __('ui.total_revenue') }}</span>
                    <div class="w-8 h-8 rounded-lg bg-[#f5e3bd]/30 flex items-center justify-center text-[#c9a96e]">
                        <iconify-icon icon="solar:wallet-money-linear" stroke-width="1.5"></iconify-icon>
                    </div>
                </div>
                <div>
                    <div class="text-3xl font-medium tracking-tight text-[#010101]" id="stat-today-revenue">
                        Rp {{ number_format($summary['today_revenue'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Cost --}}
            <div class="bg-[#ffffff] p-6 rounded-2xl border border-[#e5e5e5] shadow-sm flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-[#6b7280]">{{ __('ui.total_cost') }}</span>
                    <div class="w-8 h-8 rounded-lg bg-[#f5f5f5] flex items-center justify-center text-[#6b7280]">
                        <iconify-icon icon="solar:tag-price-linear" stroke-width="1.5"></iconify-icon>
                    </div>
                </div>
                <div>
                    <div class="text-3xl font-medium tracking-tight text-[#010101]" id="stat-today-cost">
                        Rp {{ number_format($summary['today_revenue'] - $summary['today_profit'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Profit --}}
            <div class="bg-[#ffffff] p-6 rounded-2xl border border-[#e5e5e5] shadow-sm flex flex-col gap-4 relative overflow-hidden">
                {{-- Decorative accent --}}
                <div class="absolute top-0 right-0 w-24 h-24 bg-[#edcc94] opacity-5 rounded-bl-full pointer-events-none"></div>
                <div class="flex items-center justify-between relative z-10">
                    <span class="text-sm font-medium text-[#6b7280]">{{ __('ui.net_profit') }}</span>
                    <div class="w-8 h-8 rounded-lg bg-[#edcc94]/20 flex items-center justify-center text-[#c9a96e]">
                        <iconify-icon icon="solar:graph-up-linear" stroke-width="1.5"></iconify-icon>
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="text-3xl font-medium tracking-tight text-[#c9a96e]" id="stat-today-profit">
                        Rp {{ number_format($summary['today_profit'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Orders --}}
            <div class="bg-[#ffffff] p-6 rounded-2xl border border-[#e5e5e5] shadow-sm flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-[#6b7280]">{{ __('ui.total_orders') }}</span>
                    <div class="w-8 h-8 rounded-lg bg-[#f5f5f5] flex items-center justify-center text-[#6b7280]">
                        <iconify-icon icon="solar:ticket-sale-linear" stroke-width="1.5"></iconify-icon>
                    </div>
                </div>
                <div>
                    <div class="text-3xl font-medium tracking-tight text-[#010101]" id="stat-today-tx">
                        {{ $summary['today_transactions'] }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Secondary Dashboard Modules --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Top Selling Items --}}
            <div class="bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-[#e5e5e5] flex justify-between items-center">
                    <h3 class="text-base font-medium text-[#010101]">{{ __('ui.top_selling_items') }}</h3>
                    <a href="#" class="text-xs font-medium text-[#c9a96e] hover:text-[#010101] transition-colors">{{ __('ui.view_all') }}</a>
                </div>
                <div class="p-6 flex flex-col gap-5">
                    {{-- Mock data - replace with real data --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-[#f5e3bd]/30 flex items-center justify-center text-[#c9a96e]">
                                <iconify-icon icon="solar:cup-linear" stroke-width="1.5" class="text-xl"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-[#010101]">{{ __('ui.best_selling_item') }}</h4>
                                <p class="text-xs text-[#6b7280] mt-0.5">{{ __('ui.top_category') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-medium text-[#010101]" id="top-selling-count">0</span>
                            <p class="text-xs text-[#6b7280] mt-0.5">{{ __('ui.sold') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Staff Performance --}}
            <div class="bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-[#e5e5e5]">
                    <h3 class="text-base font-medium text-[#010101]">{{ __('ui.staff_performance') }}</h3>
                </div>
                <div class="p-6 flex flex-col gap-5">
                    @forelse($summary['kasir_performance'] as $kasir)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-[#f5e3bd] flex items-center justify-center text-[#c9a96e] font-medium text-sm">
                                {{ strtoupper(substr($kasir['name'], 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-[#010101]">{{ $kasir['name'] }}</h4>
                                <p class="text-xs text-[#6b7280] mt-0.5">{{ __('ui.cashier') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <span class="text-sm font-medium text-[#010101]">{{ $kasir['transaction_count'] }}</span>
                                <p class="text-xs text-[#6b7280] mt-0.5">{{ __('ui.trans') }}.</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-[#6b7280] text-sm py-4">{{ __('ui.no_performance_data') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Chart: 7-day revenue + profit --}}
        <div class="bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-[#e5e5e5]">
                <h3 class="text-base font-medium text-[#010101]">{{ __('ui.revenue_profit_7days') }}</h3>
            </div>
            <div class="p-6">
                <div class="relative h-64">
                    <canvas id="revenue-chart"></canvas>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ CASHIER VIEW ══════════════════════════════════════════════════════ --}}
    @else

    <section id="pos-container" class="h-full flex flex-col lg:flex-row gap-6">
        
        {{-- ═══ LEFT SIDE: PRODUCTS & SEARCH ══════════════════════════════════ --}}
        <div class="flex-1 flex flex-col min-w-0">
            
            {{-- Search & Categories --}}
            <div class="bg-white p-4 rounded-2xl border border-[#e5e5e5] shadow-sm mb-6 space-y-4">
                <div class="relative">
                    <iconify-icon icon="solar:magnifer-linear" class="absolute left-4 top-1/2 -translate-y-1/2 text-[#6b7280] text-xl"></iconify-icon>
                    <input type="text" id="product-search" placeholder="{{ __('ui.search') }} {{ __('ui.products') }}..." 
                        class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl pl-12 pr-4 py-3 text-sm focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-all">
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide no-scrollbar">
                    <button class="category-tab whitespace-nowrap px-4 py-2 rounded-xl text-sm font-medium transition-all bg-[#edcc94] text-[#010101]" data-id="">
                        {{ __('ui.all_items') }}
                    </button>
                    @foreach($categories as $category)
                    <button class="category-tab whitespace-nowrap px-4 py-2 rounded-xl text-sm font-medium transition-all bg-white text-[#6b7280] border border-[#e5e5e5] hover:border-[#edcc94] hover:text-[#c9a96e]" data-id="{{ $category->id }}">
                        {{ $category->name }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Product Grid --}}
            <div id="product-grid" class="flex-1 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 pb-6">
                @foreach($products as $product)
                <div class="product-card group bg-white border border-[#e5e5e5] rounded-2xl p-3 shadow-sm hover:border-[#edcc94] hover:shadow-md transition-all cursor-pointer active:scale-[0.98] {{ $product->stock <= 0 ? 'opacity-60 cursor-not-allowed grayscale-[0.5]' : '' }}" 
                    data-id="{{ $product->id }}" 
                    data-name="{{ $product->name }}" 
                    data-category="{{ $product->category_id }}"
                    data-stock="{{ $product->stock }}"
                    onclick="{{ $product->stock > 0 ? "AppPOS.addToCart($product->id, '".addslashes($product->name)."', $product->price)" : '' }}">
                    
                    <div class="aspect-square bg-[#f5f5f5] rounded-xl mb-3 overflow-hidden relative">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-[#c9a96e]/20">
                                <iconify-icon icon="solar:gallery-linear" class="text-4xl"></iconify-icon>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-[#010101]/0 group-hover:bg-[#010101]/5 transition-colors"></div>
                        @if($product->stock <= 0)
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="bg-[#ef4444] text-white text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider shadow-sm">{{ __('ui.out_of_stock') ?? 'Habis' }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <h3 class="text-sm font-medium text-[#010101] truncate">{{ $product->name }}</h3>
                    <div class="flex items-center justify-between mt-1">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-[#c9a96e]">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-[#6b7280] font-medium">{{ __('ui.stock') }}: {{ $product->stock }}</span>
                        </div>
                        @if($product->stock > 0)
                        <div class="w-6 h-6 rounded-lg bg-[#f5e3bd]/50 flex items-center justify-center text-[#c9a96e] opacity-0 group-hover:opacity-100 transition-opacity">
                            <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ═══ RIGHT SIDE: CART ════════════════════════════════════════════ --}}
        <div class="w-full lg:w-[380px] flex flex-col h-full shrink-0">
            
            {{-- Cashier Summary (Collapsible or Mini) --}}
            <div class="bg-white border border-[#e5e5e5] rounded-2xl shadow-sm overflow-hidden mb-6">
                <div class="p-4 border-b border-[#e5e5e5] flex items-center justify-between">
                    <h3 class="text-sm font-bold text-[#010101] uppercase tracking-wider">{{ __('ui.my_activity_today') }}</h3>
                    <iconify-icon icon="solar:chart-square-linear" class="text-[#c9a96e] text-lg"></iconify-icon>
                </div>
                <div class="grid grid-cols-2 divide-x divide-[#e5e5e5]">
                    <div class="p-4">
                        <p class="text-[10px] text-[#6b7280] uppercase font-bold">{{ __('ui.revenue') }}</p>
                        <p class="text-sm font-bold text-[#010101]">Rp {{ number_format($summary['today_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] text-[#6b7280] uppercase font-bold">{{ __('ui.orders') }}</p>
                        <p class="text-sm font-bold text-[#010101]">{{ $summary['today_transactions'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Cart Section --}}
            <div class="flex-1 bg-white border border-[#e5e5e5] rounded-2xl shadow-lg flex flex-col overflow-hidden">
                <div class="p-4 border-b border-[#e5e5e5] flex items-center justify-between bg-[#f5f5f5]/30">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="solar:cart-large-minimalistic-linear" class="text-xl text-[#010101]"></iconify-icon>
                        <h3 class="font-bold text-[#010101] uppercase tracking-wider">{{ __('ui.cart') }}</h3>
                    </div>
                    <button onclick="window.location.reload()" class="text-[#6b7280] hover:text-[#010101]">
                        <iconify-icon icon="solar:restart-linear"></iconify-icon>
                    </button>
                </div>

                {{-- Cart Items --}}
                <div id="cart-items" class="flex-1 overflow-y-auto p-4 space-y-2">
                    {{-- Empty state --}}
                    <div class="h-full flex flex-col items-center justify-center text-center opacity-40">
                        <iconify-icon icon="solar:bag-linear" class="text-5xl mb-2"></iconify-icon>
                        <p class="text-sm">{{ __('ui.empty_cart') }}</p>
                    </div>
                </div>

                {{-- Cart Footer --}}
                <div class="p-4 bg-[#f5f5f5]/50 border-t border-[#e5e5e5] space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#6b7280]">{{ __('ui.total') }}</span>
                        <span id="cart-total" class="text-xl font-bold text-[#010101]">Rp 0</span>
                    </div>
                    
                    <button id="btn-checkout" class="w-full py-4 bg-[#edcc94] hover:bg-[#c9a96e] text-[#010101] rounded-xl font-bold transition-all shadow-lg shadow-[#edcc94]/20 flex items-center justify-center gap-2 active:scale-[0.98]">
                        <iconify-icon icon="solar:wad-of-money-linear" class="text-xl"></iconify-icon>
                        {{ __('ui.checkout') }}
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Modal: Checkout --}}
    <dialog id="modal-checkout" class="modal">
        <div class="modal-box max-w-md bg-white rounded-2xl border border-[#e5e5e5] shadow-2xl p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-[#e5e5e5]/50 flex items-center justify-between bg-[#f5f5f5]/30">
                <h3 class="text-lg font-bold text-[#010101] uppercase tracking-wider">{{ __('ui.payment') }}</h3>
                <button type="button" onclick="AppModal.close('modal-checkout')" class="text-[#6b7280] hover:text-[#010101]">
                    <iconify-icon icon="solar:close-circle-linear" class="text-2xl"></iconify-icon>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                {{-- Summary --}}
                <div class="flex items-center justify-between p-4 bg-[#f5e3bd]/20 rounded-xl border border-[#edcc94]/20">
                    <span class="text-[#010101]">{{ __('ui.total') }}</span>
                    <span id="checkout-total-display" class="text-2xl font-black text-[#c9a96e]">Rp 0</span>
                </div>

                {{-- Payment Input --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#6b7280] uppercase tracking-widest">{{ __('ui.payment') }} (Rp)</label>
                    <input type="text" id="payment-amount" placeholder="0" 
                        class="w-full bg-[#f5f5f5] border border-[#e5e5e5] rounded-xl px-4 py-4 text-2xl font-bold text-[#010101] focus:outline-none focus:border-[#edcc94] transition-all input-currency"
                        inputmode="numeric" oninput="AppPOS.calculateChange()">
                </div>

                {{-- Change display --}}
                <div class="flex items-center justify-between p-4 bg-[#f5f5f5] rounded-xl">
                    <span class="text-sm font-medium text-[#6b7280]">{{ __('ui.change') }}</span>
                    <span id="change-amount" class="text-lg font-bold text-[#ef4444]">Rp 0</span>
                </div>

                {{-- Notes --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#6b7280] uppercase tracking-widest">{{ __('ui.notes') }}</label>
                    <textarea id="order-notes" rows="2" placeholder="{{ __('ui.order_notes') }}"
                        class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#c9a96e] transition-all resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" class="flex-1 py-4 bg-[#f5f5f5] text-[#010101] rounded-xl font-bold transition-all" onclick="AppModal.close('modal-checkout')">
                        {{ __('ui.cancel') }}
                    </button>
                    <button type="button" id="btn-submit-order" onclick="AppPOS.submitOrder()" 
                        class="flex-1 py-4 bg-[#edcc94] hover:bg-[#c9a96e] text-[#010101] rounded-xl font-bold transition-all shadow-lg shadow-[#edcc94]/20 opacity-50 cursor-not-allowed" disabled>
                        {{ __('ui.pay') }}
                    </button>
                </div>
            </div>
        </div>
    </dialog>

    @endif

@endsection

{{-- Pass chart data + role flag to JS --}}
<script>
    window.CHART_DATA   = @json($chartData);
    window.IS_OWNER     = @json($isOwner);
    window.I18N = {
        revenue: "{{ __('ui.revenue') }}",
        profit: "{{ __('ui.profit') }}",
        transactions: "{{ __('ui.transactions') }}",
        trans: "{{ __('ui.trans') }}",
        no_transactions_today: "{{ __('ui.no_performance_data') }}",
        updated: "{{ __('ui.updated') }}",
        jt: "{{ __('ui.million_short') }}",
        rb: "{{ __('ui.thousand_short') }}",
    };
</script>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/modules/dashboard.js') }}"></script>
<script src="{{ asset('js/modules/pos.js') }}"></script>
<script>
$(document).ready(function() {
    // Date filter dropdown toggle
    $('#date-filter-btn').on('click', function(e) {
        e.stopPropagation();
        $('#date-filter-dropdown').toggleClass('hidden');
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#date-filter-btn, #date-filter-dropdown').length) {
            $('#date-filter-dropdown').addClass('hidden');
        }
    });
});
</script>
@endpush


