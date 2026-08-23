@extends('layouts.frontend')

@section('title', $cinema->name . ' - MovieGo')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-12">
        <div class="bg-slate-900 p-6 rounded-2xl border border-slate-700">
            <div class="flex items-start gap-6">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white">{{ $cinema->name }}</h1>
                    <p class="text-slate-400 mt-2">{{ $cinema->address }}</p>
                </div>
                <div class="text-right">
                    <a href="{{ route('booking.select-cinema', '') }}" class="text-sm text-primary">Xem lịch chiếu</a>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <h2 class="text-xl font-bold text-white mb-4">Phản Hồi về rạp</h2>

                    @auth
                        @if($canReview)
                            <div class="bg-slate-800 p-4 rounded-xl mb-6">
                                <form action="{{ route('cinemas.reviews.store', $cinema->id) }}" method="POST" id="cinema-review-form">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="block text-slate-300">Đánh giá (1-5)</label>
                                        <select name="rating" class="form-select bg-slate-900 border border-slate-700 text-white p-2 rounded">
                                            @for($i=1;$i<=5;$i++)
                                                <option value="{{ $i }}">{{ $i }} sao</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-slate-300">Phản hồi</label>
                                        <textarea name="comment" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded p-3 text-white" placeholder="Chia sẻ trải nghiệm tại rạp..."></textarea>
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" class="bg-primary px-4 py-2 rounded text-white font-bold">Gửi phản hồi</button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="bg-slate-800 p-4 rounded-xl mb-6 text-slate-300">
                                Bạn chỉ có thể gửi phản hồi sau khi đã đến rạp này.
                            </div>
                        @endif
                    @else
                        <div class="bg-slate-800 p-4 rounded-xl mb-6 text-slate-300">
                            Vui lòng <a href="{{ route('login') }}" class="text-primary">đăng nhập</a> để gửi phản hồi.
                        </div>
                    @endauth

                    <div id="cinema-reviews-list" class="space-y-4">
                        @forelse($reviews as $review)
                            @include('movies.partials.cinema_review_item', ['cReview' => $review])
                        @empty
                            <div class="text-slate-400">Chưa có phản hồi nào.</div>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-white mb-3">Thông tin rạp</h3>
                    <div class="bg-slate-800 p-4 rounded-lg text-slate-300">
                        <p><strong>Địa chỉ:</strong> {{ $cinema->address }}</p>
                        <p class="mt-2"><strong>Thành phố:</strong> {{ $cinema->city }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
