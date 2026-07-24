<!-- Footer -->
<footer class="relative overflow-hidden pt-20 pb-8 px-4 mt-0" style="background: linear-gradient(180deg, #060b14 0%, #020507 100%);">

    <!-- Decorative top border -->
    <div class="absolute top-0 left-0 right-0 h-px" style="background: linear-gradient(90deg, transparent, rgba(229,9,20,0.5), rgba(255,255,255,0.15), rgba(229,9,20,0.5), transparent);"></div>

    <!-- Background accent -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-1/2 h-px bg-red-500/30 blur-sm"></div>
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-96 h-96 bg-red-500/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto relative z-10">

        <!-- Top Brand Section -->
        <div class="text-center mb-16">
            <a href="/" class="inline-flex flex-col items-center gap-3 group mb-5">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center text-white font-bold text-2xl shadow-xl shadow-red-500/20 group-hover:scale-110 group-hover:shadow-red-500/40 transition-all duration-300">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <span class="font-bold text-3xl tracking-tight text-white">movie<span class="text-red-500">Go</span></span>
            </a>
            <p class="text-slate-400 max-w-sm mx-auto leading-relaxed">Đặt vé nhanh · Trải nghiệm điện ảnh mọi lúc mọi nơi</p>
        </div>

        <!-- Links Grid -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-12 mb-16">

            <!-- Giới Thiệu -->
            <div>
                <h4 class="text-white font-bold mb-5 text-sm uppercase tracking-widest flex items-center gap-2">
                    <span class="w-4 h-0.5 bg-red-500 rounded-full"></span>
                    Giới Thiệu
                </h4>
                <ul class="space-y-3 text-slate-400 text-sm">
                    <li><a href="#" class="hover:text-white hover:translate-x-1 transition-all duration-200 flex items-center gap-2 group">
                        <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-red-400 transition-colors"></i> Về MovieGo
                    </a></li>
                    <li><a href="#" class="hover:text-white hover:translate-x-1 transition-all duration-200 flex items-center gap-2 group">
                        <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-red-400 transition-colors"></i> Hệ thống rạp
                    </a></li>
                    <li><a href="{{ route('posts.index') }}" class="hover:text-white hover:translate-x-1 transition-all duration-200 flex items-center gap-2 group">
                        <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-red-400 transition-colors"></i> Tin tức
                    </a></li>
                    <li><a href="#" class="hover:text-white hover:translate-x-1 transition-all duration-200 flex items-center gap-2 group">
                        <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-red-400 transition-colors"></i> Khuyến mãi
                    </a></li>
                </ul>
            </div>

            <!-- Hỗ Trợ -->
            <div>
                <h4 class="text-white font-bold mb-5 text-sm uppercase tracking-widest flex items-center gap-2">
                    <span class="w-4 h-0.5 bg-red-500 rounded-full"></span>
                    Hỗ Trợ
                </h4>
                <ul class="space-y-3 text-slate-400 text-sm">
                    <li><a href="#" class="hover:text-white hover:translate-x-1 transition-all duration-200 flex items-center gap-2 group">
                        <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-red-400 transition-colors"></i> Trung tâm trợ giúp
                    </a></li>
                    <li><a href="#" class="hover:text-white hover:translate-x-1 transition-all duration-200 flex items-center gap-2 group">
                        <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-red-400 transition-colors"></i> Câu hỏi thường gặp
                    </a></li>
                    <li><a href="#" class="hover:text-white hover:translate-x-1 transition-all duration-200 flex items-center gap-2 group">
                        <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-red-400 transition-colors"></i> Liên hệ
                    </a></li>
                    <li><a href="#" class="hover:text-white hover:translate-x-1 transition-all duration-200 flex items-center gap-2 group">
                        <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-red-400 transition-colors"></i> Báo lỗi
                    </a></li>
                </ul>
            </div>

            <!-- Chính Sách -->
            <div>
                <h4 class="text-white font-bold mb-5 text-sm uppercase tracking-widest flex items-center gap-2">
                    <span class="w-4 h-0.5 bg-red-500 rounded-full"></span>
                    Chính Sách
                </h4>
                <ul class="space-y-3 text-slate-400 text-sm">
                    <li><a href="#" class="hover:text-white hover:translate-x-1 transition-all duration-200 flex items-center gap-2 group">
                        <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-red-400 transition-colors"></i> Điều khoản sử dụng
                    </a></li>
                    <li><a href="#" class="hover:text-white hover:translate-x-1 transition-all duration-200 flex items-center gap-2 group">
                        <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-red-400 transition-colors"></i> Chính sách bảo mật
                    </a></li>
                    <li><a href="#" class="hover:text-white hover:translate-x-1 transition-all duration-200 flex items-center gap-2 group">
                        <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-red-400 transition-colors"></i> Chính sách hoàn vé
                    </a></li>
                    <li><a href="#" class="hover:text-white hover:translate-x-1 transition-all duration-200 flex items-center gap-2 group">
                        <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-red-400 transition-colors"></i> Điều khoản thanh toán
                    </a></li>
                </ul>
            </div>

            <!-- Liên Hệ -->
            <div class="lg:col-span-2">
                <h4 class="text-white font-bold mb-5 text-sm uppercase tracking-widest flex items-center gap-2">
                    <span class="w-4 h-0.5 bg-red-500 rounded-full"></span>
                    Liên Hệ
                </h4>
                <ul class="space-y-4 text-slate-400 text-sm">
                    <li class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-red-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-map-marker-alt text-red-400 text-xs"></i>
                        </span>
                        <span>123 Đường Điện Ảnh, Quận 1,<br>TP. Hồ Chí Minh</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-red-500/15 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-phone-alt text-red-400 text-xs"></i>
                        </span>
                        <a href="tel:19001234" class="hover:text-white transition-colors font-semibold">1900 1234</a>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-red-500/15 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-red-400 text-xs"></i>
                        </span>
                        <a href="mailto:support@moviego.vn" class="hover:text-white transition-colors">support@moviego.vn</a>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-red-500/15 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-red-400 text-xs"></i>
                        </span>
                        <span>08:00 – 22:00 · Tất cả các ngày</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-white/5 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">

                <!-- Payment Methods -->
                <div>
                    <h5 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-3 text-center md:text-left">Phương Thức Thanh Toán</h5>
                    <div class="flex flex-wrap gap-2 justify-center md:justify-start items-center">
                        <span class="px-3 py-1.5 rounded-lg border border-slate-700 text-slate-400 hover:border-blue-500/60 hover:text-blue-400 transition-all text-xl cursor-help" title="Visa">
                            <i class="fab fa-cc-visa"></i>
                        </span>
                        <span class="px-3 py-1.5 rounded-lg border border-slate-700 text-slate-400 hover:border-orange-500/60 hover:text-orange-400 transition-all text-xl cursor-help" title="MasterCard">
                            <i class="fab fa-cc-mastercard"></i>
                        </span>
                        <span class="px-3 py-1.5 rounded-lg border border-slate-700 text-slate-400 hover:border-blue-400/60 hover:text-blue-300 transition-all text-xs font-bold tracking-wider cursor-help" title="VNPAY">VNPAY</span>
                        <span class="px-3 py-1.5 rounded-lg border border-slate-700 text-slate-400 hover:border-pink-500/60 hover:text-pink-400 transition-all text-xs font-bold cursor-help" title="MoMo">MoMo</span>
                        <span class="px-3 py-1.5 rounded-lg border border-slate-700 text-slate-400 hover:border-blue-400/60 hover:text-blue-300 transition-all text-xs font-bold cursor-help" title="ZaloPay">ZaloPay</span>
                    </div>
                </div>

                <!-- Social Links -->
                <div>
                    <h5 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-3 text-center md:text-right">Kết Nối Với Chúng Tôi</h5>
                    <div class="flex gap-3 justify-center md:justify-end">
                        <a href="#" class="w-10 h-10 rounded-xl border border-white/10 bg-white/5 flex items-center justify-center text-slate-400 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all duration-300 hover:scale-110 hover:-translate-y-0.5">
                            <i class="fab fa-facebook-f text-sm"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl border border-white/10 bg-white/5 flex items-center justify-center text-slate-400 hover:bg-gradient-to-br hover:from-purple-500 hover:to-pink-500 hover:text-white hover:border-transparent transition-all duration-300 hover:scale-110 hover:-translate-y-0.5">
                            <i class="fab fa-instagram text-sm"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl border border-white/10 bg-white/5 flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-black hover:border-slate-100 transition-all duration-300 hover:scale-110 hover:-translate-y-0.5">
                            <i class="fab fa-tiktok text-sm"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl border border-white/10 bg-white/5 flex items-center justify-center text-slate-400 hover:bg-red-600 hover:text-white hover:border-red-600 transition-all duration-300 hover:scale-110 hover:-translate-y-0.5">
                            <i class="fab fa-youtube text-sm"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="text-center mt-8 pt-6 border-t border-white/5">
                <p class="text-slate-600 text-xs tracking-wide">
                    &copy; {{ date('Y') }} <span class="text-slate-400">MovieGo</span>. All Rights Reserved. · Crafted with <span class="text-red-500">♥</span> for Cinema Lovers
                </p>
            </div>
        </div>
    </div>
</footer>
