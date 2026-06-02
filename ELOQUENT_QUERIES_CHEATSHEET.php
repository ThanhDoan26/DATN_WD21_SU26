<?php

/**
 * ========================================
 * Eloquent ORM - Common Queries Cheat Sheet
 * ========================================
 *
 * File này chứa các ví dụ query thường dùng với Eloquent ORM
 * Không cần include file này vào project, chỉ để reference
 *
 * Tất cả query dưới đây đều có tương đương Raw SQL
 */

// ========================================
// SECTION 1: Basic CRUD
// ========================================

// Get all users
$users = User::all();

// Get user by ID
$user = User::find(1);

// Get user by email
$user = User::where('email', 'admin@cinema.local')->first();

// Create user
$user = User::create([
    'role_id' => 1,
    'full_name' => 'John Doe',
    'email' => 'john@example.com',
    'password_hash' => Hash::make('password123'),
]);

// Update user
$user->update(['loyalty_points' => 500]);

// Delete user
$user->delete();


// ========================================
// SECTION 2: Relationships
// ========================================

// Get user's role
$role = $user->role;

// Get all bookings of user
$bookings = $user->bookings;

// Get all users of a role
$admins = Role::where('role_name', 'ADMIN')->first()->users;

// Get first booking of user (with eager loading)
$user = User::with('bookings')->find(1);

// Get bookings with showtime details
$bookings = Booking::with('showtime.movie', 'showtime.room', 'bookedSeats.seat')->get();

// Attach showtime info to booking
$booking = Booking::find(1);
$showtime = $booking->showtime;
$movie = $showtime->movie;
$room = $showtime->room;
$cinema = $room->cinema;


// ========================================
// SECTION 3: Querying Showtimes & Movies
// ========================================

// Get all showtimes for a movie
$movie = Movie::where('title', 'Avatar')->first();
$showtimes = $movie->showtimes;

// Get showtimes in a cinema, specific date
$showtimes = Showtime::whereHas('room', function ($query) {
    $query->where('cinema_id', 1);
})
->whereDate('start_time', '2026-06-02')
->orderBy('start_time')
->get();

// Get upcoming showtimes
$upcomingShowtimes = Showtime::where('start_time', '>=', now())
    ->where('status', 'SCHEDULED')
    ->with('movie', 'room.cinema')
    ->orderBy('start_time')
    ->get();

// Get now showing movies
$nowShowingMovies = Movie::where('status', 'NOW_SHOWING')->get();


// ========================================
// SECTION 4: Ticket Prices
// ========================================

// Get ticket prices for a showtime
$ticketPrices = TicketPrice::where('showtime_id', $showtimeId)
    ->where('status', 'ACTIVE')
    ->get();

// Get price for specific seat type
$price = TicketPrice::where('showtime_id', $showtimeId)
    ->where('seat_type', 'VIP')
    ->where('status', 'ACTIVE')
    ->value('price'); // Returns single value

// Update ticket price
$ticketPrice->update(['price' => 150000]);


// ========================================
// SECTION 5: Seats
// ========================================

// Get all seats in a room
$seats = Seat::where('room_id', $roomId)->get();

// Get available seats (status = AVAILABLE)
$availableSeats = Seat::where('room_id', $roomId)
    ->where('status', 'AVAILABLE')
    ->get();

// Group seats by row
$seatsByRow = Seat::where('room_id', $roomId)
    ->orderBy('row_name')
    ->orderBy('seat_number')
    ->get()
    ->groupBy('row_name');

// Get VIP seats only
$vipSeats = Seat::where('room_id', $roomId)
    ->where('seat_type', 'VIP')
    ->get();


// ========================================
// SECTION 6: Bookings (Most Important)
// ========================================

// Get all bookings of user
$bookings = Booking::where('user_id', $userId)->get();

// Get paid bookings only
$paidBookings = Booking::where('user_id', $userId)
    ->where('status', 'Paid')
    ->get();

// Get bookings with booked seats detail
$bookings = Booking::with('bookedSeats.seat')
    ->where('user_id', $userId)
    ->orderBy('booking_time', 'desc')
    ->get();

// Get bookings for a showtime (find who booked)
$bookings = Booking::where('showtime_id', $showtimeId)
    ->where('status', '!=', 'Cancelled')
    ->get();

// Count sold seats for a showtime
$soldSeatsCount = Booking::where('showtime_id', $showtimeId)
    ->where('status', '!=', 'Cancelled')
    ->join('booked_seats', 'bookings.id', '=', 'booked_seats.booking_id')
    ->count();

// Create booking with booked seats
$booking = Booking::create([
    'user_id' => $userId,
    'showtime_id' => $showtimeId,
    'total_price' => $totalPrice,
    'status' => 'Pending',
    'booking_time' => now(),
    'booking_code' => 'BK' . uniqid(),
]);

// Create booked seats for booking
foreach ($seatIds as $seatId) {
    BookedSeat::create([
        'booking_id' => $booking->id,
        'seat_id' => $seatId,
        'price_at_booking' => $price,
        'status' => 'RESERVED',
    ]);
}

