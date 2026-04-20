@extends('layouts.app')

@section('title', __('ui.team_management'))

@section('content')

<section id="employees" class="space-y-6">
    <x-ui.filter-bar 
        :action="route('employees.index')" 
        :search-placeholder="__('ui.search')"
        :search-value="request('search')"
        :show-per-page="false">
        
        <x-slot:title>{{ __('ui.team_management') }}</x-slot:title>
        <x-slot:subtitle>{{ __('ui.manage_staff') }}</x-slot:subtitle>

        {{-- Add Button --}}
        <button onclick="AppModal.open('modal-employee')" class="flex items-center justify-center gap-2 px-5 py-2.5 bg-[#edcc94] hover:bg-[#c9a96e] text-[#010101] rounded-xl text-sm font-bold transition-all shadow-lg shadow-[#edcc94]/20 active:scale-95">
            <iconify-icon icon="solar:user-plus-linear" class="text-lg"></iconify-icon>
            <span>{{ __('ui.add_employee') }}</span>
        </button>
    </x-ui.filter-bar>

    <x-ui.table>
        <x-slot:thead>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.employees') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider">{{ __('ui.employee_email') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-center">{{ __('ui.status') }}</th>
            <th class="px-6 py-4 text-xs font-medium text-[#6b7280] uppercase tracking-wider text-right">{{ __('ui.action') }}</th>
        </x-slot:thead>

        @forelse($employees as $employee)
        <tr id="employee-row-{{ $employee->id }}" class="hover:bg-[#f5f5f5]/50 transition-colors group">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#f5e3bd]/30 flex items-center justify-center text-[#c9a96e] font-bold">
                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                    </div>
                    <div>
                        <span class="text-sm font-medium text-[#010101] block">{{ $employee->name }}</span>
                        <span class="text-[10px] text-[#6b7280] uppercase tracking-widest">{{ $employee->role }}</span>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-[#6b7280]">{{ $employee->email }}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
                @if($employee->isSuspended())
                    <div class="inline-flex flex-col items-center">
                        <span class="px-2.5 py-1 rounded-full bg-[#fee2e2] text-[#ef4444] text-[10px] font-bold uppercase tracking-wider border border-[#ef4444]/10">
                            {{ __('ui.suspended') }}
                        </span>
                        @if($employee->suspended_until)
                            <span class="text-[9px] text-[#ef4444] mt-1 italic">{{ __('ui.until') }} {{ $employee->suspended_until->format('d M, H:i') }}</span>
                        @endif
                    </div>
                @else
                    <span class="px-2.5 py-1 rounded-full bg-[#dcfce7] text-[#166534] text-[10px] font-bold uppercase tracking-wider border border-[#166534]/10">
                        {{ __('ui.active') }}
                    </span>
                @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="inline-flex join shadow-sm rounded-xl border border-[#e5e5e5] bg-white overflow-hidden">
                    <button class="join-item w-9 h-9 flex items-center justify-center hover:bg-[#f5e3bd]/30 text-[#6b7280] hover:text-[#c9a96e] transition-colors"
                        title="{{ __('ui.edit') }}"
                        data-id="{{ $employee->id }}"
                        data-name="{{ $employee->name }}"
                        data-email="{{ $employee->email }}"
                        onclick="AppEmployees.editEmployee(this)">
                        <iconify-icon icon="solar:pen-linear" class="text-base"></iconify-icon>
                    </button>
                    <button class="join-item w-9 h-9 flex items-center justify-center border-l border-[#e5e5e5] hover:bg-[#f5f5f5] text-[#6b7280] hover:text-[#010101] transition-colors"
                        title="{{ __('ui.suspend_employee') }}"
                        data-id="{{ $employee->id }}"
                        data-name="{{ $employee->name }}"
                        data-is-suspended="{{ $employee->isSuspended() ? '1' : '0' }}"
                        data-reason="{{ $employee->suspension_reason }}"
                        onclick="AppEmployees.suspendEmployee(this)">
                        <iconify-icon icon="solar:user-block-linear" class="text-base"></iconify-icon>
                    </button>
                    <button class="join-item w-9 h-9 flex items-center justify-center border-l border-[#e5e5e5] hover:bg-[#fee2e2]/50 text-[#6b7280] hover:text-[#ef4444] transition-colors"
                        title="{{ __('ui.delete') }}"
                        data-id="{{ $employee->id }}"
                        data-name="{{ $employee->name }}"
                        onclick="AppEmployees.deleteEmployee(this)">
                        <iconify-icon icon="solar:trash-bin-minimalistic-linear" class="text-base"></iconify-icon>
                    </button>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="px-6 py-12 text-center text-[#6b7280] text-sm">
                {{ __('ui.no_employees_found') }}
            </td>
        </tr>
        @endforelse

        @if($employees->hasPages())
            <x-slot:pagination>
                {{ $employees->links() }}
            </x-slot:pagination>
        @endif
    </x-ui.table>
</section>

{{-- Modal: Create/Edit Employee --}}
<dialog id="modal-employee" class="modal">
    <div class="modal-box w-full max-w-md bg-white rounded-2xl border border-[#e5e5e5] shadow-xl p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-[#e5e5e5]/50 flex items-center justify-between bg-[#f5f5f5]/30">
            <h3 class="text-lg font-semibold text-[#010101]" id="modal-employee-title">{{ __('ui.add_employee') }}</h3>
            <button type="button" onclick="AppModal.close('modal-employee')" class="text-[#6b7280] hover:text-[#010101]">
                <iconify-icon icon="solar:close-circle-linear" class="text-2xl"></iconify-icon>
            </button>
        </div>
        <div class="p-6">
            <form id="form-employee" novalidate class="space-y-4">
                @csrf
                <input type="hidden" name="_employee_id" id="employee-id-field" value="">
                <div>
                    <label class="block text-xs font-bold text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.employee_name') }}</label>
                    <input type="text" name="name" id="employee-name" placeholder="e.g. John Doe"
                        class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-all" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.employee_email') }}</label>
                    <input type="email" name="email" id="employee-email" placeholder="john@example.com"
                        class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-all" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.employee_password') }}</label>
                    <input type="password" name="password" id="employee-password" placeholder="Min. 6 characters"
                        class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-all">
                    <p class="text-[10px] text-[#6b7280] mt-1 italic" id="password-hint">{{ __('ui.leave_blank_password') }}</p>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" class="flex-1 py-3 bg-[#f5f5f5] text-[#010101] rounded-xl text-sm font-bold transition-all" onclick="AppModal.close('modal-employee')">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="flex-1 py-3 bg-[#edcc94] text-[#010101] rounded-xl text-sm font-bold transition-all shadow-sm active:scale-95">{{ __('ui.save') }}</button>
                </div>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop bg-[#010101]/20">
        <button>close</button>
    </form>
</dialog>

{{-- Modal: Suspend Employee --}}
<dialog id="modal-suspend" class="modal">
    <div class="modal-box w-full max-w-md bg-white rounded-2xl border border-[#e5e5e5] shadow-xl p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-[#e5e5e5]/50 flex items-center justify-between bg-[#f5f5f5]/30">
            <h3 class="text-lg font-semibold text-[#010101]">{{ __('ui.suspend_employee') }}</h3>
            <button type="button" onclick="AppModal.close('modal-suspend')" class="text-[#6b7280] hover:text-[#010101]">
                <iconify-icon icon="solar:close-circle-linear" class="text-2xl"></iconify-icon>
            </button>
        </div>
        <div class="p-6">
            <form id="form-suspend" novalidate class="space-y-4">
                @csrf
                <input type="hidden" name="user_id" id="suspend-user-id" value="">
                <div>
                    <label class="block text-xs font-bold text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.suspension_duration') }}</label>
                    <select name="duration" id="suspend-duration" class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-all">
                        <option value="0">{{ __('ui.indefinite') }}</option>
                        <option value="60">{{ __('ui.1_hour') }}</option>
                        <option value="1440">{{ __('ui.1_day') }}</option>
                        <option value="10080">{{ __('ui.1_week') }}</option>
                        <option value="43200">{{ __('ui.1_month') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#010101] mb-1.5 uppercase tracking-wider">{{ __('ui.suspension_reason') }}</label>
                    <textarea name="reason" id="suspend-reason" rows="3" placeholder="e.g. Violation of company policy"
                        class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-all resize-none" required></textarea>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" class="flex-1 py-3 bg-[#f5f5f5] text-[#010101] rounded-xl text-sm font-bold transition-all" onclick="AppModal.close('modal-suspend')">{{ __('ui.cancel') }}</button>
                    <button type="submit" class="flex-1 py-3 bg-[#ef4444] text-white rounded-xl text-sm font-bold transition-all shadow-sm active:scale-95">{{ __('ui.update') }}</button>
                </div>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop bg-[#010101]/20">
        <button>close</button>
    </form>
</dialog>

{{-- Modal: Delete Confirm --}}
<dialog id="modal-delete-employee" class="modal">
    <div class="modal-box max-w-sm bg-white rounded-2xl border border-[#e5e5e5] shadow-xl p-6">
        <div class="flex items-start gap-4 mb-6">
            <div class="w-12 h-12 rounded-xl bg-[#fee2e2]/50 flex items-center justify-center text-[#ef4444] shrink-0 border border-[#ef4444]/20">
                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="text-2xl"></iconify-icon>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-[#010101]">{{ __('ui.confirm_delete') }}?</h3>
                <p class="text-sm text-[#6b7280] mt-1 leading-relaxed">
                    {{ __('ui.employee') }} <strong id="delete-employee-name" class="text-[#010101]"></strong> {{ __('ui.confirm_delete_message') }}
                </p>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="button" class="flex-1 py-2.5 bg-[#f5f5f5] text-[#010101] rounded-xl text-sm font-medium transition-colors border border-[#e5e5e5]" onclick="AppModal.close('modal-delete-employee')">{{ __('ui.cancel') }}</button>
            <button type="button" id="btn-confirm-delete-employee" class="flex-1 py-2.5 bg-[#fee2e2] hover:bg-[#ef4444] hover:text-white text-[#ef4444] rounded-xl text-sm font-medium transition-colors shadow-sm active:scale-95">{{ __('ui.delete') }}</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop bg-[#010101]/20">
        <button>close</button>
    </form>
</dialog>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/employee.js') }}"></script>
@endpush
