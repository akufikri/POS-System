@extends('layouts.app')

@section('title', 'My Shift')

@section('content')

<section id="shift" class="space-y-6">
    <div>
        <h2 class="text-xl font-medium tracking-tight text-[#010101]">{{ __('ui.my_shift') }}</h2>
        <p class="text-sm text-[#6b7280] mt-1">{{ __('ui.manage_work_shift') }}</p>
    </div>

    <script>
        window.SHIFT_STRINGS = {
            no_active_shift: "{{ __('ui.no_active_shift') }}",
            open_shift: "{{ __('ui.open_shift') }}",
            shift_active_since: "{{ __('ui.shift_active_since') }}",
            active: "{{ __('ui.active') }}",
            opening_cash: "{{ __('ui.opening_cash') }}",
            transactions: "{{ __('ui.transactions') }}",
            revenue: "{{ __('ui.revenue') }}",
            close_shift: "{{ __('ui.close_shift') }}",
            expected_cash: "{{ __('ui.expected_cash') }}",
            actual_cash_in_drawer: "{{ __('ui.actual_cash_in_drawer') }}",
            enter_actual_cash_amount: "{{ __('ui.enter_actual_cash_amount') }}",
            cancel: "{{ __('ui.cancel') }}",
            shift_opened_successfully: "{{ __('ui.shift_opened_successfully') }}",
            shift_closed_successfully: "{{ __('ui.shift_closed_successfully') }}",
            please_enter_actual_cash_amount: "{{ __('ui.please_enter_actual_cash_amount') }}",
            opened: "{{ __('ui.opened') }}",
            closed: "{{ __('ui.closed') }}",
            opening: "{{ __('ui.opening') }}",
            closing: "{{ __('ui.closing') }}",
            difference: "{{ __('ui.difference') }}",
            cashier: "{{ __('ui.cashier') }}",
            just_now: "{{ __('ui.just_now') }}",
            manage_work_shift: "{{ __('ui.manage_work_shift') }}",
            no_shift_records_yet: "No shift records yet.", // Add to ui.php if needed
        };
    </script>

    {{-- Current shift card --}}
    <div class="bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm p-6">
        <div id="shift-status">
            <div class="flex items-center gap-3">
                <div class="inline-block w-8 h-8 border-2 border-[#e5e5e5] border-t-[#c9a96e] rounded-full animate-spin"></div>
                <span class="text-sm text-[#6b7280]">{{ __('ui.loading_shift_status') }}</span>
            </div>
        </div>
    </div>

    {{-- Shift history (owner only) --}}
    @if(auth()->user()->isOwner())
    <div class="bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-[#e5e5e5]">
            <h3 class="text-base font-medium text-[#010101]">{{ __('ui.all_staff_shift_history') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f5f5f5]/50 border-b border-[#e5e5e5]">
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.cashier') }}</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.opened') }}</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.closed') }}</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.opening') }}</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.closing') }}</th>
                        <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.difference') }}</th>
                    </tr>
                </thead>
                <tbody id="shift-history-body" class="divide-y divide-[#e5e5e5]">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-[#6b7280] text-sm">
                            Loading shift history...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif
</section>

@push('scripts')
<script src="{{ asset('js/modules/shift.js') }}"></script>
@endpush
@endsection

{{-- Modal: Open/Close Shift --}}
<dialog id="modal-shift" class="modal">
    <div class="modal-box w-full max-w-md bg-[#ffffff] rounded-2xl border border-[#e5e5e5] shadow-sm">
        <!-- Content loaded dynamically by shift.js -->
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