// Mark booking as paid
$booking->update([
    'status' => 'Paid',
    'payment_method' => 'VNPay',
    'payment_time' => now(),
]);

// Mark booked seats as paid
$booking->bookedSeats()->update(['status' => 'PAID']);


// ========================================
// SECTION 7: Aggregations & Statistics
// ========================================

// Count total bookings
$totalBookings = Booking::count();

// Count paid bookings
$paidBookings = Booking::where('status', 'Paid')->count();

// Total revenue from a showtime
$revenue = Booking::where('showtime_id', $showtimeId)
    ->where('status', 'Paid')
    ->sum('total_price'); // Returns SUM(total_price)

// Revenue by movie
$revenueByMovie = Booking::where('status', 'Paid')
    ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
    ->join('movies', 'showtimes.movie_id', '=', 'movies.id')
    ->groupBy('movies.id', 'movies.title')
    ->selectRaw('movies.title, SUM(bookings.total_price) as revenue')
    ->get();

// Seats sold by type (for a showtime)
$seatsSoldByType = BookedSeat::join('seats', 'booked_seats.seat_id', '=', 'seats.id')
    ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
    ->where('bookings.showtime_id', $showtimeId)
    ->groupBy('seats.seat_type')
    ->selectRaw('seats.seat_type, COUNT(*) as qty, SUM(booked_seats.price_at_booking) as revenue')
    ->get();


// ========================================
// SECTION 8: Date Filtering
// ========================================

// Get showtimes today
$todayShowtimes = Showtime::whereDate('start_time', today())->get();

// Get showtimes in date range
$showtimes = Showtime::whereBetween('start_time', [
    '2026-06-01 00:00:00',
    '2026-06-30 23:59:59'
])->get();

// Get showtimes from now onwards
$futureShowtimes = Showtime::where('start_time', '>=', now())->get();

// Get past bookings
$pastBookings = Booking::where('booking_time', '<', now())->get();


// ========================================
// SECTION 9: Pagination
// ========================================

// Paginate bookings (15 items per page)
$bookings = Booking::paginate(15);

// Custom per page
$bookings = Booking::paginate(10);

// Get page 2
$bookings = Booking::paginate(15, ['*'], 'page', 2);


// ========================================
// SECTION 10: Sorting
// ========================================

// Sort by booking_time descending
$bookings = Booking::orderBy('booking_time', 'desc')->get();

// Sort by multiple columns
$showtimes = Showtime::orderBy('start_time', 'asc')
    ->orderBy('room_id', 'asc')
    ->get();

// Latest bookings
$latestBookings = Booking::latest('booking_time')->get();

// Oldest bookings
$oldestBookings = Booking::oldest('booking_time')->get();


// ========================================
// SECTION 11: Filtering & Where Conditions
// ========================================

// Multiple where conditions (AND)
$bookings = Booking::where('user_id', $userId)
    ->where('status', 'Paid')
    ->where('created_at', '>=', '2026-06-01')
    ->get();

// OR conditions
$bookings = Booking::where('status', 'Pending')
    ->orWhere('status', 'Paid')
    ->get();

// IN clause
$bookings = Booking::whereIn('status', ['Pending', 'Paid'])->get();

// NOT IN clause
$bookings = Booking::whereNotIn('status', ['Cancelled'])->get();

// LIKE search
$movies = Movie::where('title', 'like', '%Avatar%')->get();

// NULL check
$users = User::whereNull('cinema_id')->get();
$users = User::whereNotNull('cinema_id')->get();


// ========================================
// SECTION 12: Joins (Advanced)
// ========================================

// Inner Join: Bookings with their showtimes & movies
$bookings = Booking::join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
    ->join('movies', 'showtimes.movie_id', '=', 'movies.id')
    ->select('bookings.*', 'movies.title as movie_title')
    ->get();

// Left Join: All users with their bookings count
$users = User::leftJoin('bookings', 'users.id', '=', 'bookings.user_id')
    ->selectRaw('users.*, COUNT(bookings.id) as booking_count')
    ->groupBy('users.id')
    ->get();

// Using relationships (better than joins)
$bookings = Booking::with('showtime.movie', 'user')->get();
// Now access: $booking->showtime->movie->title


// ========================================
// SECTION 13: Exists & WhereHas
// ========================================

// Get movies that have showtimes
$moviesWithShowtimes = Movie::has('showtimes')->get();

// Get movies that DON'T have showtimes
$moviesWithoutShowtimes = Movie::doesntHave('showtimes')->get();

// Get cinemas with paid bookings
$cinemasWithPaidBookings = Cinema::whereHas('rooms.showtimes.bookings', function ($query) {
    $query->where('status', 'Paid');
})->get();


// ========================================
// SECTION 14: Update Multiple Records
// ========================================

// Update all pending bookings for a user
Booking::where('user_id', $userId)
    ->where('status', 'Pending')
    ->update(['status' => 'Cancelled']);

