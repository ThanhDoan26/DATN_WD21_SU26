@php
    $chatMessages = collect();
    if (auth()->check()) {
        $conversation = app(\App\Services\AI\ConversationService::class)->getOrCreateConversation(auth()->user());
        $chatMessages = \App\Models\ChatMessage::where('conversation_id', $conversation->id)->orderBy('created_at', 'asc')->get();
    }
@endphp
<div id="ai-chatbot-widget" class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-4 font-sans">
    
    {{-- Chat Window --}}
    <div id="ai-chat-window" class="w-[360px] max-w-[calc(100vw-2rem)] h-[550px] max-h-[calc(100vh-8rem)] bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-gray-200 flex flex-col overflow-hidden transition-all duration-300 origin-bottom-right opacity-0 scale-90 pointer-events-none">
        
        {{-- Header --}}
        <div class="bg-slate-900 text-white p-4 flex items-center justify-between shrink-0 shadow-sm relative z-10">
            <div class="flex items-center gap-3">
                {{-- Avatar --}}
                <div class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center border border-slate-700 shrink-0 shadow-inner">
                    <span class="text-xl">🎬</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm tracking-wide">AI MovieGo Assistant</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Hỏi về phim, suất chiếu và vé.</p>
                </div>
            </div>
            <button type="button" id="ai-chat-close" class="text-slate-400 hover:text-white hover:bg-slate-800 p-1.5 rounded-lg transition-colors" aria-label="Close Chat">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 p-4 overflow-y-auto bg-gray-50 flex flex-col gap-4 chatbot-scrollbar" id="ai-chat-body">
            @if(session('chat_error'))
                <div class="bg-red-100 text-red-700 p-2 rounded-xl text-xs mb-1 text-center font-medium">{{ session('chat_error') }}</div>
            @endif

            @if($chatMessages->isEmpty())
                {{-- Welcome Message --}}
                <div class="flex gap-3 max-w-[90%]">
                    <div class="w-7 h-7 bg-slate-900 rounded-full flex items-center justify-center shrink-0 mt-1">
                        <span class="text-xs">🤖</span>
                    </div>
                    <div class="bg-white border border-gray-100 p-3.5 rounded-2xl rounded-tl-sm shadow-sm text-sm text-gray-700 leading-relaxed">
                        <p class="font-semibold text-gray-900 mb-2 text-base">Xin chào 👋</p>
                        <p class="mb-2 text-gray-600">Tôi có thể giúp bạn:</p>
                        <ul class="space-y-2 text-gray-700 font-medium ml-1">
                            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span> Phim đang chiếu</li>
                            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span> Phim sắp chiếu</li>
                            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span> Suất chiếu</li>
                            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span> Vé của bạn</li>
                            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span> Khuyến mãi</li>
                        </ul>
                    </div>
                </div>
            @else
                @foreach($chatMessages as $msg)
                    @if($msg->role === 'user')
                        {{-- User Message --}}
                        <div class="flex gap-3 max-w-[85%] self-end flex-row-reverse">
                            <div class="bg-slate-900 text-white p-3 rounded-2xl rounded-tr-sm shadow-sm text-sm">
                                {{ $msg->message }}
                            </div>
                        </div>
                    @else
                        {{-- AI Message --}}
                        <div class="flex gap-3 max-w-[90%]">
                            <div class="w-7 h-7 bg-slate-900 rounded-full flex items-center justify-center shrink-0 mt-1">
                                <span class="text-xs">🤖</span>
                            </div>
                            <div class="bg-white border border-gray-100 p-3.5 rounded-2xl rounded-tl-sm shadow-sm text-sm text-gray-700 leading-relaxed">
                                {!! nl2br(e($msg->message)) !!}
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>

        {{-- Footer --}}
        <div class="p-4 bg-white border-t border-gray-100 shrink-0">
            @auth
            <form action="{{ route('chat.web') }}" method="POST" class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-full pl-4 pr-1.5 py-1.5 focus-within:border-slate-400 focus-within:ring-2 focus-within:ring-slate-100 transition-all shadow-sm">
                @csrf
                <input type="text" name="message" placeholder="Hãy hỏi về phim..." required class="w-full bg-transparent border-0 focus:ring-0 text-sm text-gray-700 placeholder-gray-400 outline-none" autocomplete="off" />
                <button type="submit" class="bg-slate-900 hover:bg-red-600 text-white w-9 h-9 rounded-full transition-colors flex items-center justify-center shrink-0" aria-label="Gửi">
                    <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
            @else
            <div class="text-center text-sm text-gray-500 py-1.5">
                Vui lòng <a href="{{ route('login') }}" class="text-red-600 hover:underline font-medium">đăng nhập</a> để chat.
            </div>
            @endauth
        </div>
    </div>

    {{-- Floating Button --}}
    <button type="button" id="ai-chat-toggle" class="w-14 h-14 bg-slate-900 hover:bg-slate-800 text-white rounded-full flex items-center justify-center shadow-[0_4px_14px_0_rgb(0,0,0,0.39)] hover:shadow-[0_6px_20px_rgba(0,0,0,0.23)] border border-slate-700 transition-all cursor-pointer relative z-50">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
        </svg>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatToggle = document.getElementById('ai-chat-toggle');
        const chatClose = document.getElementById('ai-chat-close');
        const chatWindow = document.getElementById('ai-chat-window');
        const chatBody = document.getElementById('ai-chat-body');

        function toggleChat() {
            if (chatWindow.classList.contains('opacity-0')) {
                // Open Chat
                chatWindow.classList.remove('opacity-0', 'scale-90', 'pointer-events-none');
                chatWindow.classList.add('opacity-100', 'scale-100', 'pointer-events-auto');
                scrollToBottom();
            } else {
                // Close Chat
                chatWindow.classList.add('opacity-0', 'scale-90', 'pointer-events-none');
                chatWindow.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
            }
        }

        function scrollToBottom() {
            if (chatBody) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        }

        if (chatToggle) chatToggle.addEventListener('click', toggleChat);
        if (chatClose) chatClose.addEventListener('click', toggleChat);

        // Giữ cửa sổ chat mở sau khi submit form
        @if(session('chat_open'))
            chatWindow.classList.remove('opacity-0', 'scale-90', 'pointer-events-none');
            chatWindow.classList.add('opacity-100', 'scale-100', 'pointer-events-auto');
            scrollToBottom();
        @endif
    });
</script>