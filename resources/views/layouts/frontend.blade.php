<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'movieGo - Đỉnh Cao Điện Ảnh')</title>
    @stack('meta')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Fallback if Vite is not running, using CDN for preview -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Outfit', 'sans-serif'],
                        },
                        colors: {
                            primary: '#e50914',
                        }
                    }
                }
            }
        </script>
    @endif

    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="{{ $bodyClass ?? 'bg-slate-900 text-white antialiased selection:bg-primary selection:text-white' }}">

    <!-- Navigation Bar -->
    @include('layouts.guest-navigation')

    @if(request()->routeIs('home'))
        @if(isset($activePendingBooking) && $activePendingBooking)
            @include('components.booking.active-indicator', ['booking' => $activePendingBooking])
        @elseif(session('show_active_booking_modal'))
            {{-- Fallback if for some reason the booking was passed via session only, but usually activePendingBooking is global --}}
            @include('components.booking.active-indicator', ['booking' => null])
        @endif
    @endif

    <!-- Global Toast Notifications -->
    @if(session('success') || session('info') || session('error'))
        <div id="global-toast" class="fixed top-24 right-5 z-[99999] max-w-sm w-full bg-slate-900/95 border {{ session('error') ? 'border-red-500/50 text-red-300' : (session('info') ? 'border-blue-500/50 text-blue-300' : 'border-emerald-500/50 text-emerald-300') }} p-4 rounded-2xl shadow-2xl backdrop-blur-md flex items-center justify-between gap-3 transform transition-all duration-500">
            <div class="flex items-center gap-3">
                <i class="fas {{ session('error') ? 'fa-exclamation-circle text-red-500' : (session('info') ? 'fa-info-circle text-blue-400' : 'fa-check-circle text-emerald-500') }} text-xl"></i>
                <p class="text-sm font-medium text-white">{{ session('success') ?? session('info') ?? session('error') }}</p>
            </div>
            <button onclick="document.getElementById('global-toast').remove()" class="text-slate-400 hover:text-white transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('global-toast');
                if (toast) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-20px)';
                    setTimeout(() => toast.remove(), 500);
                }
            }, 4000);
        </script>
    @endif

    <!-- Main Content -->
    <main class="pt-20">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('layouts.guest-footer')

    <!-- AI Chatbot Widget -->
    @include('components.chatbot.widget')

    @stack('scripts')
</body>
</html>
