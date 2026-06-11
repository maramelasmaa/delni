{{-- Chatbot V3: Conversational Stateful Widget --}}
<div id="delni-chatbot" class="delni-chatbot" data-api-url="{{ route('api.chat.v3.message') }}">
    {{-- Float Button --}}
    <button class="chatbot-toggle" aria-label="فتح محادثة المساعد" onclick="window.delniChatbot?.toggle()">
        <svg xmlns="http://www.w3.org/2000/svg" class="chatbot-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <span class="pulse-ring"></span>
    </button>

    {{-- Chat Panel --}}
    <div class="chatbot-panel hidden">
        {{-- Header --}}
        <div class="chatbot-header">
            <h3>مساعد دلني الذكي</h3>
            <button class="close-btn" onclick="window.delniChatbot?.toggle()" aria-label="إغلاق">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Messages Container --}}
        <div class="chatbot-messages" id="messages">
            <div class="message bot-message">
                <p>السلام عليكم! 👋 أنا مساعدك الذكي في دلني. كيف يمكنني مساعدتك في البحث عن خدمات اليوم؟</p>
            </div>

            {{-- Quick Chips --}}
            <div class="quick-chips">
                <button class="chip" onclick="window.delniChatbot?.sendQuickMessage('نبي محامي')">نبي محامي ⚖️</button>
                <button class="chip" onclick="window.delniChatbot?.sendQuickMessage('فني تكييف')">فني تكييف 🔧</button>
                <button class="chip" onclick="window.delniChatbot?.sendQuickMessage('مصور أفراح')">مصور أفراح 📸</button>
                <button class="chip" onclick="window.delniChatbot?.sendQuickMessage('مقاول بناء')">مقاول بناء 🏗️</button>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="chatbot-input-area">
            <div class="input-wrapper">
                <input
                    type="text"
                    id="chatbot-input"
                    placeholder="اكتب اسم الخدمة التي تبحث عنها..."
                    class="chat-input"
                    onkeypress="if(event.key === 'Enter') window.delniChatbot?.send()"
                />
                <button class="send-btn" onclick="window.delniChatbot?.send()" aria-label="إرسال">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5.951-2.976 5.951 2.976a1 1 0 001.169-1.409l-7-14z" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Footer --}}
        <div class="chatbot-footer">
            <button class="reset-btn" onclick="window.delniChatbot?.reset()" aria-label="إعادة تعيين">
                إعادة تعيين ⟳
            </button>
        </div>
    </div>
</div>

