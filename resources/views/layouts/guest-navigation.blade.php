<nav class="fixed w-full z-50 glass-nav transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="/" class="flex items-center gap-2 group">
                    <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center text-white font-bold text-xl group-hover:scale-105 transition-transform">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <span class="font-bold text-2xl tracking-tight text-white">movie<span class="text-primary">Go</span></span>
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="/" class="{{ request()->is('/') ? 'text-primary font-bold' : 'text-slate-300 hover:text-white font-medium' }} transition-colors">Trang chủ</a>
                <a href="{{ route('movies.current') }}" class="{{ request()->routeIs('movies.current') ? 'text-primary font-bold' : 'text-slate-300 hover:text-white font-medium' }} transition-colors">Phim Đang Chiếu</a>
                <a href="{{ route('movies.upcoming') }}" class="{{ request()->routeIs('movies.upcoming') ? 'text-primary font-bold' : 'text-slate-300 hover:text-white font-medium' }} transition-colors">Phim Sắp Chiếu</a>
                <a href="{{ route('posts.index') }}" class="{{ request()->routeIs('posts.*') ? 'text-primary font-bold' : 'text-slate-300 hover:text-white font-medium' }} transition-colors">Tin tức</a>
                @auth
                    <a href="{{ route('booking.history') }}" class="{{ request()->routeIs('booking.history*') ? 'text-primary font-bold' : 'text-slate-300 hover:text-white font-medium' }} transition-colors">Lịch sử đặt vé</a>
                @endauth
            </div>

            <!-- Auth / User Actions -->
            <div class="hidden md:flex items-center space-x-4">
                @if (Route::has('login'))
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="text-slate-300 hover:text-white transition-colors font-medium"><i class="fas fa-user-shield me-1"></i> Trang quản trị</a>
                        @elseif(auth()->user()->isManager())
                            <a href="{{ route('manager.dashboard') }}" class="text-slate-300 hover:text-white transition-colors font-medium"><i class="fas fa-user-shield me-1"></i> Trang quản lý</a>
                        @elseif(auth()->user()->isStaff())
                            <a href="{{ route('staff.dashboard') }}" class="text-[#ca8a04] hover:text-[#eab308] transition-colors font-medium"><i class="fas fa-user-shield me-1"></i> Trang nhân viên</a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-primary hover:bg-red-700 text-white px-5 py-2.5 rounded-full font-medium transition-all transform hover:scale-105 shadow-lg shadow-red-500/30">
                                Đăng xuất
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'text-primary font-bold' : 'text-slate-300 hover:text-white font-medium' }} transition-colors">Đăng nhập</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-primary hover:bg-red-700 text-white px-5 py-2.5 rounded-full font-medium transition-all transform hover:scale-105 shadow-lg shadow-red-500/30">
                                Đăng ký
                            </a>
                        @endif
                    @endauth
                @endif
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button class="text-slate-300 hover:text-white focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<style>
    .glass-nav {
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
</style>
