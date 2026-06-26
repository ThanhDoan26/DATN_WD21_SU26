<!-- Footer -->
<footer class="bg-slate-800 border-t border-slate-700 py-12 px-4 mt-16">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
            <div class="flex items-center gap-2 mb-4 text-white">
                <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center text-white font-bold">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <span class="font-bold text-xl">movie<span class="text-primary">Go</span></span>
            </div>
            <p class="text-slate-400 text-sm">Nền tảng đặt vé xem phim trực tuyến hàng đầu</p>
        </div>
        <div>
            <h4 class="font-semibold mb-4 text-white">Về movieGo</h4>
            <ul class="space-y-2 text-slate-400 text-sm">
                <li><a href="/" class="hover:text-white transition">Trang chủ</a></li>
                <li><a href="#" class="hover:text-white transition">Giới thiệu</a></li>
                <li><a href="#" class="hover:text-white transition">Liên hệ</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold mb-4 text-white">Phim</h4>
            <ul class="space-y-2 text-slate-400 text-sm">
                <li><a href="{{ route('movies.current') }}" class="hover:text-white transition">Phim đang chiếu</a></li>
                <li><a href="{{ route('movies.upcoming') }}" class="hover:text-white transition">Phim sắp chiếu</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold mb-4 text-white">Liên kết</h4>
            <ul class="space-y-2 text-slate-400 text-sm">
                <li><a href="#" class="hover:text-white transition">Điều khoản</a></li>
                <li><a href="#" class="hover:text-white transition">Chính sách riêng tư</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-slate-700 mt-8 pt-8 text-center text-slate-400 text-sm">
        <p>&copy; 2026 movieGo. Bảo lưu mọi quyền.</p>
    </div>
</footer>
