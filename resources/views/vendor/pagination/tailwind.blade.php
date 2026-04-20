@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between flex-wrap gap-4">
    
    {{-- Info Section --}}
    <div class="hidden sm:block">
        <p class="text-xs text-[#6b7280]">
            Showing <span class="font-bold text-[#010101]">{{ $paginator->firstItem() }}</span> 
            to <span class="font-bold text-[#010101]">{{ $paginator->lastItem() }}</span> 
            of <span class="font-bold text-[#010101]">{{ $paginator->total() }}</span> results
        </p>
    </div>

    {{-- Buttons Section --}}
    <div class="flex items-center gap-1.5">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="w-9 h-9 flex items-center justify-center rounded-full text-[#e5e5e5] cursor-not-allowed border border-transparent">
                <iconify-icon icon="solar:alt-arrow-left-linear" class="text-lg"></iconify-icon>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="w-9 h-9 flex items-center justify-center rounded-full text-[#6b7280] hover:bg-[#f5f5f5] hover:text-[#010101] transition-all border border-transparent active:scale-90">
                <iconify-icon icon="solar:alt-arrow-left-linear" class="text-lg"></iconify-icon>
            </a>
        @endif

        {{-- Pagination Elements --}}
        <div class="flex items-center gap-1">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="w-9 h-9 flex items-center justify-center text-[#6b7280] text-sm">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-9 h-9 flex items-center justify-center rounded-full bg-[#c9a96e] text-white text-sm font-bold shadow-lg shadow-[#c9a96e]/20 z-10">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="w-9 h-9 flex items-center justify-center rounded-full text-[#6b7280] hover:bg-[#f5f5f5] hover:text-[#010101] text-sm font-medium transition-all active:scale-90">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="w-9 h-9 flex items-center justify-center rounded-full text-[#6b7280] hover:bg-[#f5f5f5] hover:text-[#010101] transition-all border border-transparent active:scale-90">
                <iconify-icon icon="solar:alt-arrow-right-linear" class="text-lg"></iconify-icon>
            </a>
        @else
            <span class="w-9 h-9 flex items-center justify-center rounded-full text-[#e5e5e5] cursor-not-allowed border border-transparent">
                <iconify-icon icon="solar:alt-arrow-right-linear" class="text-lg"></iconify-icon>
            </span>
        @endif
    </div>
</nav>
@endif
