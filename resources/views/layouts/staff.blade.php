<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cinema Staff Dashboard')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #854d0e; /* Yellow/Brown base for staff */
            --sidebar-width: 250px;
            --sidebar-bg: #854d0e;
            --sidebar-text: #fff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #a16207 0%, #ca8a04 100%);
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
        .sidebar-header h4 { margin: 0; font-size: 1.3rem; font-weight: 700; color: #fff; }
        .sidebar-header p { margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9; color: #fff; }
        .sidebar-menu { list-style: none; padding: 20px 0 80px 0; }
        .sidebar-menu li { margin: 0; }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            border-left-color: #fff;
        }
        .sidebar-menu i { width: 25px; text-align: center; margin-right: 10px; font-size: 1.1rem; }
        
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
        }
        .topbar {
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }
        .topbar h5 { margin: 0; font-weight: 600; color: #333; }
        .topbar-right { display: flex; align-items: center; gap: 20px; }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #a16207 0%, #ca8a04 100%);
            color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;
        }
        .content { flex: 1; padding: 30px; overflow-y: auto; }
        
        @media (max-width: 768px) {
            .sidebar { width: 0; left: -250px; }
            .sidebar.show { left: 0; width: var(--sidebar-width); }
            .main-content { margin-left: 0; width: 100%; }
            .content { padding: 15px; }
        }
    </style>
    @yield('extra_css')
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-ticket-alt"></i> Cinema</h4>
            <p>Staff Panel</p>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('staff.dashboard') }}" class="@if(request()->routeIs('staff.dashboard')) active @endif">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('staff.ticket.search') }}" class="@if(request()->routeIs('staff.ticket.search')) active @endif">
                    <i class="fas fa-ticket-alt"></i> <span>Tra cứu & Check-in vé</span>
                </a>
            </li>
        </ul>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <h5>@yield('page_title', 'Dashboard')</h5>
            <div class="topbar-right">
                <div class="dropdown">
                    <div class="user-info dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                        <div class="text-end">
                            <small style="color: #999;">Welcome</small><br>
                            <strong style="color: #333;">{{ Auth::user()->name ?? 'Staff' }}</strong>
                        </div>
                        <div class="user-avatar ms-2">
                            {{ strtoupper(substr(Auth::user()->name ?? 'S', 0, 1)) }}
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
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
