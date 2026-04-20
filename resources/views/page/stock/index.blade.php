@extends('layouts.app')

@section('content')
    <div class="space-y-8">
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-medium tracking-tight text-[#010101]">{{ __('ui.stock_management') }}</h1>
                <p class="text-sm text-[#6b7280] mt-1">{{ __('ui.manage_inventory_stock') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="AppStock.openModal('in')" class="flex items-center gap-2 px-4 py-2.5 bg-[#edcc94] hover:bg-[#c9a96e] text-[#010101] rounded-xl text-sm font-bold transition-all shadow-sm">
                    <iconify-icon icon="solar:import-linear" class="text-lg"></iconify-icon>
                    {{ __('ui.stock_in') }}
                </button>
                <button onclick="AppStock.openModal('out')" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-[#e5e5e5] hover:border-[#ef4444] hover:text-[#ef4444] text-[#6b7280] rounded-xl text-sm font-bold transition-all shadow-sm">
                    <iconify-icon icon="solar:export-linear" class="text-lg"></iconify-icon>
                    {{ __('ui.stock_out') }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Product Stock Table --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl border border-[#e5e5e5] shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#e5e5e5] bg-[#f5f5f5]/30">
                        <h3 class="text-sm font-bold text-[#010101] uppercase tracking-wider">{{ __('ui.current_stock') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[#e5e5e5] bg-[#fcfcfc]">
                                    <th class="px-6 py-3 text-[10px] font-bold text-[#6b7280] uppercase tracking-widest">{{ __('ui.product') }}</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-[#6b7280] uppercase tracking-widest text-right">{{ __('ui.stock') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e5e5e5]">
                                @foreach($products as $product)
                                <tr class="hover:bg-[#f5f5f5]/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-[#010101]">{{ $product->name }}</div>
                                        <div class="text-[10px] text-[#6b7280]">{{ $product->category->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $product->stock <= 5 ? 'bg-[#fef2f2] text-[#ef4444]' : 'bg-[#f0fdf4] text-[#16a34a]' }}">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Stock Logs Table --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-[#e5e5e5] shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#e5e5e5] bg-[#f5f5f5]/30">
                        <h3 class="text-sm font-bold text-[#010101] uppercase tracking-wider">{{ __('ui.inventory_logs') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[#e5e5e5] bg-[#fcfcfc]">
                                    <th class="px-6 py-3 text-[10px] font-bold text-[#6b7280] uppercase tracking-widest">{{ __('ui.date') }}</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-[#6b7280] uppercase tracking-widest">{{ __('ui.product') }}</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-[#6b7280] uppercase tracking-widest">{{ __('ui.status') }}</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-[#6b7280] uppercase tracking-widest text-right">{{ __('ui.qty') }}</th>
                                    <th class="px-6 py-3 text-[10px] font-bold text-[#6b7280] uppercase tracking-widest">{{ __('ui.cashier') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e5e5e5]">
                                @foreach($logs as $log)
                                <tr class="hover:bg-[#f5f5f5]/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs text-[#010101] font-medium">{{ $log->created_at->format('d M, H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-medium text-[#010101]">{{ $log->product->name ?? 'Unknown' }}</div>
                                        @if($log->notes)
                                            <div class="text-[10px] text-[#6b7280] italic">{{ $log->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $typeColor = [
                                                'in' => 'bg-[#f0fdf4] text-[#16a34a]',
                                                'out' => 'bg-[#fef2f2] text-[#ef4444]',
                                                'sale' => 'bg-[#f5f5f5] text-[#6b7280]',
                                                'adjustment' => 'bg-[#eff6ff] text-[#3b82f6]',
                                            ][$log->type] ?? 'bg-gray-100';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $typeColor }}">
                                            {{ __('ui.stock_' . $log->type) ?? $log->type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-xs font-bold {{ $log->quantity > 0 ? 'text-[#16a34a]' : 'text-[#ef4444]' }}">
                                        {{ $log->quantity > 0 ? '+' : '' }}{{ $log->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs text-[#6b7280]">{{ $log->user->name ?? '-' }}</div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($logs->hasPages())
                    <div class="px-6 py-4 border-t border-[#e5e5e5] bg-[#fcfcfc]">
                        {{ $logs->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Stock Adjust --}}
    <dialog id="modal-stock" class="modal">
        <div class="modal-box max-w-md bg-white rounded-2xl border border-[#e5e5e5] shadow-2xl p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-[#e5e5e5]/50 flex items-center justify-between bg-[#f5f5f5]/30">
                <h3 id="modal-stock-title" class="text-lg font-bold text-[#010101] uppercase tracking-wider">Stock Adjustment</h3>
                <button type="button" onclick="AppModal.close('modal-stock')" class="text-[#6b7280] hover:text-[#010101]">
                    <iconify-icon icon="solar:close-circle-linear" class="text-2xl"></iconify-icon>
                </button>
            </div>
            
            <form id="form-stock" onsubmit="AppStock.submit(event)" class="p-6 space-y-5">
                <input type="hidden" name="type" id="stock-type">
                
                {{-- Product Select --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#6b7280] uppercase tracking-widest">{{ __('ui.product') }}</label>
                    <select name="product_id" required class="w-full bg-[#f5f5f5] border border-[#e5e5e5] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#edcc94] transition-all">
                        <option value="">{{ __('ui.select_product') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ __('ui.stock') }}: {{ $product->stock }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Quantity --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#6b7280] uppercase tracking-widest">{{ __('ui.quantity') }}</label>
                    <input type="number" name="quantity" required min="1" placeholder="0" 
                        class="w-full bg-[#f5f5f5] border border-[#e5e5e5] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#edcc94] transition-all">
                </div>

                {{-- Reason / Notes --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold text-[#6b7280] uppercase tracking-widest">{{ __('ui.notes') }}</label>
                    <textarea name="notes" rows="3" placeholder="{{ __('ui.reason') }}..."
                        class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#c9a96e] transition-all resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" class="flex-1 py-3 bg-[#f5f5f5] text-[#010101] rounded-xl font-bold transition-all" onclick="AppModal.close('modal-stock')">
                        {{ __('ui.cancel') }}
                    </button>
                    <button type="submit" class="flex-1 py-3 bg-[#edcc94] hover:bg-[#c9a96e] text-[#010101] rounded-xl font-bold transition-all shadow-lg shadow-[#edcc94]/20">
                        {{ __('ui.save') }}
                    </button>
                </div>
            </form>
        </div>
    </dialog>
@endsection

@push('scripts')
<script>
    window.I18N_STOCK = {
        stock_in: "{{ __('ui.stock_in') }}",
        stock_out: "{{ __('ui.stock_out') }}",
    };
</script>
<script src="{{ asset('js/modules/stock.js') }}"></script>
@endpush
