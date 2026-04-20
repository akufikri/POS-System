@extends('layouts.app')

@section('title', __('ui.products'))

@section('content')

<section id="products" class="space-y-6">
    {{-- Header & Filter Bar --}}
    <x-ui.filter-bar 
        :action="route('products.index')" 
        :search-placeholder="__('ui.search')"
        :search-value="request('search')">
        
        <x-slot:title>{{ __('ui.product_management') }}</x-slot:title>
        <x-slot:subtitle>{{ __('ui.manage_inventory') }}</x-slot:subtitle>

        {{-- Category Dropdown --}}
        <x-ui.filter-dropdown :label="$categories->firstWhere('id', request('category_id'))?->name ?? __('ui.all_categories')">
            <li>
                <a href="{{ request()->fullUrlWithQuery(['category_id' => null, 'page' => null]) }}" class="flex items-center gap-2 py-2.5 rounded-xl {{ !request('category_id') ? 'bg-[#f5e3bd]/30 text-[#c9a96e] font-bold' : 'text-[#6b7280]' }}">
                    {{ __('ui.all_categories') }}
                </a>
            </li>
            <div class="h-px bg-[#e5e5e5]/50 my-1"></div>
            @foreach($categories as $category)
            <li>
                <a href="{{ request()->fullUrlWithQuery(['category_id' => $category->id, 'page' => null]) }}" class="flex items-center gap-2 py-2.5 rounded-xl {{ request('category_id') == $category->id ? 'bg-[#f5e3bd]/30 text-[#c9a96e] font-bold' : 'text-[#6b7280]' }}">
                    {{ $category->name }}
                </a>
            </li>
            @endforeach
        </x-ui.filter-dropdown>
    </x-ui.filter-bar>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- Products Table --}}
        <div class="lg:col-span-2">
            <x-ui.table>
                <x-slot:thead>
                    <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.product') }}</th>
                    <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.categories') }}</th>
                    <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.price') }}</th>
                    <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.cost') }} (HPP)</th>
                    <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.stock') }}</th>
                    <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.action') }}</th>
                </x-slot:thead>

                @forelse($products as $product)
                <tr id="product-row-{{ $product->id }}" class="hover:bg-[#f5f5f5]/50 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover border border-[#e5e5e5]">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-[#f5f5f5] flex items-center justify-center border border-[#e5e5e5] text-[#6b7280]">
                                    <iconify-icon icon="solar:cup-hot-linear"></iconify-icon>
                                </div>
                            @endif
                            <span class="text-sm font-medium text-[#010101]">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2.5 py-1 rounded-lg bg-[#f5f5f5] text-[#6b7280] text-[10px] font-bold uppercase tracking-wider border border-[#e5e5e5]">
                            {{ $product->category?->name ?? '-' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[#010101] font-medium">{{ $product->price_formatted }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[#6b7280]">{{ $product->cost_formatted }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $product->stock <= 5 ? 'bg-[#fef2f2] text-[#ef4444]' : 'bg-[#f0fdf4] text-[#16a34a]' }}">
                            {{ $product->stock }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="inline-flex join shadow-sm rounded-xl border border-[#e5e5e5] bg-white overflow-hidden">
                            <button class="join-item w-9 h-9 flex items-center justify-center hover:bg-[#f5e3bd]/30 text-[#6b7280] hover:text-[#c9a96e] transition-colors"
                                title="{{ __('ui.edit') }}"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-category_id="{{ $product->category_id }}"
                                data-description="{{ $product->description }}"
                                data-price="{{ $product->price }}"
                                data-cost="{{ $product->cost }}"
                                data-stock="{{ $product->stock }}"
                                data-image_url="{{ $product->image_url }}"
                                data-is_active="{{ $product->is_active ? '1' : '0' }}"
                                onclick="AppProducts.editProduct(this)">
                                <iconify-icon icon="solar:pen-linear" class="text-base"></iconify-icon>
                            </button>
                            <button class="join-item w-9 h-9 flex items-center justify-center border-l border-[#e5e5e5] hover:bg-[#fee2e2]/50 text-[#6b7280] hover:text-[#ef4444] transition-colors"
                                title="{{ __('ui.delete') }}"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                onclick="AppProducts.deleteProduct(this)">
                                <iconify-icon icon="solar:trash-bin-minimalistic-linear" class="text-base"></iconify-icon>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="products-empty-row">
                    <td colspan="6" class="px-6 py-12 text-center text-[#6b7280] text-sm">
                        {{ __('ui.no_products_found') ?? '-' }}
                    </td>
                </tr>
                @endforelse

                @if($products->hasPages())
                    <x-slot:pagination>
                        {{ $products->links() }}
                    </x-slot:pagination>
                @endif
            </x-ui.table>
        </div>

        {{-- Quick Add Form --}}
        <div class="bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm p-6 flex flex-col gap-5 relative">
            <div class="absolute -top-3 -right-3 w-6 h-6 bg-[#f5f5f5] border border-[#e5e5e5] rounded-full flex items-center justify-center lg:hidden">
                <iconify-icon icon="solar:close-circle-linear" class="text-xs text-[#6b7280]"></iconify-icon>
            </div>
            <h3 class="text-base font-medium text-[#010101]" id="quick-add-title">{{ __('ui.add_product') }}</h3>

            <form id="form-product" enctype="multipart/form-data" novalidate class="space-y-4">
                @csrf
                <input type="hidden" name="_product_id" id="product-id-field" value="">

                <div>
                    <label class="block text-xs font-medium text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.categories') }}</label>
                    <select name="category_id" id="product-category_id" class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-3 py-2 text-sm text-[#010101] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors">
                        <option value="">{{ __('ui.select_category') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.product_name') }}</label>
                    <input type="text" name="name" id="product-name" placeholder="e.g. Matcha Latte"
                        class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-3 py-2 text-sm text-[#010101] placeholder-[#6b7280] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.description') }}</label>
                    <textarea name="description" id="product-description" rows="2" placeholder="Short description..."
                        class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-3 py-2 text-sm text-[#010101] placeholder-[#6b7280] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.price') }}</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-[#6b7280]">Rp</span>
                            <input type="text" name="price" id="product-price" placeholder="0" required
                                class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl pl-8 pr-3 py-2 text-sm text-[#010101] placeholder-[#6b7280] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors input-currency"
                                inputmode="numeric">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.cost') }}</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-[#6b7280]">Rp</span>
                            <input type="text" name="cost" id="product-cost" placeholder="0"
                                class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl pl-8 pr-3 py-2 text-sm text-[#010101] placeholder-[#6b7280] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors input-currency"
                                inputmode="numeric">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.stock') }}</label>
                    <input type="number" name="stock" id="product-stock" placeholder="0"
                        class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-3 py-2 text-sm text-[#010101] placeholder-[#6b7280] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors" required>
                    <p id="stock-help" class="text-[10px] text-[#6b7280] mt-1 hidden italic">{{ __('ui.use_stock_page_to_adjust') ?? 'Gunakan halaman Stok untuk mengubah stok yang ada' }}</p>
                </div>

                {{-- Image upload --}}
                <div>
                    <label class="block text-xs font-medium text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.image') }}</label>
                    <div class="relative">
                        <div id="image-upload-area" class="border border-dashed border-[#e5e5e5] rounded-xl h-24 flex flex-col items-center justify-center text-[#6b7280] bg-[#f5f5f5]/30 hover:bg-[#f5f5f5] cursor-pointer transition-colors">
                            <iconify-icon icon="solar:gallery-add-linear" class="text-xl mb-1"></iconify-icon>
                            <span class="text-xs" id="image-upload-text">{{ __('ui.click_to_upload') }}</span>
                        </div>
                        <input type="file" name="image" id="product-image" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                        <img id="image-preview" src="" alt="" class="absolute inset-0 w-full h-full object-cover rounded-xl hidden">
                        <button type="button" id="btn-remove-image" class="absolute top-2 right-2 w-6 h-6 bg-white rounded-full shadow-sm flex items-center justify-center text-[#6b7280] hover:text-[#010101] hidden">
                            <iconify-icon icon="solar:close-circle-linear" class="text-sm"></iconify-icon>
                        </button>
                    </div>
                    <p class="text-xs text-[#6b7280] mt-1">Max 2MB. JPG, PNG, WEBP.</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="product-is_active" value="1" checked
                        class="w-4 h-4 rounded border-[#e5e5e5] text-[#c9a96e] focus:ring-[#c9a96e] focus:ring-offset-0">
                    <label for="product-is_active" class="text-xs font-medium text-[#010101]">{{ __('ui.active') }} ({{ __('ui.visible_in_pos') }})</label>
                </div>

                <button type="submit" class="w-full py-2.5 bg-[#f5f5f5] hover:bg-[#edcc94] text-[#010101] rounded-xl text-sm font-medium transition-colors border border-[#e5e5e5] hover:border-transparent mt-2">
                    {{ __('ui.save') }}
                </button>
            </form>
        </div>
    </div>
</section>

{{-- Modal: Delete Confirm --}}
<dialog id="modal-delete-product" class="modal">
    <div class="modal-box max-w-sm bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-[#fee2e2]/30 flex items-center justify-center text-[#ef4444] shrink-0">
                <iconify-icon icon="solar:danger-triangle-linear" stroke-width="1.5" class="text-xl"></iconify-icon>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-[#010101]">{{ __('ui.confirm_delete') }}?</h3>
                <p class="text-sm text-[#6b7280] mt-1">
                    <strong id="delete-product-name"></strong> {{ __('ui.confirm_delete_message') }}
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" class="flex-1 py-2 px-4 bg-[#f5f5f5] hover:bg-[#e5e5e5] text-[#010101] rounded-xl text-sm font-medium transition-colors" onclick="AppModal.close('modal-delete-product')">
                {{ __('ui.cancel') }}
            </button>
            <button type="button" id="btn-confirm-delete-product" class="flex-1 py-2 px-4 bg-[#fee2e2] hover:bg-[#fecaca] text-[#ef4444] rounded-xl text-sm font-medium transition-colors">
                {{ __('ui.delete') }}
            </button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop bg-[#010101]/20 backdrop-blur-[2px]">
        <button class="cursor-default outline-none text-transparent">close</button>
    </form>
</dialog>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/product.js') }}"></script>
@endpush