<style>
    [dir="rtl"] .delni-chatbot {
        direction: rtl;
    }

    .delni-chatbot {
        --primary: #f97316;
        --secondary: #1f2937;
        --light: #f3f4f6;
        --border: #e5e7eb;
    }

    .chatbot-toggle {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        z-index: 999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chatbot-toggle:hover {
        background: #ea580c;
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
    }

    .chatbot-icon {
        width: 32px;
        height: 32px;
    }

    .pulse-ring {
        position: absolute;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 2px solid var(--primary);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        100% {
            transform: scale(1.5);
            opacity: 0;
        }
    }

    .chatbot-panel {
        position: fixed;
        bottom: 100px;
        right: 24px;
        width: 380px;
        height: 600px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 40px rgba(0, 0, 0, 0.16);
        display: flex;
        flex-direction: column;
        z-index: 998;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .chatbot-panel.hidden {
        display: none;
        opacity: 0;
        transform: translateY(10px);
    }

    .chatbot-header {
        padding: 20px;
        background: linear-gradient(135deg, var(--secondary) 0%, #374151 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }

    .chatbot-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .close-btn {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 4px;
        transition: transform 0.2s;
    }

    .close-btn:hover {
        transform: rotate(90deg);
    }

    .chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: white;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .message {
        display: flex;
        margin-bottom: 8px;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .bot-message {
        justify-content: flex-start;
    }

    .bot-message p {
        background: var(--light);
        padding: 10px 14px;
        border-radius: 12px;
        border-top-left-radius: 4px;
        margin: 0;
        font-size: 14px;
        color: var(--secondary);
        max-width: 85%;
        word-wrap: break-word;
    }

    .user-message {
        justify-content: flex-end;
    }

    .user-message p {
        background: var(--primary);
        color: white;
        padding: 10px 14px;
        border-radius: 12px;
        border-top-right-radius: 4px;
        margin: 0;
        font-size: 14px;
        max-width: 85%;
        word-wrap: break-word;
    }

    .quick-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .chip {
        background: var(--light);
        border: 1px solid var(--border);
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .chip:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .provider-card {
        background: var(--light);
        padding: 12px;
        border-radius: 8px;
        margin: 8px 0;
        border-left: 3px solid var(--primary);
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid var(--border);
        display: block;
        text-decoration: none;
        color: inherit;
    }

    .provider-card:hover {
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        border-left-color: #ea580c;
        border-left-width: 4px;
    }

    .provider-card-header {
        display: flex;
        align-items: start;
        gap: 10px;
        margin-bottom: 8px;
    }

    .provider-logo {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        background: white;
        object-fit: cover;
        flex-shrink: 0;
    }

    .provider-info {
        flex: 1;
    }

    .provider-info h4 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: var(--secondary);
    }

    .provider-info p {
        margin: 2px 0 0;
        font-size: 12px;
        color: #6b7280;
    }

    .provider-rating {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        margin-top: 6px;
    }

    .stars {
        color: #fbbf24;
    }

    .provider-badges {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
        margin-top: 6px;
    }

    .provider-badge {
        background: var(--primary);
        color: white;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
        white-space: nowrap;
    }

    .chatbot-input-area {
        padding: 16px;
        border-top: 1px solid var(--border);
        background: white;
    }

    .input-wrapper {
        display: flex;
        gap: 8px;
        background: var(--light);
        border-radius: 8px;
        padding: 4px;
    }

    .chat-input {
        flex: 1;
        border: none;
        background: none;
        padding: 8px 12px;
        font-size: 14px;
        outline: none;
        font-family: inherit;
    }

    .send-btn {
        background: var(--primary);
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .send-btn:hover {
        background: #ea580c;
    }

    .chatbot-footer {
        padding: 12px 16px;
        border-top: 1px solid var(--border);
        background: var(--light);
    }

    .reset-btn {
        background: none;
        border: 1px solid var(--border);
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
    }

    .reset-btn:hover {
        background: white;
        border-color: var(--primary);
        color: var(--primary);
    }

    .loading {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--primary);
        margin: 0 4px;
        animation: loading 1.4s infinite;
    }

    .loading:nth-child(2) {
        animation-delay: 0.2s;
    }

    .loading:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes loading {
        0%, 80%, 100% {
            opacity: 0.5;
        }
        40% {
            opacity: 1;
        }
    }

    @media (max-width: 480px) {
        .chatbot-panel {
            width: calc(100vw - 32px);
            height: 70vh;
            max-height: 600px;
        }

        .chatbot-toggle {
            bottom: 16px;
            right: 16px;
        }
    }
</style>

<script>
    window.delniChatbot = {
        conversationId: null,
        apiUrl: '{{ route("api.chat.v3.message") }}',
        initUrl: '{{ route("api.chat.v3.init") }}',
        resetUrl: '{{ route("api.chat.v3.reset") }}',

        async init() {
            try {
                const response = await fetch(this.initUrl);
                const data = await response.json();
                this.conversationId = data.conversation_id;
            } catch (error) {
                console.error('Failed to initialize chatbot:', error);
            }
        },

        toggle() {
            const panel = document.querySelector('.chatbot-panel');
            panel.classList.toggle('hidden');
        },

        async send() {
            const input = document.getElementById('chatbot-input');
            const message = input.value.trim();

            if (!message) return;

            this.addMessage(message, 'user');
            input.value = '';

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta?.content ?? '';

                const response = await fetch(this.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...(csrfToken && { 'X-CSRF-Token': csrfToken }),
                    },
                    body: JSON.stringify({
                        message,
                        conversation_id: this.conversationId,
                    }),
                });

                if (!response.ok) {
                    const data = await response.json();

                    // Handle rate limiting (429)
                    if (response.status === 429) {
                        const errorMessage = data?.error || `تم تجاوز الحد المسموح. الرجاء الانتظار ${data?.retry_after_minutes || 1} دقيقة.`;
                        this.addMessage(errorMessage, 'bot');
                        return;
                    }

                    // Handle other errors
                    const errorMessage = data?.error || 'حدث خطأ. الرجاء المحاولة لاحقاً.';
                    this.addMessage(errorMessage, 'bot');
                    return;
                }

                const data = await response.json();
                this.conversationId = data.conversation_id || this.conversationId;

                // Handle different response types
                let botMessage = '';

                if (data.type === 'greeting') {
                    // Greeting response
                    botMessage = data.message || 'وعليكم السلام!';
                } else if (data.type === 'clarification') {
                    // Clarification needed response
                    botMessage = data.question || 'هل يمكنك توضيح أكثر؟';
                } else if (data.type === 'no_results') {
                    // No results found
                    botMessage = data.message || 'لم نجد نتائج مطابقة.';
                } else if (data.type === 'results') {
                    // Results found
                    botMessage = data.message || `لقيتلك ${data.count} مقدمي خدمة:`;
                } else {
                    // Fallback for unknown response type
                    botMessage = data.message || 'حدث خطأ مؤقت في المساعد.';
                }

                this.addMessage(botMessage, 'bot');

                // Render provider cards if available
                if (data?.providers && data.providers.length > 0) {
                    data.providers.forEach(provider => {
                        this.addProviderCard(provider);
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                this.addMessage('حدث خطأ. الرجاء التحقق من الاتصال.', 'bot');
            }
        },

        sendQuickMessage(message) {
            document.getElementById('chatbot-input').value = message;
            this.send();
        },

        addMessage(text, sender) {
            const messagesContainer = document.getElementById('messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}-message`;

            const p = document.createElement('p');
            p.textContent = text;
            messageDiv.appendChild(p);

            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        },

        addProviderCard(provider) {
            const messagesContainer = document.getElementById('messages');

            // Create clickable link
            const cardLink = document.createElement('a');
            cardLink.href = provider.url || '#';
            cardLink.className = 'provider-card';
            cardLink.target = '_blank';
            cardLink.onclick = (e) => {
                if (provider.url) {
                    window.location.href = provider.url;
                }
                return false;
            };

            let ratingHtml = '';
            if (provider.rating_avg) {
                const stars = '⭐'.repeat(Math.floor(provider.rating_avg));
                ratingHtml = `<div class="provider-rating"><span class="stars">${stars}</span> ${provider.rating_avg} (${provider.reviews_count})</div>`;
            }

            let badgesHtml = '';
            if (provider.badges && provider.badges.length > 0) {
                const badgeElements = provider.badges.map(badge =>
                    `<span class="provider-badge">${badge}</span>`
                ).join('');
                badgesHtml = `<div class="provider-badges">${badgeElements}</div>`;
            }

            cardLink.innerHTML = `
                <div class="provider-card-header">
                    ${provider.logo_url ? `<img src="${provider.logo_url}" alt="${provider.business_name}" class="provider-logo">` : '<div style="width: 40px; height: 40px; border-radius: 4px; background: #e5e7eb;"></div>'}
                    <div class="provider-info">
                        <h4>${provider.business_name}</h4>
                        <p>${provider.category} • ${provider.city}</p>
                        ${ratingHtml}
                        ${badgesHtml}
                    </div>
                </div>
            `;

            const messageDiv = document.createElement('div');
            messageDiv.className = 'message bot-message';
            messageDiv.appendChild(cardLink);

            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        },

        async reset() {
            if (!confirm('هل تريد إعادة تعيين المحادثة؟')) return;

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta?.content ?? '';

                const response = await fetch(this.resetUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...(csrfToken && { 'X-CSRF-Token': csrfToken }),
                    },
                    body: JSON.stringify({
                        conversation_id: this.conversationId,
                        message: 'reset',
                    }),
                });

                const data = await response.json();
                this.conversationId = data?.conversation_id || 'chat_' + Date.now();

                const messagesContainer = document.getElementById('messages');
                messagesContainer.innerHTML = `
                    <div class="message bot-message">
                        <p>السلام عليكم! 👋 أنا مساعدك الذكي في دلني. كيف يمكنني مساعدتك في البحث عن خدمات اليوم؟</p>
                    </div>
                `;

                document.getElementById('chatbot-input').value = '';
            } catch (error) {
                console.error('Error resetting chat:', error);
                alert('فشل إعادة تعيين المحادثة. حاول مرة أخرى.');
            }
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.delniChatbot.init();
    });
</script>
