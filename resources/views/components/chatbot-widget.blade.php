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

    /* Provider Card Styles */
    .provider-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .provider-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .provider-card:hover {
        border-color: #f97316;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.15);
        transform: translateY(-2px);
    }

    .provider-card-top {
        display: flex;
        gap: 12px;
        margin-bottom: 12px;
    }

    .provider-avatar {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .provider-avatar-placeholder {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        flex-shrink: 0;
    }

    .provider-header {
        flex: 1;
        min-width: 0;
    }

    .provider-name {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #1f2937;
        word-break: break-word;
    }

    .provider-category {
        margin: 4px 0 0 0;
        font-size: 12px;
        color: #6b7280;
    }

    .provider-details {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
    }

    .detail-item {
        font-size: 12px;
        color: #4b5563;
    }

    .detail-item strong {
        color: #1f2937;
        font-weight: 600;
    }

    .provider-rating {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
        font-size: 12px;
    }

    .stars {
        color: #fbbf24;
        font-size: 13px;
        letter-spacing: 1px;
    }

    .rating-value {
        font-weight: 700;
        color: #1f2937;
    }

    .review-count {
        color: #6b7280;
    }

    .type-badge {
        display: inline-block;
        background: #f0f9ff;
        color: #0369a1;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    .provider-footer {
        text-align: center;
        padding-top: 12px;
        border-top: 1px solid #f3f4f6;
    }

    .view-profile {
        font-size: 13px;
        font-weight: 600;
        color: #f97316;
        transition: color 0.2s ease;
    }

    .provider-card:hover .view-profile {
        color: #ea580c;
    }

    /* Email Prompt for Rate Limit Upgrade */
    .email-prompt {
        background: #f0f9ff;
        border-left: 4px solid #0284c7;
        padding: 0 !important;
        margin: 8px 0;
    }

    .email-container {
        padding: 16px;
        color: #0c4a6e;
    }

    .email-header {
        margin: 0 0 12px 0;
        font-weight: 600;
        font-size: 14px;
        color: #0c4a6e;
    }

    .email-form {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }

    .email-input {
        flex: 1;
        padding: 10px 12px;
        border: 1px solid #0284c7;
        border-radius: 6px;
        font-size: 13px;
        font-family: inherit;
        box-sizing: border-box;
    }

    .email-input:focus {
        outline: none;
        border-color: #0369a1;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }

    .email-submit-btn {
        padding: 10px 16px;
        background: #0284c7;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: background 0.2s ease;
        white-space: nowrap;
    }

    .email-submit-btn:hover {
        background: #0369a1;
    }

    .email-fallback {
        margin: 0;
        text-align: center;
    }

    .fallback-btn {
        background: none;
        border: none;
        color: #0284c7;
        font-size: 12px;
        cursor: pointer;
        text-decoration: underline;
        padding: 4px 8px;
    }

    .fallback-btn:hover {
        color: #0369a1;
    }

    .signup-cta {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        border-radius: 12px;
        padding: 0 !important;
        margin: 8px 0;
    }

    .signup-container {
        padding: 16px;
        color: white;
    }

    .signup-text {
        margin: 0 0 12px 0;
        font-weight: 600;
        font-size: 14px;
        color: white;
    }

    .signup-buttons {
        display: flex;
        gap: 8px;
        flex-direction: column;
    }

    .signup-buttons .btn {
        flex: 1;
        padding: 10px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        text-align: center;
        transition: all 0.2s ease;
        display: inline-block;
        cursor: pointer;
        border: none;
    }

    .signup-buttons .btn-primary {
        background: white;
        color: #f97316;
    }

    .signup-buttons .btn-primary:hover {
        background: #f3f4f6;
        transform: translateY(-1px);
    }

    .signup-buttons .btn-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid white;
    }

    .signup-buttons .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
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

        .signup-buttons {
            flex-direction: row;
        }

        .signup-buttons .btn {
            flex: 1;
        }
    }
</style>

