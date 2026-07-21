<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Cinema Booking System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter & Sora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #9333ea;
            --primary-hover: #7c3aed;
            --primary-light: rgba(147, 51, 234, 0.08);
            --brand-color: #ff4d7d;
            --sidebar-width: 260px;
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-hover-bg: rgba(255, 255, 255, 0.04);
            --sidebar-hover-text: #f8fafc;
            --sidebar-active-bg: rgba(147, 51, 234, 0.12);
            --sidebar-active-text: #ffffff;
            --bg-base: #f8fafc;
            --bg-surface: #ffffff;
            --text-ink: #0f172a;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --border-hover: #cbd5e1;
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
        }

        /* ========== SIDEBAR ========== */
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
        }

        .sidebar-header p {
            margin: 0;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            font-weight: 600;
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
            border-radius: 8px;
            font-weight: 500;
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
            font-weight: 600;
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
            margin-right: 12px;
            font-size: 1rem;
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
            box-shadow: 0 4px 12px rgba(147, 51, 234, 0.25);
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
            box-shadow: 0 0 0 4px rgba(147, 51, 234, 0.12);
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
            background: rgba(255, 255, 255, 0.1);
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
            <h4><i class="fas fa-film"></i> Cinema</h4>
            <p>Admin Panel</p>
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
                <a href="{{ route('admin.dashboard') }}"
                   class="@if(request()->routeIs('admin.dashboard')) active @endif">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.cinemas.index') }}"
                   class="@if(request()->routeIs('admin.cinemas.*')) active @endif">
                    <i class="fas fa-building"></i>
                    <span>Cinemas</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.rooms.index') }}"
                   class="@if(request()->routeIs('admin.rooms.*')) active @endif">
                    <i class="fas fa-door-open"></i>
                    <span>Rooms</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.categories.index') }}"
                   class="@if(request()->routeIs('admin.categories.*')) active @endif">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.movies.index') }}"
                   class="@if(request()->routeIs('admin.movies.*')) active @endif">
                    <i class="fas fa-video"></i>
                    <span>Movies</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.showtimes.index') }}"
                   class="@if(request()->routeIs('admin.showtimes.*')) active @endif">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Showtimes</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.bookings.index') }}"
                   class="@if(request()->routeIs('admin.bookings.*')) active @endif">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reviews.index') }}"
                   class="@if(request()->routeIs('admin.reviews.*')) active @endif">
                    <i class="fas fa-comments"></i>
                    <span>Đánh Giá</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.coupons.index') }}"
                   class="@if(request()->routeIs('admin.coupons.*')) active @endif">
                    <i class="fas fa-tags"></i>
                    <span>Mã giảm giá</span>
                </a>
            </li>
            <li>
                <a href="#comboSubmenu" data-bs-toggle="collapse" class="@if(request()->routeIs('admin.combos.*') || request()->routeIs('admin.combo-reviews.*')) active @else text-white-50 @endif d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-utensils"></i>
                        <span>Combo Bắp Nước</span>
                    </div>
                    <i class="fas fa-chevron-down text-sm" style="font-size: 0.8em"></i>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('admin.combos.*') || request()->routeIs('admin.combo-reviews.*') ? 'show' : '' }}" id="comboSubmenu" style="background: rgba(0,0,0,0.1);">
                    <li>
                        <a href="{{ route('admin.combos.index') }}" class="@if(request()->routeIs('admin.combos.*')) active @endif" style="padding-left: 50px;">
                            <i class="fas fa-list me-2" style="font-size: 0.8rem; width: auto;"></i> Danh sách
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.combo-reviews.index') }}" class="@if(request()->routeIs('admin.combo-reviews.*')) active @endif" style="padding-left: 50px;">
                            <i class="fas fa-star me-2" style="font-size: 0.8rem; width: auto;"></i> Đánh giá
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#blogSubmenu" data-bs-toggle="collapse" class="@if(request()->routeIs('admin.posts.*') || request()->routeIs('admin.post-categories.*')) active @else text-white-50 @endif d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-newspaper"></i>
                        <span class="ms-1">Tin tức / Blog</span>
                    </div>
                    <i class="fas fa-chevron-down text-sm" style="font-size: 0.8em"></i>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('admin.posts.*') || request()->routeIs('admin.post-categories.*') ? 'show' : '' }}" id="blogSubmenu" style="background: rgba(0,0,0,0.1); margin: 0; padding: 0;">
                    <li>
                        <a href="{{ route('admin.posts.index') }}" class="@if(request()->routeIs('admin.posts.index') || request()->routeIs('admin.posts.create') || request()->routeIs('admin.posts.edit') || request()->routeIs('admin.posts.show')) active @endif" style="padding-left: 50px;">
                            <i class="fas fa-list me-2" style="font-size: 0.8rem; width: auto;"></i> Danh sách bài viết
                        </a>
                    </li>
                    @if(auth()->user()->isAdmin())
                    <li>
                        <a href="{{ route('admin.post-categories.index') }}" class="@if(request()->routeIs('admin.post-categories.*')) active @endif" style="padding-left: 50px;">
                            <i class="fas fa-folder me-2" style="font-size: 0.8rem; width: auto;"></i> Danh mục tin tức
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="@if(request()->routeIs('admin.users.*')) active @endif">
                    <i class="fas fa-users"></i>
                    <span>Người dùng</span>
                </a>
            </li>
        </ul>


    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOP BAR -->
        <div class="topbar">
            <h5>@yield('page_title', 'Dashboard')</h5>
            <div class="topbar-right">
                <div class="dropdown">
                    <div class="user-info dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                        <div class="text-end">
                            <small style="color: #999;">Welcome</small><br>
                            <strong style="color: #333;">{{ Auth::user()->name }}</strong>
                        </div>
                        <div class="user-avatar ms-2">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-circle me-2 text-primary"></i> Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
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
    @yield('extra_js')
</body>
</html>
