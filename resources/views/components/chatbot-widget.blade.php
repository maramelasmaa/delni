<div class="delni-chat" data-delni-chat>
    <button class="delni-chat__toggle" type="button" data-chat-toggle aria-label="مساعد دلني">
        <span>؟</span>
    </button>

    <section class="delni-chat__panel" data-chat-panel hidden>
        <header class="delni-chat__header">
            <div>
                <strong>مساعد دلني</strong>
                <span>اسأل عن خدمة أو مقدم خدمة</span>
            </div>
            <button type="button" data-chat-reset>إعادة</button>
        </header>

        <div class="delni-chat__messages" data-chat-messages></div>

        <div class="delni-chat__suggestions" data-chat-suggestions>
            <button type="button">محامي في طرابلس</button>
            <button type="button">فني تكييف في بنغازي</button>
            <button type="button">مصور أفراح</button>
        </div>

        <form class="delni-chat__form" data-chat-form>
            <input type="text" name="message" maxlength="500" autocomplete="off" placeholder="اكتب طلبك هنا..." data-chat-input>
            <button type="submit">إرسال</button>
        </form>
    </section>
</div>

@once
    <style>
        .delni-chat {
            position: fixed;
            right: 1rem;
            inset-inline-end: 1rem;
            bottom: 1rem;
            z-index: 9999;
            display: block;
            font-family: inherit;
            direction: rtl;
        }

        .delni-chat__toggle {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 18px;
            background: var(--delni-primary);
            color: #fff;
            box-shadow: 0 18px 38px rgba(241, 98, 15, .28);
            font-size: 1.4rem;
            font-weight: 950;
            cursor: pointer;
        }

        .delni-chat__panel {
            position: absolute;
            right: 0;
            bottom: 4.5rem;
            width: min(92vw, 380px);
            overflow: hidden;
            border: 1px solid var(--delni-border);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(11, 26, 52, .18);
        }

        .delni-chat__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .9rem 1rem;
            background: var(--delni-navy);
            color: #fff;
        }

        .delni-chat__header strong,
        .delni-chat__header span {
            display: block;
        }

        .delni-chat__header span {
            color: rgba(255, 255, 255, .72);
            font-size: .78rem;
            font-weight: 700;
        }

        .delni-chat__header button,
        .delni-chat__suggestions button,
        .delni-chat__form button {
            border: 0;
            font-family: inherit;
            font-weight: 900;
            cursor: pointer;
        }

        .delni-chat__header button {
            border-radius: 999px;
            padding: .45rem .65rem;
            background: rgba(255, 255, 255, .12);
            color: #fff;
        }

        .delni-chat__messages {
            display: flex;
            flex-direction: column;
            gap: .65rem;
            height: 360px;
            overflow-y: auto;
            padding: .9rem;
            background: #FCFBFB;
        }

        .delni-chat__bubble {
            max-width: 88%;
            padding: .7rem .8rem;
            border-radius: 14px;
            color: var(--delni-navy);
            background: #fff;
            border: 1px solid var(--delni-border);
            font-size: .9rem;
            line-height: 1.65;
            font-weight: 700;
        }

        .delni-chat__bubble.is-user {
            align-self: flex-start;
            background: rgba(241, 98, 15, .1);
            border-color: rgba(241, 98, 15, .2);
        }

        .delni-chat__provider {
            display: grid;
            gap: .35rem;
            padding: .7rem;
            border: 1px solid var(--delni-border);
            border-radius: 14px;
            background: #fff;
        }

        .delni-chat__provider strong {
            color: var(--delni-navy);
            font-size: .92rem;
        }

        .delni-chat__provider span {
            color: var(--delni-muted);
            font-size: .8rem;
            font-weight: 750;
        }

        .delni-chat__provider a {
            color: var(--delni-primary);
            font-size: .82rem;
            font-weight: 950;
            text-decoration: none;
        }

        .delni-chat__suggestions {
            display: flex;
            gap: .45rem;
            overflow-x: auto;
            padding: .75rem .9rem;
            border-top: 1px solid var(--delni-border);
        }

        .delni-chat__suggestions button {
            flex: 0 0 auto;
            border-radius: 999px;
            padding: .45rem .65rem;
            background: rgba(11, 26, 52, .06);
            color: var(--delni-navy);
            font-size: .78rem;
        }

        .delni-chat__form {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: .5rem;
            padding: .75rem .9rem .9rem;
            border-top: 1px solid var(--delni-border);
        }

        .delni-chat__form input {
            min-width: 0;
            border: 1px solid var(--delni-border);
            border-radius: 12px;
            padding: .7rem .8rem;
            font-family: inherit;
        }

        .delni-chat__form button {
            border-radius: 12px;
            padding: .7rem .85rem;
            background: var(--delni-primary);
            color: #fff;
        }

        @media (max-width: 520px) {
            .delni-chat {
                right: .75rem;
                inset-inline-end: .75rem;
                bottom: .75rem;
            }

            .delni-chat__panel {
                width: calc(100vw - 1.5rem);
            }
        }
    </style>