<script>
    window.delniChatbot = {
        conversationId: null,
        lastEmail: null,
        lastMessage: null,
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

            this.lastMessage = message; // Store for retry after email submission
            this.addMessage(message, 'user');
            input.value = '';

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta?.content ?? '';

                const body = {
                    message,
                    conversation_id: this.conversationId,
                };

                // Include email if provided
                if (this.lastEmail) {
                    body.email = this.lastEmail;
                }

                const response = await fetch(this.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...(csrfToken && { 'X-CSRF-Token': csrfToken }),
                    },
                    body: JSON.stringify(body),
                });

                if (!response.ok) {
                    const data = await response.json();

                    // Handle rate limiting (429)
                    if (response.status === 429) {
                        const errorMessage = data?.error || `تم تجاوز الحد المسموح.`;
                        this.addMessage(errorMessage, 'bot');

                        // Show email input for guests
                        if (data?.email_prompt) {
                            this.addEmailPrompt(data);
                        }
                        // Show sign-up CTA for authenticated users
                        else if (data?.signup_url || data?.login_url) {
                            this.addSignupCTA(data);
                        }
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

        addEmailPrompt(data) {
            const messagesContainer = document.getElementById('messages');
            const emailDiv = document.createElement('div');
            emailDiv.className = 'message bot-message email-prompt';

            emailDiv.innerHTML = `
                <div class="email-container">
                    <p class="email-header">${data.prompt_message || 'أدخل بريدك الإلكتروني'}</p>
                    <div class="email-form">
                        <input
                            type="email"
                            class="email-input"
                            placeholder="your@email.com"
                            id="rate-limit-email"
                        />
                        <button class="email-submit-btn" onclick="window.delniChatbot?.submitEmailAndRetry()">
                            احصل على 60 رسالة/يوم
                        </button>
                    </div>
                    <p class="email-fallback">
                        <button class="fallback-btn" onclick="window.delniChatbot?.addMessage('${data.fallback_option}', 'info')">
                            ${data.fallback_option || 'المتابعة بدون بريد'}
                        </button>
                    </p>
                </div>
            `;

            messagesContainer.appendChild(emailDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            document.getElementById('rate-limit-email').focus();
        },

        async submitEmailAndRetry() {
            const emailInput = document.getElementById('rate-limit-email');
            const email = emailInput?.value?.trim();

            if (!email || !this.isValidEmail(email)) {
                alert('البريد الإلكتروني غير صحيح');
                return;
            }

            // Store email for next requests
            this.lastEmail = email;

            // Retry the last message with email
            if (this.lastMessage) {
                const input = document.getElementById('chatbot-input');
                input.value = this.lastMessage;
                await this.send();
            }
        },

        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },

        addSignupCTA(data) {
            const messagesContainer = document.getElementById('messages');
            const ctaDiv = document.createElement('div');
            ctaDiv.className = 'message bot-message signup-cta';

            const signupUrl = data.signup_url || '/register';
            const loginUrl = data.login_url || '/login';
            const message = data.upsell_message || 'سجّل الدخول للحصول على المزيد';

            ctaDiv.innerHTML = `
                <div class="signup-container">
                    <p class="signup-text">${message}</p>
                    <div class="signup-buttons">
                        <a href="${signupUrl}" class="btn btn-primary">إنشاء حساب</a>
                        <a href="${loginUrl}" class="btn btn-secondary">تسجيل الدخول</a>
                    </div>
                </div>
            `;

            messagesContainer.appendChild(ctaDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        },

        addProviderCard(provider) {
            const messagesContainer = document.getElementById('messages');
            const cardLink = document.createElement('a');
            cardLink.href = provider.url || '#';
            cardLink.className = 'provider-card-link';
            cardLink.style.cursor = 'pointer';
            cardLink.onclick = (e) => {
                if (provider.url) {
                    window.location.href = provider.url;
                }
                return false;
            };

            // Build rating stars
            let starsHtml = '';
            if (provider.rating_avg) {
                const fullStars = Math.floor(provider.rating_avg);
                const hasHalfStar = provider.rating_avg % 1 >= 0.5;
                starsHtml = '★'.repeat(fullStars);
                if (hasHalfStar) starsHtml += '½';
                starsHtml += '☆'.repeat(5 - Math.ceil(provider.rating_avg));
            }

            // Build provider type badge
            let typeBadge = provider.provider_type ? `<span class="type-badge">${provider.provider_type === 'individual' ? 'مستقل' : 'شركة'}</span>` : '';

            cardLink.innerHTML = `
                <div class="provider-card">
                    <div class="provider-card-top">
                        ${provider.logo_url ? `<img src="${provider.logo_url}" alt="${provider.business_name}" class="provider-avatar">` : '<div class="provider-avatar-placeholder"></div>'}
                        <div class="provider-header">
                            <h3 class="provider-name">${provider.business_name || provider.name}</h3>
                            <p class="provider-category">${provider.category || 'خدمات'}</p>
                        </div>
                    </div>

                    <div class="provider-details">
                        ${provider.city ? `<span class="detail-item"><strong>المدينة:</strong> ${provider.city}</span>` : ''}
                        ${provider.experience_years ? `<span class="detail-item"><strong>الخبرة:</strong> ${provider.experience_years} سنوات</span>` : ''}
                    </div>

                    ${provider.rating_avg ? `
                    <div class="provider-rating">
                        <span class="stars">${starsHtml}</span>
                        <span class="rating-value">${provider.rating_avg.toFixed(1)}</span>
                        ${provider.reviews_count ? `<span class="review-count">(${provider.reviews_count})</span>` : ''}
                    </div>
                    ` : ''}

                    <div class="provider-footer">
                        <span class="view-profile">عرض الملف الشخصي →</span>
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
