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
                <!-- Location Selector (Desktop) -->
                <div class="relative z-50" id="desktop-location-wrapper">
                    <button id="desktop-location-btn" type="button" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 hover:bg-white/20 border border-white/15 text-white text-xs font-medium backdrop-blur-md transition-all duration-200 hover:border-red-500/50 hover:shadow-lg hover:shadow-red-500/20 group cursor-pointer" title="Chọn Tỉnh / Thành phố để lọc phim và rạp">
                        <i class="fas fa-map-marker-alt text-red-500 group-hover:scale-110 transition-transform"></i>
                        <span class="max-w-[110px] truncate" id="desktop-current-location-text">
                            {{ (!empty($userLocation) && strtoupper($userLocation) !== 'ALL') ? $userLocation : 'Toàn quốc' }}
                        </span>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 group-hover:text-white transition-colors"></i>
                    </button>

                    <!-- Location Dropdown Modal -->
                    <div id="desktop-location-dropdown" class="absolute right-0 mt-3 w-80 sm:w-96 bg-slate-900/98 backdrop-blur-2xl rounded-2xl shadow-2xl py-3 px-3 opacity-0 invisible transition-all duration-300 transform origin-top-right translate-y-3 border border-slate-700/80 z-[100] text-slate-200">
                        <!-- Header & Search -->
                        <div class="px-2 pb-2 mb-2 border-b border-slate-800">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                    <i class="fas fa-map-marker-alt text-red-500"></i> Chọn khu vực xem phim
                                </span>
                                <a href="{{ route('location.switch', 'ALL') }}" class="text-[11px] font-semibold text-red-400 hover:text-red-300 transition-colors">
                                    Toàn quốc
                                </a>
                            </div>
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                                <input type="text" id="location-search-input" placeholder="Tìm nhanh tỉnh, thành phố..." class="w-full bg-slate-800/90 border border-slate-700 rounded-xl pl-8 pr-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-red-500 transition-all">
                            </div>
                        </div>

                        <!-- Scrollable Cities List -->
                        <div class="max-h-72 overflow-y-auto custom-scrollbar space-y-3 pr-1" id="location-list-container">
                            <!-- Active Cinema Cities -->
                            @if(isset($activeCinemaCities) && $activeCinemaCities->count() > 0)
                                <div class="location-group">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-red-400 px-2 py-1 flex items-center gap-1">
                                        <i class="fas fa-fire text-xs"></i> Khu vực có rạp chiếu
                                    </div>
                                    <div class="grid grid-cols-2 gap-1.5 mt-1">
                                        @foreach($activeCinemaCities as $cCity => $cCount)
                                            <a href="{{ route('location.switch', urlencode($cCity)) }}" 
                                               class="location-item flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition-all {{ ($userLocation === $cCity) ? 'bg-red-600 text-white font-bold shadow-md shadow-red-600/30' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 hover:text-white border border-slate-700/50' }}"
                                               data-name="{{ mb_strtolower($cCity, 'UTF-8') }}">
                                                <span class="truncate">{{ $cCity }}</span>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ ($userLocation === $cCity) ? 'bg-white/20 text-white' : 'bg-slate-700 text-slate-300' }}">{{ $cCount }} rạp</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- All Provinces -->
                            <div class="location-group">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-2 py-1 mt-2">
                                    Tất cả Tỉnh / Thành phố
                                </div>
                                <div class="grid grid-cols-2 gap-1 mt-1">
                                    <a href="{{ route('location.switch', 'ALL') }}" 
                                       class="location-item col-span-2 flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ (empty($userLocation) || $userLocation === 'ALL') ? 'bg-red-600 text-white font-bold shadow-md shadow-red-600/30' : 'bg-slate-800/40 hover:bg-slate-800 text-slate-300 hover:text-white' }}"
                                       data-name="toan quoc tat ca">
                                        <span><i class="fas fa-globe-asia mr-1.5"></i> Toàn quốc (Tất cả vị trí)</span>
                                        <i class="fas fa-check text-[10px] {{ (empty($userLocation) || $userLocation === 'ALL') ? 'opacity-100' : 'opacity-0' }}"></i>
                                    </a>
                                    @foreach($allProvinces ?? config('provinces', []) as $province)
                                        <a href="{{ route('location.switch', urlencode($province)) }}" 
                                           class="location-item flex items-center justify-between px-3 py-1.5 rounded-lg text-xs transition-all {{ ($userLocation === $province) ? 'bg-red-600/20 text-red-400 font-bold border border-red-500/30' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}"
                                           data-name="{{ mb_strtolower($province, 'UTF-8') }}">
                                            <span class="truncate">{{ $province }}</span>
                                            @if(isset($activeCinemaCities[$province]))
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                        <div class="relative z-50" id="desktop-avatar-wrapper">
                            <button id="desktop-avatar-btn" class="inline-flex items-center gap-2 rounded-full focus:outline-none transition-all cursor-pointer">
                                <span class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-white text-red-600 font-bold shadow-md shadow-black/20 hover:ring-2 hover:ring-red-500 hover:ring-offset-2 hover:ring-offset-black/50 transition-all">{{ strtoupper(substr(auth()->user()->name ?? 'U',0,1)) }}</span>
                            </button>
                            
                            <!-- Dropdown menu -->
                            <div id="desktop-avatar-dropdown" class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-xl py-2 opacity-0 invisible transition-all duration-300 transform origin-top-right translate-y-3 border border-gray-100">
                                <div class="px-4 py-3 border-b border-gray-100 mb-1 bg-gray-50/50">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500 truncate mt-0.5">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors flex items-center gap-2.5">
                                    <i class="fas fa-user-circle text-gray-400 group-hover/link:text-red-500"></i> Thông tin tài khoản
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="m-0 block">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors flex items-center gap-2.5">
                                        <i class="fas fa-sign-out-alt text-gray-400"></i> Đăng xuất
                                    </button>
                                </form>
                            </div>
                        </div>
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
            <button id="mobile-menu-btn" class="md:hidden flex flex-col gap-1.5 p-2 group cursor-pointer" aria-label="Menu">
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
        <button id="mobile-close-btn" class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-white hover:bg-white/10 transition-colors cursor-pointer">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Links -->
    <div class="flex flex-col justify-center flex-1 px-8 gap-2 overflow-y-auto">
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

        <!-- Mobile Location Picker -->
        <div class="mt-4 pb-4 border-b border-white/10 mobile-nav-link" style="transition-delay: 280ms; opacity: 0; transform: translateX(-20px);">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <i class="fas fa-map-marker-alt text-red-500"></i> Khu vực xem phim:
            </div>
            <select onchange="window.location.href='/location/set/' + encodeURIComponent(this.value)" class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-red-500">
                <option value="ALL" {{ (empty($userLocation) || $userLocation === 'ALL') ? 'selected' : '' }}>Toàn quốc (Tất cả vị trí)</option>
                @if(isset($activeCinemaCities) && $activeCinemaCities->count() > 0)
                    <optgroup label="Khu vực có rạp">
                        @foreach($activeCinemaCities as $cCity => $cCount)
                            <option value="{{ $cCity }}" {{ ($userLocation === $cCity) ? 'selected' : '' }}>{{ $cCity }} ({{ $cCount }} rạp)</option>
                        @endforeach
                    </optgroup>
                @endif
                <optgroup label="Tất cả tỉnh thành">
                    @foreach($allProvinces ?? config('provinces', []) as $province)
                        <option value="{{ $province }}" {{ ($userLocation === $province) ? 'selected' : '' }}>{{ $province }}</option>
                    @endforeach
                </optgroup>
            </select>
        </div>

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

    /* Avatar & Location Dropdown Open State */
    #desktop-avatar-dropdown.open,
    #desktop-location-dropdown.open {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(0) !important;
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

    // Avatar dropdown toggle
    const avatarBtn = document.getElementById('desktop-avatar-btn');
    const avatarDropdown = document.getElementById('desktop-avatar-dropdown');
    
    if (avatarBtn && avatarDropdown) {
        avatarBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (locationDropdown) locationDropdown.classList.remove('open');
            avatarDropdown.classList.toggle('open');
        });
        
        document.addEventListener('click', (e) => {
            if (!avatarDropdown.contains(e.target) && !avatarBtn.contains(e.target)) {
                avatarDropdown.classList.remove('open');
            }
        });
    }

    // Location dropdown toggle & Quick Search
    const locationBtn = document.getElementById('desktop-location-btn');
    const locationDropdown = document.getElementById('desktop-location-dropdown');
    const locationSearchInput = document.getElementById('location-search-input');

    if (locationBtn && locationDropdown) {
        locationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (avatarDropdown) avatarDropdown.classList.remove('open');
            const isOpen = locationDropdown.classList.toggle('open');
            if (isOpen && locationSearchInput) {
                setTimeout(() => locationSearchInput.focus(), 150);
            }
        });

        document.addEventListener('click', (e) => {
            if (!locationDropdown.contains(e.target) && !locationBtn.contains(e.target)) {
                locationDropdown.classList.remove('open');
            }
        });

        // Quick search location filter
        if (locationSearchInput) {
            locationSearchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                const items = locationDropdown.querySelectorAll('.location-item');
                let hasVisibleInGroup = {};

                items.forEach(item => {
                    const name = item.getAttribute('data-name') || '';
                    if (name.includes(query)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    }
</script>

@auth
    @include('partials.profile_modal')
@endauth
