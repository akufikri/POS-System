@props([
    'action' => '',
    'searchPlaceholder' => 'Search...',
    'searchName' => 'search',
    'searchValue' => request('search'),
    'showPerPage' => true
])

<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
    <div>
        @if(isset($title))
            <h2 class="text-xl font-medium tracking-tight text-[#010101]">{{ $title }}</h2>
        @endif
        @if(isset($subtitle))
            <p class="text-sm text-[#6b7280] mt-1">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
        {{-- Search Bar --}}
        <form action="{{ $action }}" method="GET" class="relative w-full sm:w-64">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6b7280] flex items-center">
                <iconify-icon icon="solar:magnifer-linear" class="text-lg"></iconify-icon>
            </span>
            <input type="text" name="{{ $searchName }}" value="{{ $searchValue }}" placeholder="{{ $searchPlaceholder }}"
                class="w-full bg-white border border-[#e5e5e5] rounded-xl pl-10 pr-4 py-2 text-sm text-[#010101] placeholder-[#6b7280] focus:outline-none focus:border-[#c9a96e] transition-colors shadow-sm js-auto-submit">
            
            {{-- Persist other filters --}}
            @foreach(request()->except([$searchName, 'page']) as $key => $value)
                @if(!is_array($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
        </form>

        {{-- Custom Filters Slot --}}
        {{ $slot }}

        {{-- Per Page Dropdown --}}
        @if($showPerPage)
        <div class="dropdown dropdown-end w-full sm:w-auto">
            <div tabindex="0" role="button" class="flex items-center gap-2 bg-white border border-[#e5e5e5] rounded-xl px-4 py-2 text-sm text-[#010101] shadow-sm hover:border-[#c9a96e] transition-colors w-full sm:w-auto">
                <span class="text-[#6b7280]">Show:</span>
                <span class="font-medium">{{ request('per_page', 10) }}</span>
                <iconify-icon icon="solar:alt-arrow-down-linear" class="text-xs ml-1 text-[#6b7280]"></iconify-icon>
            </div>
            <ul tabindex="0" class="dropdown-content z-[30] menu p-2 shadow-xl bg-white rounded-2xl w-24 border border-[#e5e5e5] mt-1 animate-in fade-in slide-in-from-top-2 duration-200">
                @foreach([5, 10, 25, 50] as $size)
                <li>
                    <a href="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => null]) }}" class="flex items-center justify-center py-2.5 rounded-xl {{ request('per_page', 10) == $size ? 'bg-[#f5f5f5] text-[#010101] font-bold' : 'text-[#6b7280]' }}">
                        {{ $size }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(request()->anyFilled(['search', 'category_id', 'per_page', 'date', 'cashier_id']))
            <a href="{{ $action }}" class="text-sm text-[#6b7280] hover:text-[#ef4444] transition-colors flex items-center gap-1.5 px-2">
                <iconify-icon icon="solar:restart-linear" class="text-base"></iconify-icon>
                <span>Reset</span>
            </a>
        @endif
    </div>
</div>
