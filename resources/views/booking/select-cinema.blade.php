@extends($layout ?? 'layouts.frontend')

@section('content')
@php
    $userLoc = $userLocation ?? session('user_location', 'ALL');
    $hasCustomLocation = (!empty($userLoc) && strtoupper($userLoc) !== 'ALL');
    $initialCity = $initialCity ?? (($hasCustomLocation && isset($cities) && $cities->contains($userLoc)) ? $userLoc : 'ALL');
@endphp

    <!-- Page Header -->
    <div class="bg-gradient-to-b from-slate-800 to-slate-900 pt-32 pb-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center gap-4 mb-4">
                <i class="fas fa-map-marker-alt text-primary text-4xl"></i>
                <h1 class="text-5xl md:text-6xl font-bold">Chọn Cụm Rạp</h1>
            </div>
            <p class="text-slate-400 text-lg">
                Bước 1: Chọn rạp chiếu phim {{ $movie->title }}
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <section class="py-16 px-4 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <!-- Movie Info Bar -->
            <div class="bg-slate-800 rounded-lg p-6 mb-8 flex items-center gap-4">
                <img src="{{ str_starts_with($movie->poster_url, 'http') ? $movie->poster_url : asset('storage/' . $movie->poster_url) }}" alt="{{ $movie->title }}" class="w-20 h-28 rounded-lg object-cover shadow-lg border border-slate-700">
                <div class="flex-1">
                    <h2 class="text-3xl font-bold mb-2">{{ $movie->title }}</h2>
                    <p class="text-slate-300">{{ $movie->description }}</p>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            @if($cinemas->count() > 0)
                <div class="bg-slate-800/90 backdrop-blur rounded-2xl p-5 mb-8 border border-slate-700 shadow-xl">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <!-- City/Province Filter Tabs -->
                        <div class="w-full md:w-auto flex-1 flex flex-wrap items-center gap-2" id="cityFilterContainer">
                            <span class="text-sm font-semibold text-slate-400 mr-2 flex items-center gap-1.5">
                                <i class="fas fa-map-marker-alt text-primary"></i> Khu vực:
                            </span>

                            @if($hasCustomLocation)
                                {{-- Chỉ hiện mỗi khu vực đã chọn --}}
                                @foreach($cities as $city)
                                    @php
                                        $cityCount = $cinemas->where('city', $city)->count();
                                    @endphp
                                    <span class="px-4 py-2 rounded-xl text-sm font-bold bg-primary text-white shadow-lg shadow-primary/30 border border-primary flex items-center gap-2">
                                        <i class="fas fa-map-pin text-xs"></i> {{ $city }} ({{ $cityCount }})
                                    </span>
                                @endforeach

                                <a href="{{ route('location.switch', 'ALL') }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition flex items-center gap-1.5 ml-1">
                                    <i class="fas fa-globe-asia text-slate-400"></i> Xem tất cả khu vực
                                </a>
                            @else
                                <button type="button" 
                                        class="city-filter-btn active bg-primary text-white shadow-lg shadow-primary/30 border border-primary px-4 py-2 rounded-xl text-sm font-bold transition-all duration-200"
                                        data-city="ALL">
                                    Tất cả ({{ $cinemas->count() }})
                                </button>
                                @foreach($cities as $city)
                                    @php
                                        $cityCount = $cinemas->where('city', $city)->count();
                                    @endphp
                                    <button type="button" 
                                            class="city-filter-btn bg-slate-900/60 text-slate-300 border border-slate-700 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 hover:bg-slate-700 hover:text-white"
                                            data-city="{{ $city }}">
                                        {{ $city }} ({{ $cityCount }})
                                    </button>
                                @endforeach
                            @endif
                        </div>

                        <!-- Search Box -->
                        <div class="w-full md:w-72 relative">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" 
                                   id="cinemaSearchInput" 
                                   placeholder="Tìm rạp theo tên, địa chỉ..." 
                                   class="w-full bg-slate-900/80 border border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all">
                        </div>
                    </div>
                </div>

                <!-- Active Filter Summary -->
                <div class="flex items-center justify-between mb-6 px-1">
                    <p class="text-slate-400 text-sm" id="filterResultText">
                        Hiển thị <span class="font-bold text-white" id="visibleCount">{{ $cinemas->count() }}</span> cụm rạp phù hợp
                    </p>
                </div>

                <!-- Cinemas Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="cinemasGrid">
                    @foreach($cinemas as $cinema)
                        <div class="cinema-card bg-slate-800 rounded-xl overflow-hidden hover:bg-slate-700 transition-all duration-300 cursor-pointer group hover:-translate-y-2 hover:shadow-xl hover:shadow-primary/20 border border-slate-700 hover:border-primary"
                             data-city="{{ $cinema->city }}"
                             data-name="{{ mb_strtolower($cinema->name, 'UTF-8') }}"
                             data-address="{{ mb_strtolower($cinema->address, 'UTF-8') }}"
                             onclick="selectCinema({{ $cinema->id }}, '{{ $cinema->name }}')">
                            <div class="p-6">
                                <!-- Cinema Header -->
                                <div class="flex items-start gap-4 mb-4">
                                    <div class="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-building text-primary text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold group-hover:text-primary transition">{{ $cinema->name }}</h3>
                                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-900 text-primary border border-primary/30">
                                            <i class="fas fa-map-pin text-[10px] mr-1"></i>{{ $cinema->city }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Cinema Details -->
                                <div class="space-y-2 mb-4 text-slate-300 text-sm">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-map-marker-alt text-slate-500 w-4"></i>
                                        <span class="line-clamp-2">{{ $cinema->address }}</span>
                                    </div>
                                    @if($cinema->phone)
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-phone text-slate-500 w-4"></i>
                                        <span>{{ $cinema->phone }}</span>
                                    </div>
                                    @endif
                                </div>

                                <!-- Room Count -->
                                <div class="bg-slate-900 rounded-lg p-3 mb-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-300">{{ $cinema->rooms->count() }} phòng chiếu</span>
                                        <span class="text-primary font-bold">{{ $cinema->rooms->count() }}</span>
                                    </div>
                                </div>

                                <!-- Select Button -->
                                <button type="button" class="w-full bg-primary hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 group-hover:shadow-lg group-hover:shadow-primary/50">
                                    <i class="fas fa-arrow-right mr-2"></i>Chọn Rạp Này
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- No match state -->
                <div id="noMatchState" class="hidden text-center py-16 bg-slate-800/50 rounded-2xl border border-slate-700/50">
                    <i class="fas fa-search-location text-slate-500 text-5xl mb-4"></i>
                    <p class="text-slate-300 text-lg font-semibold mb-2">Không tìm thấy rạp phù hợp tại khu vực này</p>
                    <p class="text-slate-500 text-sm mb-6">Vui lòng thử chọn tỉnh thành khác hoặc xóa từ khóa tìm kiếm</p>
                    <button type="button" onclick="resetFilters()" class="inline-flex items-center gap-2 bg-primary hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-xl transition shadow">
                        <i class="fas fa-undo"></i> Xem tất cả rạp
                    </button>
                </div>
            @else
                <div class="text-center py-20 bg-slate-800/40 rounded-2xl border border-slate-700/50 p-8 max-w-xl mx-auto">
                    <i class="fas fa-search-location text-red-500 text-6xl mb-4"></i>
                    @if(!empty($userLocation) && strtoupper($userLocation) !== 'ALL')
                        <h3 class="text-white text-xl font-bold mb-2">Chưa có rạp chiếu tại {{ $userLocation }}</h3>
                        <p class="text-slate-400 text-sm mb-6">Hiện tại bộ phim <strong>{{ $movie->title }}</strong> chưa có suất chiếu tại khu vực <strong>{{ $userLocation }}</strong>.</p>
                        <div class="flex flex-wrap justify-center gap-3">
                            <a href="{{ route('location.switch', 'ALL') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-red-700 text-white font-semibold px-6 py-2.5 rounded-xl transition shadow-lg shadow-primary/30">
                                <i class="fas fa-globe-asia"></i> Xem rạp Toàn quốc
                            </a>
                            <a href="{{ (isset($isWalkIn) && $isWalkIn) ? route('staff.walkin.movies') : route('movies.current') }}" class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-white px-5 py-2.5 rounded-xl transition">
                                <i class="fas fa-arrow-left"></i> Quay lại danh sách phim
                            </a>
                        </div>
                    @else
                        <p class="text-slate-400 text-xl mb-6">Không có cụm rạp nào có suất chiếu phim này</p>
                        <a href="{{ (isset($isWalkIn) && $isWalkIn) ? route('staff.walkin.movies') : route('movies.current') }}" class="inline-block bg-primary hover:bg-red-700 text-white px-6 py-2 rounded-lg transition">
                            <i class="fas fa-arrow-left mr-2"></i>Quay lại danh sách phim
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cityButtons = document.querySelectorAll('.city-filter-btn');
            const searchInput = document.getElementById('cinemaSearchInput');
            const cinemaCards = document.querySelectorAll('.cinema-card');
            const noMatchState = document.getElementById('noMatchState');
            const visibleCountEl = document.getElementById('visibleCount');

            let currentCity = '{{ $initialCity ?? "ALL" }}';
            let currentKeyword = '';

            function filterCinemas() {
                let visibleCount = 0;

                cinemaCards.forEach(card => {
                    const cardCity = card.getAttribute('data-city') || '';
                    const cardName = card.getAttribute('data-name') || '';
                    const cardAddress = card.getAttribute('data-address') || '';

                    const matchCity = (currentCity === 'ALL' || cardCity === currentCity || cardCity.includes(currentCity));
                    const matchSearch = (!currentKeyword || cardName.includes(currentKeyword) || cardAddress.includes(currentKeyword) || cardCity.toLowerCase().includes(currentKeyword));

                    if (matchCity && matchSearch) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (visibleCountEl) {
                    visibleCountEl.textContent = visibleCount;
                }

                if (noMatchState) {
                    if (visibleCount === 0) {
                        noMatchState.classList.remove('hidden');
                    } else {
                        noMatchState.classList.add('hidden');
                    }
                }
            }

            if (currentCity !== 'ALL') {
                filterCinemas();
            }

            cityButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    cityButtons.forEach(b => {
                        b.classList.remove('active', 'bg-primary', 'text-white', 'shadow-lg', 'shadow-primary/30', 'border-primary');
                        b.classList.add('bg-slate-900/60', 'text-slate-300', 'border-slate-700');
                    });

                    this.classList.add('active', 'bg-primary', 'text-white', 'shadow-lg', 'shadow-primary/30', 'border-primary');
                    this.classList.remove('bg-slate-900/60', 'text-slate-300', 'border-slate-700');

                    currentCity = this.getAttribute('data-city') || 'ALL';
                    filterCinemas();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    currentKeyword = this.value.trim().toLowerCase();
                    filterCinemas();
                });
            }

            window.resetFilters = function() {
                currentCity = 'ALL';
                currentKeyword = '';
                if (searchInput) searchInput.value = '';

                cityButtons.forEach(b => {
                    if (b.getAttribute('data-city') === 'ALL') {
                        b.classList.add('active', 'bg-primary', 'text-white', 'shadow-lg', 'shadow-primary/30', 'border-primary');
                        b.classList.remove('bg-slate-900/60', 'text-slate-300', 'border-slate-700');
                    } else {
                        b.classList.remove('active', 'bg-primary', 'text-white', 'shadow-lg', 'shadow-primary/30', 'border-primary');
                        b.classList.add('bg-slate-900/60', 'text-slate-300', 'border-slate-700');
                    }
                });

                filterCinemas();
            };
        });

        // Step 1: BFCache listener
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                console.log('Restored from BFCache...');
            }
        });

        function selectCinema(cinemaId, cinemaName, e) {
            if (e) { e.preventDefault(); e.stopPropagation(); }
            const movieId = {{ $movie->id }};
            // Chuyển đến bước chọn ngày và suất chiếu
            @if(isset($isWalkIn) && $isWalkIn)
                window.location.href = `/staff/walk-in/movie/${movieId}/cinema/${cinemaId}/dates`;
            @else
                window.location.href = `/booking/movie/${movieId}/cinema/${cinemaId}/dates`;
            @endif
        }
    </script>
@endpush
