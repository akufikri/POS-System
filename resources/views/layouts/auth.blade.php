<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="{{ __('ui.login_description') }}">
    <meta property="og:title" content="{{ __('ui.login_title') }} — {{ config('app.name', 'Volare POS') }}">
    <meta property="og:description" content="{{ __('ui.login_description') }}">
    <meta property="og:type" content="website">
    <title>{{ __('ui.login_title') }} — {{ config('app.name', 'Volare POS') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5.0.0-beta.1/daisyui.css" rel="stylesheet" type="text/css" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#edcc94',
                        secondary: '#c9a96e',
                    }
                }
            }
        }
    </script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f5f5;
            color: #010101;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center bg-[#f5f5f5] relative">
    
    {{-- Language Dropdown --}}
    <div class="fixed top-6 right-6 z-50">
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="flex items-center bg-white border border-[#e5e5e5] rounded-xl px-4 py-2 gap-3 shadow-sm hover:border-[#c9a96e] transition-all group">
                <iconify-icon icon="solar:global-linear" class="text-lg text-[#6b7280] group-hover:text-[#c9a96e] transition-colors"></iconify-icon>
                <span class="text-[11px] font-bold uppercase tracking-widest text-[#010101]">{{ app()->getLocale() }}</span>
                <iconify-icon icon="solar:alt-arrow-down-linear" class="text-[10px] text-[#6b7280]"></iconify-icon>
            </div>
            <ul tabindex="0" class="dropdown-content z-[30] menu p-2 shadow-xl bg-white rounded-2xl w-48 border border-[#e5e5e5] mt-2 animate-in fade-in slide-in-from-top-2 duration-200">
                <li>
                    <a href="{{ route('lang.switch', 'id') }}" class="flex items-center justify-between py-2.5 rounded-xl {{ app()->getLocale() == 'id' ? 'bg-[#f5f5f5] text-[#010101] font-bold' : 'text-[#6b7280]' }}">
                        <span class="flex items-center gap-2 uppercase tracking-wider text-[11px]">ID — Indonesia</span>
                        @if(app()->getLocale() == 'id')
                            <iconify-icon icon="solar:check-circle-bold" class="text-[#c9a96e]"></iconify-icon>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('lang.switch', 'en') }}" class="flex items-center justify-between py-2.5 rounded-xl {{ app()->getLocale() == 'en' ? 'bg-[#f5f5f5] text-[#010101] font-bold' : 'text-[#6b7280]' }}">
                        <span class="flex items-center gap-2 uppercase tracking-wider text-[11px]">EN — English</span>
                        @if(app()->getLocale() == 'en')
                            <iconify-icon icon="solar:check-circle-bold" class="text-[#c9a96e]"></iconify-icon>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="w-full max-w-md px-4">
        @yield('content')
    </div>

    {{-- jQuery CDN --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    </script>
    @stack('scripts')
</body>
</html>
