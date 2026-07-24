<nav id="main-nav" class="fixed w-full z-50 transition-all duration-500" style="background: linear-gradient(180deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 70%, transparent 100%); backdrop-filter: blur(0px);">
    <div class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-16">
        <div class="flex justify-between items-center py-5 lg:py-6">

            <!-- Logo -->
            <a href="/" class="flex items-center gap-3 group flex-shrink-0">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center text-white font-bold shadow-lg shadow-red-500/30 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-ticket-alt text-sm"></i>
                </div>
                <span class="font-bold text-xl sm:text-2xl tracking-tight text-white">
                    movie<span class="text-red-500">Go</span>
                </span>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-8">
                <a href="/" class="{{ request()->is('/') ? 'text-white font-semibold' : 'text-white/70 hover:text-white font-medium' }} transition-colors text-sm tracking-wide relative group">
                    Trang chủ
                    <span class="absolute -bottom-1 left-0 h-px bg-red-500 transition-all duration-300 {{ request()->is('/') ? 'w-full' : 'w-0 group-hover:w-full' }}"></span>
                </a>
                <a href="{{ route('movies.current') }}" class="{{ request()->routeIs('movies.current') ? 'text-white font-semibold' : 'text-white/70 hover:text-white font-medium' }} transition-colors text-sm tracking-wide relative group">
                    Phim Đang Chiếu
                    <span class="absolute -bottom-1 left-0 h-px bg-red-500 transition-all duration-300 {{ request()->routeIs('movies.current') ? 'w-full' : 'w-0 group-hover:w-full' }}"></span>
                </a>
                <a href="{{ route('movies.upcoming') }}" class="{{ request()->routeIs('movies.upcoming') ? 'text-white font-semibold' : 'text-white/70 hover:text-white font-medium' }} transition-colors text-sm tracking-wide relative group">
                    Phim Sắp Chiếu
                    <span class="absolute -bottom-1 left-0 h-px bg-red-500 transition-all duration-300 {{ request()->routeIs('movies.upcoming') ? 'w-full' : 'w-0 group-hover:w-full' }}"></span>
                </a>
                <a href="{{ route('posts.index') }}" class="{{ request()->routeIs('posts.*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white font-medium' }} transition-colors text-sm tracking-wide relative group">
                    Tin tức
                    <span class="absolute -bottom-1 left-0 h-px bg-red-500 transition-all duration-300 {{ request()->routeIs('posts.*') ? 'w-full' : 'w-0 group-hover:w-full' }}"></span>
                </a>
                @auth
                    <a href="{{ route('booking.history') }}" class="{{ request()->routeIs('booking.history*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white font-medium' }} transition-colors text-sm tracking-wide relative group">
                        Lịch sử đặt vé
                        <span class="absolute -bottom-1 left-0 h-px bg-red-500 transition-all duration-300 {{ request()->routeIs('booking.history*') ? 'w-full' : 'w-0 group-hover:w-full' }}"></span>
                    </a>
                @endauth
            </div>

            <!-- Auth Actions -->
            <div class="hidden md:flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="text-white/70 hover:text-white transition-colors text-sm font-medium flex items-center gap-1.5">
                                <i class="fas fa-user-shield text-xs"></i> Quản trị
                            </a>
                        @elseif(auth()->user()->isManager())
                            <a href="{{ route('manager.dashboard') }}" class="text-white/70 hover:text-white transition-colors text-sm font-medium flex items-center gap-1.5">
                                <i class="fas fa-user-shield text-xs"></i> Quản lý
                            </a>
                        @elseif(auth()->user()->isStaff())
                            <a href="{{ route('staff.dashboard') }}" class="text-yellow-400 hover:text-yellow-300 transition-colors text-sm font-medium flex items-center gap-1.5">
                                <i class="fas fa-user-shield text-xs"></i> Nhân viên
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-5 py-2 rounded-full font-medium text-sm transition-all hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-0.5">
                                Đăng xuất
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-white/70 hover:text-white transition-colors text-sm font-medium">
                            Đăng nhập
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-red-600 hover:bg-red-500 text-white px-5 py-2 rounded-full font-medium text-sm transition-all hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-0.5">
                                Đăng ký
                            </a>
                        @endif
                    @endauth
                @endif
            </div>

            <!-- Mobile Hamburger -->
            <button id="mobile-menu-btn" class="md:hidden flex flex-col gap-1.5 p-2 group" aria-label="Menu">
                <span class="w-6 h-0.5 bg-white rounded-full transition-all duration-300 group-[.open]:rotate-45 group-[.open]:translate-y-2"></span>
                <span class="w-6 h-0.5 bg-white rounded-full transition-all duration-300 group-[.open]:opacity-0"></span>
                <span class="w-4 h-0.5 bg-white rounded-full transition-all duration-300 group-[.open]:-rotate-45 group-[.open]:-translate-y-2"></span>
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Menu Overlay -->
<div id="mobile-menu" class="fixed inset-0 z-[60] bg-black/97 backdrop-blur-lg opacity-0 invisible transition-all duration-500 flex flex-col">
    <!-- Header -->
    <div class="flex justify-between items-center px-6 py-5 border-b border-white/10">
        <a href="/" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center text-white font-bold">
                <i class="fas fa-ticket-alt text-sm"></i>
            </div>
            <span class="font-bold text-xl text-white">movie<span class="text-red-500">Go</span></span>
        </a>
        <button id="mobile-close-btn" class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-white hover:bg-white/10 transition-colors">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Links -->
    <div class="flex flex-col justify-center flex-1 px-8 gap-2">
        @php $mobileLinks = [
            ['href' => '/', 'label' => 'Trang chủ'],
            ['href' => route('movies.current'), 'label' => 'Phim Đang Chiếu'],
            ['href' => route('movies.upcoming'), 'label' => 'Phim Sắp Chiếu'],
            ['href' => route('posts.index'), 'label' => 'Tin tức'],
        ]; @endphp

        @foreach($mobileLinks as $i => $link)
        <a href="{{ $link['href'] }}" class="mobile-nav-link block py-4 border-b border-white/10 text-white/70 hover:text-white font-medium text-lg tracking-wide transition-all duration-300 hover:translate-x-2 hover:text-red-400"
           style="transition-delay: {{ $i * 60 }}ms; opacity: 0; transform: translateX(-20px);">
            {{ $link['label'] }}
            <i class="fas fa-chevron-right float-right text-sm mt-1 text-white/30"></i>
        </a>
        @endforeach

        @auth
        <a href="{{ route('booking.history') }}" class="mobile-nav-link block py-4 border-b border-white/10 text-white/70 hover:text-white font-medium text-lg tracking-wide transition-all duration-300 hover:translate-x-2 hover:text-red-400"
           style="transition-delay: 240ms; opacity: 0; transform: translateX(-20px);">
            Lịch sử đặt vé
            <i class="fas fa-chevron-right float-right text-sm mt-1 text-white/30"></i>
        </a>
        @endauth

        <!-- Auth -->
        <div class="mt-8 flex flex-col gap-3 mobile-nav-link" style="transition-delay: 300ms; opacity: 0; transform: translateX(-20px);">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-500 text-white py-3 rounded-full font-semibold text-base transition-all">
                        Đăng xuất
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block text-center border border-white/30 hover:border-white/60 text-white py-3 rounded-full font-semibold text-base transition-all hover:bg-white/10">
                    Đăng nhập
                </a>
                @if(Route::has('register'))
                <a href="{{ route('register') }}" class="block text-center bg-red-600 hover:bg-red-500 text-white py-3 rounded-full font-semibold text-base transition-all">
                    Đăng ký
                </a>
                @endif
            @endauth
        </div>
    </div>
