<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-[#e5e5e5] shadow-sm overflow-hidden']) }}>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-[#e5e5e5] bg-[#fcfcfc]/50">
                    {{ $thead }}
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e5e5e5]">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    
    @if(isset($pagination))
        <div class="px-6 py-4 border-t border-[#e5e5e5] bg-[#fcfcfc]/50">
            {{ $pagination }}
        </div>
    @endif
</div>
