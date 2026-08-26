<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cinema Staff Dashboard') - Cinema Booking System</title>
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('staff_theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }
        })();
    </script>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter & Sora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: #f59e0b;
            --primary-hover: #d97706;
            --primary-light: rgba(245, 158, 11, 0.08);
            --brand-color: #b45309;
            --sidebar-width: 260px;
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-hover-bg: rgba(255, 255, 255, 0.05);
            --sidebar-hover-text: #f8fafc;
            --sidebar-active-bg: rgba(245, 158, 11, 0.15);
            --sidebar-active-text: #ffffff;
            --bg-base: #f8fafc;
            --bg-surface: #ffffff;
            --text-ink: #0f172a;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --border-hover: #cbd5e1;
        }

        /* Dark Mode Variable Overrides */
        html.dark-theme {
            --bg-base: #0b0f19;
            --bg-surface: #131927;
            --text-ink: #f3f4f6;
            --text-muted: #9ca3af;
            --border-light: #1f2937;
            --border-hover: #374151;
        }

        html.dark-theme body {
            background-color: #0b0f19 !important;
            color: #f3f4f6 !important;
        }

        html.dark-theme .topbar {
            background-color: #131927 !important;
            border-bottom: 1px solid #1f2937 !important;
        }

        html.dark-theme .card,
        html.dark-theme .card-header {
            background-color: #131927 !important;
            border-color: #1f2937 !important;
            color: #f3f4f6 !important;
        }

        html.dark-theme .table-custom th {
            background-color: #1a2234 !important;
            color: #9ca3af !important;
            border-bottom-color: #1f2937 !important;
        }

        html.dark-theme .table-custom td {
            background-color: #131927 !important;
            border-bottom-color: #1f2937 !important;
            color: #f3f4f6 !important;
        }

        html.dark-theme .form-control,
        html.dark-theme .form-select {
            background-color: #1a2234 !important;
            border-color: #374151 !important;
            color: #f3f4f6 !important;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-base);
            color: var(--text-ink);
            display: flex;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1, h2, h3, h4, h5, h6, .font-sora {
            font-family: 'Sora', sans-serif !important;
        }

        /* Theme Toggle Button Style */
        .theme-toggle-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1px solid var(--border-light);
            background-color: var(--bg-surface);
            color: var(--text-ink);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .theme-toggle-btn:hover {
            transform: scale(1.12) rotate(15deg);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.25);
            border-color: var(--primary-color);
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-header h4 {
            margin: 0;
            font-family: 'Sora', sans-serif;
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header h4 i {
            color: var(--primary-color);
        }

        .sidebar-header p {
            margin: 0;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-muted);
            font-weight: 700;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 12px 80px 12px;
        }

        .sidebar-menu li {
            margin-bottom: 4px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .sidebar-menu a:hover {
            background-color: var(--sidebar-hover-bg);
            color: var(--sidebar-hover-text);
        }

        .sidebar-menu a.active {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            font-weight: 700;
        }

        .sidebar-menu i {
            width: 22px;
            text-align: center;
            margin-right: 12px;
            font-size: 1rem;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: var(--bg-surface);
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 72px;
        }

        .topbar h5 {
            margin: 0;
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            color: var(--text-ink);
            font-size: 1.15rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-family: 'Sora', sans-serif;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.25);
        }

        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        /* GLOBAL FORM CONTROL HARMONIZATION */
        .form-control,
        .form-select {
            border-radius: 12px !important;
            border: 1px solid var(--border-light) !important;
            padding: 9px 14px !important;
            font-family: 'Sora', sans-serif !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            color: var(--text-ink) !important;
            background-color: var(--bg-surface) !important;
            transition: all 0.2s ease-in-out !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02) !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 4px var(--primary-light) !important;
            outline: none !important;
        }

        .input-group .form-control,
        .input-group .form-select {
            border-radius: 0 !important;
        }

        .input-group > :first-child {
            border-top-left-radius: 12px !important;
            border-bottom-left-radius: 12px !important;
        }

        .input-group > :last-child {
            border-top-right-radius: 12px !important;
            border-bottom-right-radius: 12px !important;
        }
    </style>

    @yield('extra_css')
    @stack('styles')
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-ticket-alt"></i> MovieGo</h4>
            <p>Staff Panel</p>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ url('/') }}">
                    <i class="fas fa-home"></i>
                    <span>Trang Chủ Client</span>
                </a>
            </li>
            <li>
                <a href="{{ route('staff.ticket.search') }}"
                   class="@if(request()->routeIs('staff.ticket.search')) active @endif">
                    <i class="fas fa-search"></i>
                    <span>Tra cứu & Check-in vé</span>
                </a>
            </li>
            <li>
                <a href="{{ route('staff.walkin.movies') }}"
                   class="@if(request()->routeIs('staff.walkin.*')) active @endif">
                    <i class="fas fa-cash-register"></i>
                    <span>Tạo vé tại quầy POS</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOP BAR -->
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <h5 class="mb-0">@yield('page_title', 'Staff POS Portal')</h5>
                @if(Auth::user()->cinema && !empty(Auth::user()->cinema->name))
                    <span class="badge bg-warning-subtle text-amber border border-warning-subtle px-3 py-1 rounded-pill fw-bold small font-sora" style="background-color: rgba(245, 158, 11, 0.12); color: #d97706;">
                        <i class="fas fa-building me-1"></i> {{ Auth::user()->cinema->name }}
                    </span>
                @endif
            </div>

            <div class="topbar-right d-flex align-items-center">
                <!-- Theme Switcher Toggle -->
                <button type="button" class="theme-toggle-btn me-3" id="themeToggleBtn" onclick="toggleStaffTheme()" title="Chuyển đổi Chế độ Sáng / Tối">
                    <i class="fas fa-moon" id="themeToggleIcon"></i>
                </button>

                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                        <div class="text-end">
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Staff</small>
                            <strong class="font-sora text-ink fs-6">{{ Auth::user()->name ?? 'Staff' }}</strong>
                        </div>
                        <div class="user-avatar ms-2">
                            {{ strtoupper(substr(Auth::user()->name ?? 'S', 0, 1)) }}
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2" aria-labelledby="userDropdown">
                        <li>
                            <a href="{{ route('profile.edit') }}" class="dropdown-item py-2 fw-semibold">
                                <i class="fas fa-user-circle me-2 text-warning"></i> Hồ sơ cá nhân
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger fw-semibold">
                                    <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CONTENT AREA -->
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleStaffTheme() {
            const isDark = document.documentElement.classList.toggle('dark-theme');
            if (isDark) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                localStorage.setItem('staff_theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-bs-theme');
                localStorage.setItem('staff_theme', 'light');
            }
            updateThemeIcon(isDark);
        }

        function updateThemeIcon(isDark) {
            const icon = document.getElementById('themeToggleIcon');
            if (icon) {
                icon.className = isDark ? 'fas fa-sun text-warning' : 'fas fa-moon text-dark';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark-theme');
            updateThemeIcon(isDark);
        });
    </script>
    @stack('scripts')
    @yield('extra_js')
</body>
</html>
