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

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    @include('layouts.guest-footer')

    @stack('scripts')
</body>
</html>
