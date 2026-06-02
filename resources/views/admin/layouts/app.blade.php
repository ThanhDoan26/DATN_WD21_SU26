<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Cinema Booking System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #1e3c72;
            --sidebar-width: 250px;
            --sidebar-bg: #1e3c72;
            --sidebar-text: #fff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: var(--sidebar-text);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header h4 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .sidebar-header p {
            margin: 5px 0 0 0;
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-left-color: #ffc107;
        }

        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            border-left-color: #ffc107;
        }

        .sidebar-menu i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .sidebar-user {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 15px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background-color: rgba(0, 0, 0, 0.1);
        }

        .sidebar-user a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            border: none;
        }

        .sidebar-user a:hover {
            color: #fff;
            background: none;
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
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .topbar h5 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Content Area */
        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        /* Breadcrumb */
        .breadcrumb-custom {
            background: none;
            padding: 0 0 15px 0;
            margin: 0 0 20px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .breadcrumb-custom .breadcrumb {
            margin: 0;
            background: none;
        }

        .breadcrumb-custom .breadcrumb-item.active {
            color: #666;
            font-weight: 500;
        }

        /* Page Title */
        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1e3c72;
        }

        .page-title h2 {
            margin: 0;
            color: #1e3c72;
            font-weight: 700;
            font-size: 1.8rem;
        }

        .page-title .btn-group {
            display: flex;
            gap: 10px;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
            border: none;
        }

        /* Tables */
        .table {
            margin: 0;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #333;
            padding: 15px;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f9f9f9;
        }

        /* Stats Box */
        .stat-box {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
        }

        .stat-box .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e3c72;
        }

        .stat-box .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        /* Buttons */
        .btn-primary {
            background-color: #1e3c72;
            border-color: #1e3c72;
        }

        .btn-primary:hover {
            background-color: #2a5298;
            border-color: #2a5298;
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 0.85rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                position: fixed;
                left: -250px;
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
            }

            .topbar {
                padding: 10px 15px;
            }
        }

        /* Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
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
                <a href="{{ route('admin.seats.index') }}"
                   class="@if(request()->routeIs('admin.seats.*')) active @endif">
                    <i class="fas fa-chair"></i>
                    <span>Seats</span>
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
                <a href="{{ route('admin.users.index') }}"
                   class="@if(request()->routeIs('admin.users.*')) active @endif">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
        </ul>

        <!-- Sidebar User -->
        <div class="sidebar-user">
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-link" style="width: 100%; text-align: left; padding: 0;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOP BAR -->
        <div class="topbar">
            <h5>@yield('page_title', 'Dashboard')</h5>
            <div class="topbar-right">
                <div class="user-info">
                    <div>
                        <small style="color: #999;">Welcome</small><br>
                        <strong style="color: #333;">{{ Auth::user()->name }}</strong>
                    </div>
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="content">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('extra_js')
</body>
</html>