@endonce

@once
@push('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-delni-chat]');
            if (!root) return;

            const panel = root.querySelector('[data-chat-panel]');
            const messages = root.querySelector('[data-chat-messages]');
            const form = root.querySelector('[data-chat-form]');
            const input = root.querySelector('[data-chat-input]');
            const suggestions = root.querySelector('[data-chat-suggestions]');
            let sessionId = localStorage.getItem('delni_chat_session_id') || '';

            const appendBubble = (text, type = 'assistant') => {
                const bubble = document.createElement('div');
                bubble.className = 'delni-chat__bubble' + (type === 'user' ? ' is-user' : '');
                bubble.textContent = text;
                messages.appendChild(bubble);
                messages.scrollTop = messages.scrollHeight;
            };

            const appendProviders = (providers = []) => {
                providers.forEach((provider) => {
                    const card = document.createElement('article');
                    card.className = 'delni-chat__provider';
                    card.innerHTML = `
                        <strong>${provider.name || 'مقدم خدمة'}</strong>
                        <span>${[provider.city, provider.category].filter(Boolean).join(' · ')}</span>
                        <span>${Number(provider.rating || 0).toFixed(1)} ★ · ${provider.reviews_count || 0} تقييم</span>
                        <a href="${provider.url}">عرض الملف</a>
                    `;
                    messages.appendChild(card);
                });
                messages.scrollTop = messages.scrollHeight;
            };

            const sendMessage = async (message) => {
                appendBubble(message, 'user');
                input.value = '';
                input.disabled = true;

                try {
                    const response = await fetch('{{ route('api.chat.message') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ message, session_id: sessionId }),
                    });
                    const payload = await response.json();
                    sessionId = payload.session_id || sessionId;
                    localStorage.setItem('delni_chat_session_id', sessionId);
                    appendBubble(payload.message || 'صار خطأ بسيط. حاول مرة ثانية.');
                    appendProviders(payload.providers || []);
                } catch (error) {
                    appendBubble('صار خطأ في الاتصال. حاول مرة ثانية.');
                } finally {
                    input.disabled = false;
                    input.focus();
                }
            };

            root.querySelector('[data-chat-toggle]').addEventListener('click', () => {
                panel.hidden = !panel.hidden;
                if (!panel.hidden && messages.children.length === 0) {
                    appendBubble('أهلاً، أنا مساعد دلني. قلّي شن الخدمة أو مقدم الخدمة اللي تبحث عليه؟');
                }
            });

            root.querySelector('[data-chat-reset]').addEventListener('click', async () => {
                messages.innerHTML = '';
                await fetch('{{ route('api.chat.reset') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: sessionId }),
                });
                sessionId = '';
                localStorage.removeItem('delni_chat_session_id');
                appendBubble('تم مسح المحادثة. كيف نقدر نساعدك؟');
            });

            suggestions.addEventListener('click', (event) => {
                if (event.target.matches('button')) {
                    sendMessage(event.target.textContent.trim());
                }
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const message = input.value.trim();
                if (message) sendMessage(message);
            });
        })();
    </script>
@endpush
@endonce
