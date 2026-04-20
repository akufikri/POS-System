@props([
    'label' => 'Select',
    'activeValue' => null,
    'options' => [],
    'paramName' => 'filter',
    'icon' => 'solar:filter-linear'
])

<div class="dropdown dropdown-end sm:dropdown-start w-full sm:w-auto">
    <div tabindex="0" role="button" class="flex items-center gap-2 bg-white border border-[#e5e5e5] rounded-xl px-4 py-2 text-sm text-[#010101] shadow-sm hover:border-[#c9a96e] transition-colors min-w-[160px] w-full sm:w-auto">
        <iconify-icon icon="{{ $icon }}" class="text-lg text-[#6b7280]"></iconify-icon>
        <span class="truncate">{{ $label }}</span>
        <iconify-icon icon="solar:alt-arrow-down-linear" class="text-xs ml-auto text-[#6b7280]"></iconify-icon>
    </div>
    <ul tabindex="0" class="dropdown-content z-[30] menu p-2 shadow-xl bg-white rounded-2xl w-52 border border-[#e5e5e5] mt-1 animate-in fade-in slide-in-from-top-2 duration-200">
        {{ $slot }}
    </ul>
</div>
