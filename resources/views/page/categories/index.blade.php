@extends('layouts.app')

@section('title', __('ui.categories'))

@section('content')

<section id="categories" class="space-y-6">
    <x-ui.filter-bar 
        :action="route('categories.index')" 
        :search-placeholder="__('ui.search')"
        :search-value="request('search')"
        :show-per-page="false">
        
        <x-slot:title>{{ __('ui.manage_categories') }}</x-slot:title>
        <x-slot:subtitle>{{ __('ui.organize_categories') }}</x-slot:subtitle>

        {{-- Add Button --}}
        <button onclick="AppModal.open('modal-category')" class="flex items-center justify-center gap-2 px-5 py-2.5 bg-[#edcc94] hover:bg-[#c9a96e] text-[#010101] rounded-xl text-sm font-bold transition-all shadow-lg shadow-[#edcc94]/20 active:scale-95">
            <iconify-icon icon="solar:add-circle-linear" class="text-lg"></iconify-icon>
            <span>{{ __('ui.add_category') }}</span>
        </button>
    </x-ui.filter-bar>

    <x-ui.table>
        <x-slot:thead>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.category_name') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.products_count') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.sort_order') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.action') }}</th>
        </x-slot:thead>

        @forelse($categories as $category)
        <tr id="category-row-{{ $category->id }}" class="hover:bg-[#f5f5f5]/50 transition-colors group">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#f5e3bd]/30 flex items-center justify-center text-[#c9a96e]">
                        <iconify-icon icon="solar:tag-horizontal-linear" class="text-lg"></iconify-icon>
                    </div>
                    <span class="text-sm font-medium text-[#010101]">{{ $category->name }}</span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-[#6b7280]">{{ $category->products->count() }} {{ __('ui.items') }}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-0.5 rounded-md bg-[#f5f5f5] text-[#6b7280] text-[10px] font-bold border border-[#e5e5e5]">
                    {{ __('ui.sort_order') }}: {{ $category->sort_order }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="inline-flex join shadow-sm rounded-xl border border-[#e5e5e5] bg-white overflow-hidden">
                    <button class="join-item w-9 h-9 flex items-center justify-center hover:bg-[#f5e3bd]/30 text-[#6b7280] hover:text-[#c9a96e] transition-colors"
                        title="{{ __('ui.edit') }}"
                        data-id="{{ $category->id }}"
                        data-name="{{ $category->name }}"
                        data-sort="{{ $category->sort_order }}"
                        onclick="AppCategories.editCategory(this)">
                        <iconify-icon icon="solar:pen-linear" class="text-base"></iconify-icon>
                    </button>
                    <button class="join-item w-9 h-9 flex items-center justify-center border-l border-[#e5e5e5] hover:bg-[#fee2e2]/50 text-[#6b7280] hover:text-[#ef4444] transition-colors"
                        title="{{ __('ui.delete') }}"
                        data-id="{{ $category->id }}"
                        data-name="{{ $category->name }}"
                        onclick="AppCategories.deleteCategory(this)">
                        <iconify-icon icon="solar:trash-bin-minimalistic-linear" class="text-base"></iconify-icon>
                    </button>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="px-6 py-12 text-center text-[#6b7280] text-sm">
                -
            </td>
        </tr>
        @endforelse
    </x-ui.table>
</section>

{{-- Modal: Create/Edit Category --}}
<dialog id="modal-category" class="modal">
    <div class="modal-box w-full max-w-md bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-xl p-0 overflow-hidden">
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-[#e5e5e5]/50 flex items-center justify-between bg-[#f5f5f5]/30">
            <h3 class="text-lg font-semibold text-[#010101]" id="modal-category-title">{{ __('ui.add_category') }}</h3>
            <form method="dialog">
                <button class="text-[#6b7280] hover:text-[#010101] transition-colors p-1">
                    <iconify-icon icon="solar:close-circle-linear" stroke-width="1.5" class="text-2xl"></iconify-icon>
                </button>
            </form>
        </div>

        {{-- Modal Body --}}
        <div class="p-6">
            <form id="form-category" novalidate class="space-y-5">
                @csrf
                <input type="hidden" name="_category_id" id="category-id-field" value="">

                <div>
                    <label class="block text-xs font-medium text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.category_name') }}</label>
                    <input type="text" name="name" id="category-name" placeholder="e.g. Beverages"
                        class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-2.5 text-sm text-[#010101] placeholder-[#6b7280] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-all" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.sort_order') }}</label>
                    <input type="number" name="sort_order" id="category-sort" min="0" value="0"
                        class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-2.5 text-sm text-[#010101] placeholder-[#6b7280] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-all">
                    <p class="text-[10px] text-[#6b7280] mt-1.5 flex items-center gap-1">
                        <iconify-icon icon="solar:info-circle-linear" class="text-xs"></iconify-icon>
                        Lower numbers appear first in the POS menu
                    </p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" class="flex-1 py-2.5 bg-[#f5f5f5] hover:bg-[#e5e5e5] text-[#010101] rounded-xl text-sm font-medium transition-colors border border-[#e5e5e5]" onclick="AppModal.close('modal-category')">
                        {{ __('ui.cancel') }}
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-[#edcc94] hover:bg-[#c9a96e] text-[#010101] rounded-xl text-sm font-medium transition-colors shadow-sm active:scale-[0.98]">
                        {{ __('ui.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- Backdrop with manual fix for visible text and backdrop blur --}}
    <form method="dialog" class="modal-backdrop bg-[#010101]/20 backdrop-blur-[2px]">
        <button class="cursor-default outline-none text-transparent">close</button>
    </form>
</dialog>

{{-- Modal: Delete Confirm --}}
<dialog id="modal-delete-category" class="modal">
    <div class="modal-box max-w-sm bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-xl p-6">
        <div class="flex items-start gap-4 mb-6">
            <div class="w-12 h-12 rounded-xl bg-[#fee2e2]/50 flex items-center justify-center text-[#ef4444] shrink-0 border border-[#ef4444]/20">
                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="text-2xl"></iconify-icon>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-[#010101]">{{ __('ui.confirm_delete') }}?</h3>
                <p class="text-sm text-[#6b7280] mt-1 leading-relaxed">
                    {{ __('ui.categories') }} <strong id="delete-category-name" class="text-[#010101]"></strong> {{ __('ui.confirm_delete_message') }}
                </p>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="button" class="flex-1 py-2.5 bg-[#f5f5f5] hover:bg-[#e5e5e5] text-[#010101] rounded-xl text-sm font-medium transition-colors border border-[#e5e5e5]" onclick="AppModal.close('modal-delete-category')">
                {{ __('ui.cancel') }}
            </button>
            <button type="button" id="btn-confirm-delete-category" class="flex-1 py-2.5 bg-[#fee2e2] hover:bg-[#ef4444] hover:text-white text-[#ef4444] rounded-xl text-sm font-medium transition-colors shadow-sm active:scale-[0.98]">
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
<script src="{{ asset('js/modules/category.js') }}"></script>
@endpush
