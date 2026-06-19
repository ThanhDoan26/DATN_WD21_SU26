<div class="max-w-4xl mx-auto backdrop-blur-xl bg-slate-900/60 border border-slate-700/50 p-6 rounded-2xl shadow-2xl mt-12 mb-4 relative z-20 transition-all hover:bg-slate-900/70">
    <form action="{{ route('home') }}" method="GET">
        <div class="flex flex-col gap-4">
            <!-- Keyword -->
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm tên phim..." class="w-full bg-slate-800/80 border border-slate-600 text-white rounded-xl py-3 pl-12 pr-4 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none hover:border-slate-500">
            </div>

            <!-- Filters Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Status -->
                <div class="relative">
                    <select name="status" class="w-full bg-slate-800/80 border border-slate-600 text-white rounded-xl py-3 px-4 appearance-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none hover:border-slate-500 cursor-pointer">
                        <option value="">Tất cả trạng thái</option>
                        <option value="NOW_SHOWING" {{ request('status') == 'NOW_SHOWING' ? 'selected' : '' }}>Đang chiếu</option>
                        <option value="COMING_SOON" {{ request('status') == 'COMING_SOON' ? 'selected' : '' }}>Sắp chiếu</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>

                    <!-- Removed Cinemas completely per user intent -->

                <!-- Categories -->
                <div class="relative">
                    <select name="genre_id" class="w-full bg-slate-800/80 border border-slate-600 text-white rounded-xl py-3 px-4 appearance-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none hover:border-slate-500 cursor-pointer">
                        <option value="">Tất cả thể loại</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('genre_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                <div class="relative" style="margin-left: 40px;">
                    <button type="submit" class="bg-primary hover:bg-red-700 text-white px-8 py-3 rounded-xl font-semibold transition-all shadow-lg shadow-red-500/30 flex items-center gap-2 transform hover:-translate-y-1">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            
        </div>
    </form>
</div>