// Update all booked seats for a booking
BookedSeat::where('booking_id', $bookingId)
    ->update(['status' => 'PAID']);

// Increment loyalty points
$user->increment('loyalty_points', 10); // +10
$user->decrement('loyalty_points', 5); // -5


// ========================================
// SECTION 15: Chunking (Large Data)
// ========================================

// Process 1000 bookings at a time
Booking::chunk(1000, function ($bookings) {
    foreach ($bookings as $booking) {
        // Do something
    }
});


// ========================================
// SECTION 16: Transactions
// ========================================

// Use DB::transaction for complex operations
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $booking = Booking::create([...]);

    foreach ($seats as $seat) {
        BookedSeat::create([
            'booking_id' => $booking->id,
            'seat_id' => $seat->id,
        ]);
    }
}, 5); // Retry 5 times


// ========================================
// SECTION 17: Raw Queries (When needed)
// ========================================

// Using raw SQL
$bookings = DB::table('bookings')
    ->selectRaw('*, CONCAT(user_id, "-", booking_code) as unique_ref')
    ->whereRaw('status = ? AND user_id = ?', ['Paid', $userId])
    ->get();

// Raw where clause
$bookings = Booking::whereRaw('DATEDIFF(booking_time, NOW()) <= 7')
    ->get();


// ========================================
// SECTION 18: Caching (Performance)
// ========================================

// Cache popular movies for 1 hour
$movies = Cache::remember('movies.popular', 3600, function () {
    return Movie::where('status', 'NOW_SHOWING')
        ->withCount('showtimes')
        ->orderBy('showtimes_count', 'desc')
        ->limit(10)
        ->get();
});

// Forget cache when data changes
Cache::forget('movies.popular');


// ========================================
// SECTION 19: Scopes (Reusable Queries)
// ========================================

// Define scope in Model:
// public function scopeActive($query) {
//     return $query->where('status', 'ACTIVE');
// }

// Use scope:
$activeUsers = User::active()->get();
$activeMovies = Movie::active()->get();


// ========================================
// SECTION 20: HasMany vs BelongsToMany
// ========================================

// User has many bookings (One-to-Many)
$bookings = $user->bookings;

// Booking belongs to one showtime (Many-to-One)
$showtime = $booking->showtime;

// If need Many-to-Many later (e.g., movie_seat, promotion_showtime):
// Use belongsToMany() with pivot table


// ========================================
// PRODUCTION QUERIES - REAL WORLD EXAMPLES
// ========================================

/**
 * Example 1: Get available seats for a showtime (used in BookingService)
 */
$availableSeats = Seat::where('room_id', $showtimeRoomId)
    ->where('status', 'AVAILABLE')
    ->whereNotIn('id', function ($query) {
        $query->select('booked_seats.seat_id')
            ->from('booked_seats')
            ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
            ->where('bookings.showtime_id', $showtimeId)
            ->where('bookings.status', '!=', 'Cancelled');
    })
    ->get();

/**
 * Example 2: Get revenue report by cinema & date
 */
$revenueReport = Booking::where('status', 'Paid')
    ->whereBetween('payment_time', [$startDate, $endDate])
    ->with('showtime.room.cinema')
    ->get()
    ->groupBy(function ($booking) {
        return $booking->showtime->room->cinema->id;
    })
    ->map(function ($bookings, $cinemaId) {
        return [
            'cinema_id' => $cinemaId,
            'total_revenue' => $bookings->sum('total_price'),
            'booking_count' => $bookings->count(),
        ];
    });

/**
 * Example 3: Manager dashboard - get their cinema's stats
 */
$manager = auth()->user();
$stats = [
    'total_showtimes' => Showtime::whereHas('room', function ($q) {
        $q->where('cinema_id', $manager->cinema_id);
    })->count(),
    'total_bookings' => Booking::whereHas('showtime.room', function ($q) {
        $q->where('cinema_id', $manager->cinema_id);
    })->count(),
    'total_revenue' => Booking::whereHas('showtime.room', function ($q) {
        $q->where('cinema_id', $manager->cinema_id);
    })
    ->where('status', 'Paid')
    ->sum('total_price'),
];

/**
 * Example 4: Customer dashboard - get their bookings with details
 */
$customerBookings = auth()->user()
    ->bookings()
    ->with('showtime.movie', 'showtime.room.cinema', 'bookedSeats.seat')
    ->orderBy('booking_time', 'desc')
    ->get();

/**
 * Example 5: Search showtimes with flexible filters
 */
$showtimes = Showtime::query()
    ->when($movieId, fn($q) => $q->where('movie_id', $movieId))
    ->when($cinemaId, fn($q) => $q->whereHas('room', fn($r) => $r->where('cinema_id', $cinemaId)))
    ->when($date, fn($q) => $q->whereDate('start_time', $date))
    ->where('status', 'SCHEDULED')
    ->with('movie', 'room.cinema')
    ->orderBy('start_time')
    ->paginate(20);
