@extends('layouts.auth')

@section('content')
<div class="bg-[#ffffff] p-8 rounded-2xl border border-[#e5e5e5] shadow-sm">

    {{-- Brand --}}
    <div class="text-center mb-8">
        <div class="w-14 h-14 rounded-xl bg-[#edcc94] mx-auto mb-4 flex items-center justify-center">
            <span class="text-[#010101] font-bold text-2xl">V</span>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight text-[#010101]">{{ __('ui.welcome_back') }}</h1>
        <p class="text-sm text-[#6b7280] mt-2">{{ __('ui.signin_to_account') }}</p>
    </div>

    {{-- Error alert --}}
    @if($errors->any())
    <div class="mb-4 p-4 bg-[#fee2e2] border border-[#fecaca] rounded-xl flex items-start gap-3">
        <iconify-icon icon="solar:danger-triangle-linear" stroke-width="1.5" class="text-[#ef4444] text-xl shrink-0 mt-0.5"></iconify-icon>
        <p class="text-sm text-[#ef4444] flex-1">{{ $errors->first() }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-[#010101] mb-2">{{ __('ui.employee_email') }}</label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-3 text-sm text-[#010101] placeholder-[#6b7280] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors @error('email') border-[#fecaca] @enderror"
                placeholder="owner@volare.com"
                required
                autofocus
            >
        </div>

        <div>
            <label class="block text-sm font-medium text-[#010101] mb-2">{{ __('ui.employee_password') }}</label>
            <input
                type="password"
                name="password"
                class="w-full bg-[#f5f5f5]/50 border border-[#e5e5e5] rounded-xl px-4 py-3 text-sm text-[#010101] placeholder-[#6b7280] focus:outline-none focus:border-[#c9a96e] focus:bg-white transition-colors"
                placeholder="••••••••"
                required
            >
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-[#e5e5e5] text-[#c9a96e] focus:ring-[#c9a96e] focus:ring-offset-0">
                <span class="text-sm text-[#6b7280]">{{ __('ui.remember_me') }}</span>
            </label>
        </div>

        <button type="submit" class="w-full py-3 bg-[#edcc94] hover:bg-[#c9a96e] text-[#010101] rounded-xl text-sm font-medium transition-colors shadow-sm">
            {{ __('ui.sign_in') }}
        </button>
    </form>

    <div class="mt-6 text-center">
        <p class="text-sm text-[#6b7280]">
            {{ __('ui.forgot_password') }}? <a href="#" class="text-[#c9a96e] hover:text-[#010101] font-medium transition-colors">{{ __('ui.reset_it_here') }}</a>
        </p>
    </div>
</div>
@endsection
