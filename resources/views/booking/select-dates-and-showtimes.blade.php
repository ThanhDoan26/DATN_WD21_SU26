<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chọn Suất Chiếu - movieGo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
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
        .date-item {
            @apply p-4 rounded-xl border-2 border-slate-700 cursor-pointer transition-all duration-300 hover:border-primary hover:bg-slate-800 hover:-translate-y-1 hover:shadow-lg hover:shadow-primary/20;
        }
        .date-item.active {
            @apply bg-primary border-primary shadow-lg shadow-primary/40 transform -translate-y-1;
        }
        .showtime-item {
            @apply p-5 rounded-xl border-2 border-slate-700 cursor-pointer transition-all duration-300 hover:border-primary hover:bg-slate-800 hover:-translate-y-1 hover:shadow-lg hover:shadow-primary/20;
        }
        .showtime-item.active {
            @apply bg-primary border-primary shadow-lg shadow-primary/40 transform -translate-y-1;
        }
        .showtime-item.disabled {
            @apply opacity-50 cursor-not-allowed hover:border-slate-700 hover:bg-slate-800;
        }
    </style>
</head>
<body class="bg-slate-900 text-white antialiased selection:bg-primary selection:text-white">

    @include('layouts.guest-navigation')

    <!-- Page Header -->
    <div class="bg-gradient-to-b from-slate-800 to-slate-900 pt-32 pb-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center gap-4 mb-4">
                <i class="fas fa-calendar text-primary text-4xl"></i>
                <h1 class="text-5xl md:text-6xl font-bold">Chọn Suất Chiếu</h1>
            </div>
            <p class="text-slate-400 text-lg">
                Bước 2 & 3: Chọn ngày và suất chiếu
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <section class="py-16 px-4 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <!-- Movie Info Bar -->
            <div class="bg-slate-800 rounded-lg p-6 mb-8 flex items-center gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-slate-400">Phim:</span>
                        <span class="text-xl font-bold" id="movieTitle">Loading...</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400">Rạp:</span>
                        <span class="text-xl font-bold" id="cinemaName">Loading...</span>
                    </div>
                </div>
            </div>

            <!-- Step 2: Select Date -->
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold">2</div>
                    <h2 class="text-3xl font-bold">Chọn Ngày Chiếu</h2>
                    <span class="text-slate-400 text-sm ml-auto">Chỉ hiển thị ngày có suất chiếu</span>
                </div>

                <div id="datesContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    <div class="col-span-full text-center py-8">
                        <i class="fas fa-spinner text-primary text-3xl animate-spin"></i>
                        <p class="text-slate-400 mt-4">Đang tải danh sách ngày...</p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Select Showtime -->
            <div id="showtimeSection" class="hidden">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center font-bold">3</div>
                    <h2 class="text-3xl font-bold">Chọn Suất Chiếu</h2>
                    <span class="text-slate-400 text-sm ml-auto" id="selectedDateDisplay"></span>
                </div>

                <div id="showtimesContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Showtimes will be loaded here -->
                </div>
            </div>

            <!-- No Showtimes Message -->
            <div id="noShowtimesMessage" class="hidden text-center py-20">
                <i class="fas fa-inbox text-slate-500 text-6xl mb-4"></i>
                <p class="text-slate-400 text-xl">Không có suất chiếu nào cho ngày này</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 mt-12 justify-between">
                <a href="{{ route('booking.select-cinema', $movie) }}" class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-3 rounded-lg transition font-bold">
                    <i class="fas fa-arrow-left mr-2"></i>Quay lại chọn rạp
                </a>
                <button id="nextButton"
                        onclick="proceedToSeats()"
                        disabled
                        class="bg-primary hover:bg-red-700 disabled:bg-slate-600 disabled:cursor-not-allowed text-white px-8 py-3 rounded-lg transition font-bold">
                    <i class="fas fa-arrow-right mr-2"></i>Tiếp tục chọn ghế
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-800 border-t border-slate-700 py-12 px-4 mt-16">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center text-white font-bold">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <span class="font-bold text-xl">movie<span class="text-primary">Go</span></span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        const movieId = {{ $movie->id }};
        const cinemaId = {{ $cinema->id }};
        const cinemaName = '{{ $cinema->name }}';
        const movieTitle = '{{ $movie->title }}';

        let selectedDate = null;
        let selectedShowtime = null;

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('movieTitle').textContent = movieTitle;
            document.getElementById('cinemaName').textContent = cinemaName;

            if (movieId && cinemaId) {
                loadDates();
            }
        });

        async function loadDates() {
            try {
                const response = await fetch(`/api/booking/dates?movie_id=${movieId}&cinema_id=${cinemaId}`);
                const result = await response.json();

                if (result.data && result.data.length > 0) {
                    displayDates(result.data);
                } else {
                    document.getElementById('datesContainer').innerHTML = `
                        <div class="col-span-full text-center py-20">
                            <i class="fas fa-calendar-times text-slate-500 text-6xl mb-4"></i>
                            <p class="text-slate-400 text-xl">Không có ngày chiếu nào</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading dates:', error);
                document.getElementById('datesContainer').innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <i class="fas fa-exclamation-circle text-red-500 text-6xl mb-4"></i>
                        <p class="text-red-400 text-xl">Lỗi khi tải dữ liệu</p>
                    </div>
                `;
            }
        }

        function displayDates(dates) {
            const container = document.getElementById('datesContainer');
            container.innerHTML = dates.map(date => {
                const dateObj = new Date(date);
                const dayName = dateObj.toLocaleDateString('vi-VN', { weekday: 'short' });
                const dayNum = dateObj.getDate();
                const month = dateObj.getMonth() + 1;

                return `
                    <button onclick="selectDate('${date}', this)" class="date-item w-full flex flex-col items-center justify-center p-4 rounded-xl border border-slate-700 bg-slate-800/50 cursor-pointer transition-all duration-300 hover:border-primary hover:bg-slate-800 hover:-translate-y-1 hover:shadow-lg hover:shadow-primary/20">
                        <div class="text-2xl font-bold text-white mb-1">${dayNum}</div>
                        <div class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold mb-1">Th ${month}</div>
                        <div class="text-xs text-slate-500">${dayName}</div>
                    </button>
                `;
            }).join('');
        }

        function selectDate(date, button) {
            selectedDate = date;

            // Update UI
            document.querySelectorAll('.date-item').forEach(el => {
                el.classList.remove('bg-primary', 'border-primary', 'shadow-lg', 'shadow-primary/40', '-translate-y-1');
                el.classList.add('border-slate-700', 'bg-slate-800/50');
            });
            button.classList.remove('border-slate-700', 'bg-slate-800/50');
            button.classList.add('bg-primary', 'border-primary', 'shadow-lg', 'shadow-primary/40', '-translate-y-1');

            // Load showtimes
            loadShowtimes(date);
        }

        async function loadShowtimes(date) {
            try {
                const response = await fetch(`/api/booking/showtimes?movie_id=${movieId}&cinema_id=${cinemaId}&date=${date}`);
                const result = await response.json();

                document.getElementById('selectedDateDisplay').textContent = new Date(date).toLocaleDateString('vi-VN', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                if (result.data && result.data.length > 0) {
                    displayShowtimes(result.data);
                    document.getElementById('showtimeSection').classList.remove('hidden');
                    document.getElementById('noShowtimesMessage').classList.add('hidden');
                } else {
                    document.getElementById('showtimeSection').classList.add('hidden');
                    document.getElementById('noShowtimesMessage').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error loading showtimes:', error);
            }
        }

        function displayShowtimes(showtimes) {
            const container = document.getElementById('showtimesContainer');
            container.innerHTML = showtimes.map(showtime => {
                const isDisabled = showtime.available_seats <= 0;
                
                // Extra styling based on status
                const baseClasses = "showtime-item w-full flex flex-col p-5 rounded-xl border border-slate-700 bg-slate-800/50 cursor-pointer transition-all duration-300";
                const hoverClasses = isDisabled ? "opacity-50 cursor-not-allowed" : "hover:border-primary hover:bg-slate-800 hover:-translate-y-1 hover:shadow-lg hover:shadow-primary/20";
                
                return `
                    <button onclick="selectShowtime(${showtime.id}, this)"
                            class="${baseClasses} ${hoverClasses}"
                            ${isDisabled ? 'disabled' : ''}>
                        <div class="flex justify-between items-start w-full mb-4">
                            <div class="text-left">
                                <div class="text-3xl font-bold text-white mb-1">${showtime.time}</div>
                                <div class="text-xs text-slate-400 font-medium bg-slate-900/50 inline-block px-2 py-0.5 rounded">
                                    Kết thúc: ${new Date(showtime.start_time).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}
                                </div>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-primary border border-primary/30 bg-primary/10 px-3 py-1 rounded-full">
                                ${showtime.room_format}
                            </span>
                        </div>
                        <div class="flex justify-between items-center w-full pt-4 border-t border-slate-700/50">
                            <div class="flex items-center gap-2 text-sm text-slate-300">
                                <i class="fas fa-door-open text-slate-500"></i>
                                <span class="font-semibold">${showtime.room_name}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <i class="fas fa-chair text-slate-500"></i>
                                <span class="${showtime.available_seats > 10 ? 'text-green-400' : 'text-orange-400'} font-semibold">
                                    ${showtime.available_seats} ghế
                                </span>
                            </div>
                        </div>
                    </button>
                `;
            }).join('');
        }

        function selectShowtime(showtimeId, button) {
            selectedShowtime = showtimeId;

            document.querySelectorAll('.showtime-item').forEach(el => {
                el.classList.remove('bg-primary', 'border-primary', 'shadow-lg', 'shadow-primary/40', '-translate-y-1');
                el.classList.add('border-slate-700', 'bg-slate-800/50');
            });
            
            button.classList.remove('border-slate-700', 'bg-slate-800/50');
            button.classList.add('bg-primary', 'border-primary', 'shadow-lg', 'shadow-primary/40', '-translate-y-1');

            document.getElementById('nextButton').disabled = false;
        }

        function proceedToSeats() {
            if (selectedShowtime) {
                window.location.href = `/booking/showtime/${selectedShowtime}/seats`;
            }
        }
    </script>
</body>
</html>