</div>

<style>
    /* Navbar scroll effect */
    #main-nav.scrolled {
        background: rgba(6, 11, 20, 0.95) !important;
        backdrop-filter: blur(20px) !important;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }

    /* Mobile menu open state */
    #mobile-menu.open {
        opacity: 1 !important;
        visibility: visible !important;
    }
    #mobile-menu.open .mobile-nav-link {
        opacity: 1 !important;
        transform: translateX(0) !important;
        transition: opacity 0.4s ease, transform 0.4s ease;
    }
</style>

<script>
    // Navbar scroll effect
    const nav = document.getElementById('main-nav');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 60) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    }, { passive: true });

    // Mobile menu
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileBtn  = document.getElementById('mobile-menu-btn');
    const closeBtn   = document.getElementById('mobile-close-btn');

    function openMenu() {
        mobileMenu.classList.add('open');
        document.body.style.overflow = 'hidden';
        // Trigger link animations
        setTimeout(() => {
            mobileMenu.querySelectorAll('.mobile-nav-link').forEach((el, i) => {
                el.style.transitionDelay = (i * 60 + 80) + 'ms';
                el.style.opacity = '1';
                el.style.transform = 'translateX(0)';
            });
        }, 50);
    }

    function closeMenu() {
        mobileMenu.querySelectorAll('.mobile-nav-link').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateX(-20px)';
        });
        setTimeout(() => {
            mobileMenu.classList.remove('open');
            document.body.style.overflow = '';
        }, 300);
    }

    mobileBtn.addEventListener('click', openMenu);
    closeBtn.addEventListener('click', closeMenu);

    // Close on nav link click
    mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeMenu);
    });
</script>
