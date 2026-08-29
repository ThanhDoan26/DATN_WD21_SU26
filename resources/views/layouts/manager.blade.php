<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Manager Dashboard') - Cinema Booking System</title>
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('admin_theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }
        })();
    </script>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (jsDelivr + cdnjs fallback) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Fonts Inter & Sora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* FontAwesome Fix for Icon Display */
        .fa, .fas, .far, .fal, .fad, .fab, [class*=" fa-"], [class^="fa-"] {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands", "FontAwesome" !important;
            font-style: normal !important;
        }
        .far, .fa-regular { font-weight: 400 !important; }
        .fas, .fa-solid { font-weight: 900 !important; }
        .fab, .fa-brands { font-family: "Font Awesome 6 Brands" !important; font-weight: 400 !important; }

        :root {
            --primary-color: #0d9488;
            --primary-hover: #0f766e;
            --primary-light: rgba(13, 148, 136, 0.08);
            --brand-color: #06b6d4;
            --sidebar-width: 260px;
            --sidebar-bg: #052120;
            --sidebar-text: #99f6e4;
            --sidebar-hover-bg: rgba(45, 212, 191, 0.1);
            --sidebar-hover-text: #ffffff;
            --sidebar-active-bg: linear-gradient(135deg, #0d9488 0%, #0891b2 100%);
            --sidebar-active-text: #ffffff;
            --bg-base: #f8fafc;
            --bg-surface: #ffffff;
            --text-ink: #0f172a;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --border-hover: #cbd5e1;
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .theme-toggle-btn:hover {
            transform: scale(1.12) rotate(15deg);
            box-shadow: 0 6px 16px rgba(13, 148, 136, 0.25);
            border-color: var(--primary-color);
        }

        /* Light Mode High-Contrast & Font Clarity Overrides */
        label, .form-label {
            color: #1e293b !important;
            font-weight: 600 !important;
            letter-spacing: -0.01em;
        }

        .form-control, .form-select, input, select, textarea {
            color: #0f172a !important;
            font-weight: 500;
            border-color: #cbd5e1;
        }

        .form-control::placeholder, .form-select::placeholder, input::placeholder {
            color: #64748b !important;
        }

        input:disabled, select:disabled, .form-control:disabled, .form-select:disabled, [readonly] {
            color: #334155 !important;
            background-color: #f1f5f9 !important;
            opacity: 1 !important;
            font-weight: 600 !important;
            border-color: #e2e8f0 !important;
        }

        .text-muted, small, .form-text, .form-hint, .card-subtitle {
            color: #475569 !important;
        }

        /* Dark Mode Variable Overrides */
        html.dark-theme {
            --bg-base: #0b0f19;
            --bg-surface: #131927;
            --text-ink: #f3f4f6;
            --text-muted: #cbd5e1;
            --border-light: #1f2937;
            --border-hover: #374151;
        }

        html.dark-theme body {
            background-color: #0b0f19 !important;
            color: #f3f4f6 !important;
        }

        html.dark-theme label,
        html.dark-theme .form-label {
            color: #f1f5f9 !important;
            font-weight: 600 !important;
        }

        html.dark-theme .topbar {
            background-color: #131927 !important;
            border-bottom: 1px solid #1f2937 !important;
        }

        html.dark-theme .topbar h5,
        html.dark-theme .user-info strong {
            color: #f3f4f6 !important;
        }

        html.dark-theme .card,
        html.dark-theme .card-header {
            background-color: #131927 !important;
            border-color: #1f2937 !important;
            color: #f3f4f6 !important;
        }

        html.dark-theme .card-header {
            border-bottom-color: #1f2937 !important;
        }

        html.dark-theme .form-control,
        html.dark-theme .form-select {
            background-color: #1a2234 !important;
            border-color: #374151 !important;
            color: #f3f4f6 !important;
        }

        html.dark-theme input:disabled,
        html.dark-theme select:disabled,
        html.dark-theme .form-control:disabled,
        html.dark-theme .form-select:disabled,
        html.dark-theme [readonly] {
            color: #cbd5e1 !important;
            background-color: #1e293b !important;
            opacity: 1 !important;
            border-color: #334155 !important;
        }

        html.dark-theme .table {
            color: #f3f4f6 !important;
            border-color: #1f2937 !important;
        }

        html.dark-theme .table td,
        html.dark-theme .table th {
            background-color: #131927 !important;
            border-color: #1f2937 !important;
            color: #f3f4f6 !important;
        }

        html.dark-theme .table-hover tbody tr:hover td {
            background-color: #1a2234 !important;
        }

        html.dark-theme .dropdown-menu {
            background-color: #131927 !important;
            border-color: #374151 !important;
        }

        html.dark-theme .dropdown-item {
            color: #e5e7eb !important;
        }

        html.dark-theme .dropdown-item:hover {
            background-color: rgba(13, 148, 136, 0.2) !important;
            color: #ffffff !important;
        }

        html.dark-theme strong,
        html.dark-theme h1, html.dark-theme h2, html.dark-theme h3, html.dark-theme h4, html.dark-theme h5, html.dark-theme h6 {
            color: #f3f4f6 !important;
        }

        html.dark-theme .bg-light {
            background-color: #1a2234 !important;
        }

        html.dark-theme .text-dark {
            color: #f3f4f6 !important;
        }

        html.dark-theme .list-group-item {
            background-color: transparent !important;
            border-color: #1f2937 !important;
            color: #cbd5e1 !important;
        }

        html.dark-theme .list-group-item strong {
            color: #e5e7eb !important;
        }

        html.dark-theme .text-muted {
            color: #cbd5e1 !important;
        }

        html.dark-theme .card-text.text-muted {
            color: #cbd5e1 !important;
        }

        /* Utility overrides to sync Bootstrap colors with Brand Design System */
        .text-primary {
            color: var(--primary-color) !important;
        }
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        .border-primary {
            border-color: var(--primary-color) !important;
        }
        .badge.bg-primary {
            background-color: var(--primary-color) !important;
            color: #ffffff !important;
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
            text-rendering: optimizeLegibility;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #052120 0%, #031615 100%);
            color: var(--sidebar-text);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid rgba(45, 212, 191, 0.15);
            z-index: 1000;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.25);
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(45, 212, 191, 0.15);
            background: rgba(0, 0, 0, 0.18);
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-header h4 {
            margin: 0;
            font-family: 'Sora', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header h4 i {
            color: var(--brand-color);
            filter: drop-shadow(0 0 8px rgba(6, 182, 212, 0.6));
        }

        .sidebar-header .manager-tag-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
        }

        .sidebar-header .manager-badge {
            background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%);
            color: #ffffff;
            font-size: 0.68rem;
            letter-spacing: 0.8px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(13, 148, 136, 0.4);
            border: none;
        }

        .sidebar-header p {
            margin: 0;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #5eead4;
            font-weight: 600;
            opacity: 0.95;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 12px 80px 12px;
        }

        .sidebar-menu li {
            margin-bottom: 6px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #99f6e4;
            opacity: 0.85;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.25s ease;
        }

        .sidebar-menu a:hover {
            background-color: rgba(45, 212, 191, 0.1);
            color: #ffffff;
            opacity: 1;
            transform: translateX(3px);
        }

        .sidebar-menu a.active {
            background: linear-gradient(135deg, #0d9488 0%, #0891b2 100%) !important;
            color: #ffffff !important;
            font-weight: 700;
            opacity: 1;
            box-shadow: 0 4px 14px rgba(13, 148, 136, 0.4);
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
            margin-right: 12px;
            font-size: 1rem;
            color: #2dd4bf;
            transition: all 0.2s ease;
        }

        .sidebar-menu a.active i {
            color: #ffffff !important;
            filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.6));
        }

        .sidebar-menu a:hover i {
            color: #5eead4;
            transform: scale(1.1);
        }

        .sidebar-menu .collapse a {
            padding-left: 48px !important;
            font-size: 0.85rem;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
        }

        /* Header/Top Bar */
        .topbar {
            background: var(--bg-surface);
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .topbar h5 {
            margin: 0;
            font-family: 'Sora', sans-serif;
            font-weight: 600;
            color: var(--text-ink);
            font-size: 1.1rem;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topbar-right .dropdown:hover .dropdown-menu {
            display: block;
            margin-top: 0;
        }

        .topbar-right .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-right .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--brand-color) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-family: 'Sora', sans-serif;
        }

        /* Content Area & Page Load Transition */
        @keyframes pageFadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            animation: pageFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @media (prefers-reduced-motion: reduce) {
            .content {
                animation: pageFadeInReduced 0.1s linear forwards;
            }
            @keyframes pageFadeInReduced {
                from { opacity: 0; }
                to { opacity: 1; }
            }
        }

        /* Breadcrumb */
        .breadcrumb-custom {
            background: none;
            padding: 0 0 15px 0;
            margin: 0 0 20px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .breadcrumb-custom .breadcrumb {
            margin: 0;
            background: none;
        }

        .breadcrumb-custom .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .breadcrumb-custom .breadcrumb-item a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .breadcrumb-custom .breadcrumb-item.active {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Page Title */
        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-light);
        }

        .page-title h2 {
            margin: 0;
            color: var(--text-ink);
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-title h2 i {
            color: var(--primary-color);
        }

        .page-title .btn-group {
            display: flex;
            gap: 10px;
        }

        /* Cards */
        .card {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            transition: all 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: #ffffff;
            color: var(--text-ink);
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light);
            border-radius: 12px 12px 0 0 !important;
            font-family: 'Sora', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
        }

        /* Tables */
        .table {
            margin: 0;
        }

        .table thead th {
            background-color: var(--bg-base);
            border-bottom: 1px solid var(--border-light);
            font-family: 'Sora', sans-serif;
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px;
        }

        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            font-size: 0.9rem;
            color: #334155;
            border-bottom: 1px solid var(--border-light);
        }

        .table tbody tr:hover {
            background-color: var(--bg-base);
        }

        /* Stats Box */
        .stat-box {
            background: var(--bg-surface);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            text-align: center;
            margin-bottom: 20px;
        }

        .stat-box .stat-number {
            font-size: 2rem;
            font-weight: 700;
            font-family: 'Sora', sans-serif;
            color: var(--primary-color);
        }

        .stat-box .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 10px;
            font-weight: 500;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #ffffff;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);
            color: #ffffff;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Forms & Inputs */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-light);
            padding: 10px 14px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            color: var(--text-ink);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.12);
            outline: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                position: fixed;
                left: -260px;
                height: 100vh;
                transition: left 0.3s ease;
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .content {
                padding: 15px;
            }

            .page-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .topbar {
                padding: 10px 15px;
            }
        }

        /* Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(45, 212, 191, 0.25);
            border-radius: 3px;
        }

        /* Alert Box Styles */
        .alert {
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.9rem;
            font-weight: 500;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .alert-success {
            background-color: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        .alert-danger {
            background-color: #fef2f2;
            border-color: #fca5a5;
            color: #991b1b;
        }
    </style>

    @yield('extra_css')
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <h4><i class="fas fa-film"></i> MovieGo</h4>
            <div class="manager-tag-wrapper">
                <span class="manager-badge">MANAGER</span>
                <p>Quản lý rạp</p>
            </div>
            <div class="cinema-info-tag mt-2 d-flex align-items-center text-truncate" style="color: #99f6e4; font-size: 0.78rem; font-weight: 500;" title="{{ Auth::user()->cinema->name ?? 'Chưa phân công rạp' }}">
                <i class="fas fa-building me-1 text-info"></i>
                <span class="text-truncate">{{ Auth::user()->cinema->name ?? 'Chưa phân công rạp' }}</span>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <li>
                <a href="{{ url('/') }}">
                    <i class="fas fa-home"></i>
                    <span>Trang Chủ</span>
                </a>
            </li>
            <li>
                <a href="{{ route('manager.dashboard') }}"
                   class="@if(request()->routeIs('manager.dashboard')) active @endif">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Bảng điều khiển</span>
                </a>
            </li>
            <li>
                <a href="{{ route('manager.rooms.index') }}"
                   class="@if(request()->routeIs('manager.rooms.*')) active @endif">
                    <i class="fas fa-door-open"></i>
                    <span>Phòng chiếu</span>
                </a>
            </li>
            <li>
                <a href="{{ route('manager.showtimes.index') }}"
                   class="@if(request()->routeIs('manager.showtimes.*')) active @endif">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Lịch chiếu</span>
                </a>
            </li>
            <li>
                <a href="{{ route('manager.combos.index') }}"
                   class="@if(request()->routeIs('manager.combos.*')) active @endif">
                    <i class="fas fa-utensils"></i>
                    <span>Combo Bắp Nước</span>
                </a>
            </li>
            <li>
                <a href="{{ route('manager.movies.index') }}"
                   class="@if(request()->routeIs('manager.movies.*')) active @endif">
                    <i class="fas fa-video"></i>
                    <span>Phim</span>
                </a>
            </li>
            <li>
                <a href="{{ route('manager.coupons.index') }}"
                   class="@if(request()->routeIs('manager.coupons.*')) active @endif">
                    <i class="fas fa-tags"></i>
                    <span>Mã giảm giá</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOP BAR -->
        <div class="topbar">
            <h5>@yield('page_title', 'Bảng điều khiển')</h5>
            <div class="topbar-right d-flex align-items-center">
                <button type="button" class="theme-toggle-btn me-3" id="themeToggleBtn" onclick="toggleAdminTheme()" title="Chuyển đổi Chế độ Sáng / Tối">
                    <i class="fas fa-moon" id="themeToggleIcon"></i>
                </button>
                <div class="dropdown">
                    <div class="user-info dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                        <div class="text-end me-1">
                            <div class="d-flex align-items-center justify-content-end gap-1 mb-1">
                                <span class="badge" style="background: rgba(13, 148, 136, 0.15); color: #0d9488; font-size: 0.65rem; font-weight: 700; padding: 2px 6px; border-radius: 4px;">MANAGER</span>
                            </div>
                            <strong style="color: var(--text-ink); font-size: 0.88rem;">{{ Auth::user()->name ?? 'Manager' }}</strong>
                            <div class="small text-muted d-flex align-items-center justify-content-end" style="font-size: 0.73rem;">
                                <i class="fas fa-building me-1 text-info" style="font-size: 0.7rem;"></i>
                                <span>{{ Auth::user()->cinema->name ?? 'Chưa gán rạp' }}</span>
                            </div>
                        </div>
                        <div class="user-avatar ms-2">
                            {{ strtoupper(substr(Auth::user()->name ?? 'M', 0, 1)) }}
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-circle me-2 text-primary"></i> Hồ sơ cá nhân
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function toggleAdminTheme() {
            const isDark = document.documentElement.classList.toggle('dark-theme');
            if (isDark) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                localStorage.setItem('admin_theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-bs-theme');
                localStorage.setItem('admin_theme', 'light');
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
