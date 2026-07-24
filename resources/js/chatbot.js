document.addEventListener('DOMContentLoaded', function () {
    const chatToggle = document.getElementById('ai-chat-toggle');
    const chatClose = document.getElementById('ai-chat-close');
    const chatWindow = document.getElementById('ai-chat-window');
    const chatBody = document.getElementById('ai-chat-body');
    const chatForm = document.getElementById('ai-chat-form');
    const chatInput = document.getElementById('ai-chat-input');
    const chatSubmit = document.getElementById('ai-chat-submit');
    const chatLoading = document.getElementById('ai-chat-loading');

    // Toggle Chat Window
    function toggleChat() {
        if (!chatWindow) return;

        if (chatWindow.classList.contains('opacity-0')) {
            // Open Chat
            chatWindow.classList.remove('opacity-0', 'scale-90', 'pointer-events-none');
            chatWindow.classList.add('opacity-100', 'scale-100', 'pointer-events-auto');
            scrollToBottom();
            if (chatInput) {
                chatInput.focus();
            }
        } else {
            // Close Chat
            chatWindow.classList.add('opacity-0', 'scale-90', 'pointer-events-none');
            chatWindow.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
        }
    }

    // Scroll chat body to bottom
    function scrollToBottom() {
        if (chatBody) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    }

    if (chatToggle) chatToggle.addEventListener('click', toggleChat);
    if (chatClose) chatClose.addEventListener('click', toggleChat);

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Format message text with line breaks
    function formatMessage(text) {
        return escapeHtml(text).replace(/\n/g, '<br>');
    }

    // Create User Message Element
    function appendUserMessage(text) {
        if (!chatBody) return;
        const msgDiv = document.createElement('div');
        msgDiv.className = 'flex gap-3 max-w-[85%] self-end flex-row-reverse';
        msgDiv.innerHTML = `
            <div class="bg-slate-900 text-white p-3 rounded-2xl rounded-tr-sm shadow-sm text-sm break-words">
                ${formatMessage(text)}
            </div>
        `;
        chatBody.appendChild(msgDiv);
        scrollToBottom();
    }

    // Create AI Message Element
    function appendAiMessage(text) {
        if (!chatBody) return;
        const msgDiv = document.createElement('div');
        msgDiv.className = 'flex gap-3 max-w-[90%]';
        msgDiv.innerHTML = `
            <div class="w-7 h-7 bg-slate-900 rounded-full flex items-center justify-center shrink-0 mt-1">
                <span class="text-xs">🤖</span>
            </div>
            <div class="bg-white border border-gray-100 p-3.5 rounded-2xl rounded-tl-sm shadow-sm text-sm text-gray-700 leading-relaxed break-words">
                ${formatMessage(text)}
            </div>
        `;
        chatBody.appendChild(msgDiv);
        scrollToBottom();
    }

    // Show Loading Indicator
    function showLoading() {
        if (chatLoading && chatBody) {
            chatLoading.classList.remove('hidden');
            chatBody.appendChild(chatLoading); // Move loading element to the end of messages
            scrollToBottom();
        }
    }

    // Hide Loading Indicator
    function hideLoading() {
        if (chatLoading) {
            chatLoading.classList.add('hidden');
        }
    }

    // Handle Enter and Shift+Enter in Input / Textarea
    if (chatInput) {
        chatInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (chatForm) {
                    chatForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                }
            }
        });
    }

    // Handle AJAX Form Submit
    if (chatForm) {
        chatForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!chatInput) return;

            const message = chatInput.value.trim();
            if (!message) return;

            // Clear welcome message if present
            const welcomeMsg = chatBody ? chatBody.querySelector('.welcome-message') : null;
            if (welcomeMsg) {
                welcomeMsg.remove();
            }

            // Disable input and submit button
            chatInput.value = '';
            chatInput.disabled = true;
            if (chatSubmit) chatSubmit.disabled = true;

            // Display user message
            appendUserMessage(message);

            // Show loading
            showLoading();

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                    || chatForm.querySelector('input[name="_token"]')?.value;

                const response = await fetch(chatForm.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();

                hideLoading();

                if (response.ok && data.success) {
                    appendAiMessage(data.message);
                } else {
                    const errorMsg = data.message || 'Xin lỗi, hiện tại chatbot đang gặp sự cố. Vui lòng thử lại.';
                    appendAiMessage(errorMsg);
                }
            } catch (error) {
                console.error('Chatbot error:', error);
                hideLoading();
                appendAiMessage('Xin lỗi, hiện tại chatbot đang gặp sự cố. Vui lòng thử lại.');
            } finally {
                // Re-enable input and submit button
                chatInput.disabled = false;
                if (chatSubmit) chatSubmit.disabled = false;
                chatInput.focus();
                scrollToBottom();
            }
        });
    }
});
