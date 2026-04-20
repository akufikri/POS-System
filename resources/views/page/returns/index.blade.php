@extends('layouts.app')

@section('title', 'Returns')

@section('content')

<section id="returns" class="space-y-6">
    <div>
        <h2 class="text-xl font-medium tracking-tight text-[#010101]">Return Requests</h2>
        <p class="text-sm text-[#6b7280] mt-1">Manage and process return requests.</p>
    </div>

    {{-- Filter --}}
    <div class="bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm p-4">
        <form method="GET" action="{{ route('returns.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-[#010101] mb-1.5">Status</label>
                <select name="status" class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-3 py-2.5 text-sm text-[#010101] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors">
                    <option value="">All</option>
                    <option value="pending" @if($filters['status'] === 'pending') selected @endif>Pending</option>
                    <option value="approved" @if($filters['status'] === 'approved') selected @endif>Approved</option>
                    <option value="rejected" @if($filters['status'] === 'rejected') selected @endif>Rejected</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-[#010101] mb-1.5">Date From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                    class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-3 py-2.5 text-sm text-[#010101] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-[#010101] mb-1.5">Date To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                    class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-3 py-2.5 text-sm text-[#010101] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-[#edcc94] hover:bg-[#c9a96e] text-[#010101] rounded-xl text-sm font-medium transition-colors shadow-sm">
                    <iconify-icon icon="solar:filter-linear" stroke-width="1.5"></iconify-icon>
                    Filter
                </button>
                @if($filters['status'] || $filters['date_from'] || $filters['date_to'])
                <a href="{{ route('returns.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-[#f5f5f5] hover:bg-[#e5e5e5] text-[#010101] rounded-xl text-sm font-medium transition-colors border border-[#e5e5e5]">
                    <iconify-icon icon="solar:restart-linear" stroke-width="1.5"></iconify-icon>
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Returns list --}}
    <div class="bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f5f5f5]/50 border-b border-[#e5e5e5]">
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">#ID</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">Order</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">Cashier</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">Refund</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e5e5]">
                    @forelse($returns as $return)
                    <tr class="hover:bg-[#f5f5f5]/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#010101]">#RET-{{ str_pad($return->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#6b7280]">{{ $return->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#010101]">#{{ $return->order_id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#010101]">{{ $return->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-[#6b7280] max-w-xs truncate">{{ $return->reason }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#010101] text-right">
                            Rp {{ number_format($return->refund_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($return->status === 'approved')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#dcfce7]/30 text-[#16a34a] text-xs font-medium rounded-full">
                                <iconify-icon icon="solar:check-circle-linear" stroke-width="1.5" class="text-sm"></iconify-icon>
                                Approved
                            </span>
                            @elseif($return->status === 'pending')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#fef3c7]/30 text-[#ca8a04] text-xs font-medium rounded-full">
                                <iconify-icon icon="solar:clock-circle-linear" stroke-width="1.5" class="text-sm"></iconify-icon>
                                Pending
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#fee2e2]/30 text-[#ef4444] text-xs font-medium rounded-full">
                                <iconify-icon icon="solar:close-circle-linear" stroke-width="1.5" class="text-sm"></iconify-icon>
                                Rejected
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-[#6b7280] text-sm">
                            No returns yet
                            @if($filters['status'] || $filters['date_from']) with this filter @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="p-4 border-t border-[#e5e5e5] flex items-center justify-center">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</section>

@endsection
