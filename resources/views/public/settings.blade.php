@extends('public.layout')

@section('title', 'الإعدادات - ' . config('app.name'))

@section('content')
<div class="lp-wrapper mobile-settings-container">

    <header class="lp-header">
        <div class="lp-header-body">
            <span class="lp-label">دلني</span>
            <h1 class="lp-title">الإعدادات</h1>
        </div>
    </header>

    @auth
        {{-- User Identity --}}
        <div class="st-identity">
            <div class="st-avatar">
                {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
            <div class="st-identity__info">
                <strong>{{ auth()->user()->name }}</strong>
                <span>{{ auth()->user()->email }}</span>
            </div>
            @if(auth()->user()->profile)
                <a href="{{ route('public.provider', auth()->user()->profile->slug) }}" class="st-identity__badge">
                    مزود
                </a>
            @endif
        </div>
    @else
        {{-- Guest Prompts --}}
        <div class="st-guest">
            <div class="st-guest__icon">
                <x-render-icon icon="heroicon-o-user-circle" style="width: 16px; height: 16px;" />
            </div>
            <div class="st-identity__info">
                <strong>غير مسجّل</strong>
                <span>سجّل دخولك للوصول إلى حسابك</span>
            </div>
            <a href="{{ route('login') }}" class="st-guest__btn">تسجيل الدخول</a>
        </div>
    @endauth

    {{-- Account Menu Group (Authenticated) --}}
    @auth
        @if(auth()->user()->profile)
            <div class="st-group">
                <div class="st-list">
                    <a href="{{ route('public.provider', auth()->user()->profile->slug) }}" class="st-row">
                        <div class="st-row__icon st-row__icon--orange">
                            <x-render-icon icon="heroicon-o-identification" style="width: 16px; height: 16px;" />
                        </div>
                        <span class="st-row__text">ملفي المهني</span>
                        <x-render-icon icon="heroicon-o-chevron-left" class="st-row__arrow" />
                    </a>
                </div>
            </div>
        @endif
    @endauth

    {{-- Display/Appearance Menu Group --}}
    <div class="st-group">
        <p class="st-group__label">المظهر</p>
        <div class="st-list">
            <div class="st-row st-row--no-link">
                <div class="st-row__icon st-row__icon--purple">
                    <x-render-icon icon="heroicon-o-moon" style="width: 16px; height: 16px;" />
                </div>
                <span class="st-row__text" id="themeLabel">الوضع الليلي</span>
                <button class="st-toggle" id="themeToggle" aria-label="تبديل المظهر">
                    <span class="st-toggle__knob"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Info Menu Group --}}
    <div class="st-group">
        <p class="st-group__label">التطبيق</p>
        <div class="st-list">
            <a href="{{ route('about') }}" class="st-row">
                <div class="st-row__icon st-row__icon--teal">
                    <x-render-icon icon="heroicon-o-information-circle" style="width: 16px; height: 16px;" />
                </div>
                <span class="st-row__text">من نحن</span>
                <x-render-icon icon="heroicon-o-chevron-left" class="st-row__arrow" />
            </a>
            <a href="{{ route('contact') }}" class="st-row">
                <div class="st-row__icon st-row__icon--green">
                    <x-render-icon icon="heroicon-o-chat-bubble-left-ellipsis" style="width: 16px; height: 16px;" />
                </div>
                <span class="st-row__text">تواصل معنا</span>
                <x-render-icon icon="heroicon-o-chevron-left" class="st-row__arrow" />
            </a>
            <a href="{{ route('terms') }}" class="st-row">
                <div class="st-row__icon st-row__icon--slate">
                    <x-render-icon icon="heroicon-o-document-text" style="width: 16px; height: 16px;" />
                </div>
                <span class="st-row__text">شروط الاستخدام</span>
                <x-render-icon icon="heroicon-o-chevron-left" class="st-row__arrow" />
            </a>
            <a href="{{ route('privacy') }}" class="st-row">
                <div class="st-row__icon st-row__icon--slate">
                    <x-render-icon icon="heroicon-o-shield-check" style="width: 16px; height: 16px;" />
                </div>
                <span class="st-row__text">سياسة الخصوصية</span>
                <x-render-icon icon="heroicon-o-chevron-left" class="st-row__arrow" />
            </a>
        </div>
    </div>

    {{-- System Actions Group (Authenticated) --}}
    @auth
        <div class="st-group">
            <p class="st-group__label">إجراءات الحساب</p>
            <div class="st-list">
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                    <button type="submit" class="st-row st-row--btn">
                        <div class="st-row__icon st-row__icon--orange">
                            <x-render-icon icon="heroicon-o-arrow-right-on-rectangle" style="width: 16px; height: 16px;" />
                        </div>
                        <span class="st-row__text">تسجيل الخروج</span>
                    </button>
                </form>
            </div>
        </div>
    @endauth

</div>

@endsection

@push('scripts')
<script>
    const toggle = document.getElementById('themeToggle');
    const themeLabel = document.getElementById('themeLabel');
    const html = document.documentElement;

    const applyTheme = (theme) => {
        html.setAttribute('data-theme', theme);
        toggle?.classList.toggle('is-on', theme === 'dark');
        if (themeLabel) {
            themeLabel.textContent = theme === 'dark' ? 'الوضع النهاري' : 'الوضع الليلي';
        }
    };

    applyTheme(localStorage.getItem('delni-theme') || 'light');

    toggle?.addEventListener('click', () => {
        const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('delni-theme', next);
        applyTheme(next);
    });


</script>
@endpush

@push('styles')
<style>
    /* Global/Layout Baseline Containers */
    .mobile-settings-container {
        background-color: var(--delni-bg);
        min-height: 100vh;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .lp-header {
        margin-bottom: 1rem;
    }

    .lp-label {
        color: #F1620F;
    }

    /* Account Identity Header Card */
    .st-identity {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border-radius: 18px;
        color: #fff;
    }

    .st-avatar {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #F1620F;
        color: #fff;
        font-size: 1.2rem;
        font-weight: 950;
    }

    .st-identity__info {
        flex: 1;
        min-width: 0;
        text-align: right;
    }

    .st-identity__info strong {
        display: block;
        font-size: .92rem;
        font-weight: 900;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .st-identity__info span {
        display: block;
        margin-top: .15rem;
        font-size: .76rem;
        color: rgba(255,255,255,.66);
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .st-identity__badge {
        flex-shrink: 0;
        padding: .25rem .6rem;
        border-radius: 999px;
        background: rgba(241,98,15,0.2);
        border: 1px solid rgba(241,98,15,0.3);
        color: #FFA07A;
        font-size: .72rem;
        font-weight: 900;
        text-decoration: none;
    }

    /* Guest View Layout Elements */
    .st-guest {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: 1rem 1.1rem;
        margin-bottom: 1rem;
        background: #fff;
        border: 1px solid var(--delni-border);
        border-radius: 18px;
        box-shadow: var(--delni-shadow-sm);
    }

    .st-guest__icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: rgba(241,98,15,.08);
        color: #94A3B8;
    }

    .st-guest__btn {
        flex-shrink: 0;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        padding: .45rem .8rem;
        border-radius: 10px;
        background: #F1620F;
        color: #fff;
        font-size: .78rem;
        font-weight: 900;
        text-decoration: none;
    }

    .st-guest .st-identity__info strong {
        color: var(--delni-navy);
    }

    .st-guest .st-identity__info span {
        color: var(--delni-muted);
    }

    /* Setting Lists Structural Grid Layouts */
    .st-group {
        margin-top: 1.1rem;
    }

    .st-group__label {
        margin: 0 0.25rem 0.45rem 0.25rem;
        color: var(--delni-muted);
        font-size: .72rem;
        font-weight: 900;
        text-align: right;
    }

    .st-list {
        background: #fff;
        border: 1px solid var(--delni-border);
        border-radius: 18px;
        box-shadow: var(--delni-shadow-sm);
        overflow: hidden;
    }

    .st-row {
        display: flex;
        align-items: center;
        gap: .85rem;
        min-height: 64px;
        padding: .9rem 1rem;
        border-bottom: 1px solid var(--delni-border);
        color: inherit;
        text-decoration: none;
        width: 100%;
        background: none;
        border-left: none;
        border-right: none;
        border-top: none;
        cursor: pointer;
        font: inherit;
        text-align: right;
        -webkit-tap-highlight-color: transparent;
    }

    .st-row:not(.st-row--no-link):active {
        background-color: #F8FAFC;
    }

    .st-row:last-child {
        border-bottom: none;
    }

    .st-row__icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    /* Enforce rigid micro-sizing specifications for embedded SVG code */
    .st-row__icon svg,
    .st-guest__icon svg {
        width: 22px !important;
        height: 22px !important;
        stroke-width: 1.8px;
    }

    .st-row__icon--blue   { background: #EFF6FF; color: #3B82F6; }
    .st-row__icon--orange { background: rgba(241,98,15,0.06); color: #F1620F; }
    .st-row__icon--purple { background: #F5F3FF; color: #8B5CF6; }
    .st-row__icon--teal   { background: #F0FDFA; color: #14B8A6; }
    .st-row__icon--green  { background: #F0FDF4; color: #22C55E; }
    .st-row__icon--slate  { background: #F8FAFC; color: #64748B; }
    .st-row__icon--red    { background: #FEF2F2; color: #EF4444; }

    .st-row__text {
        flex: 1;
        font-size: .9rem;
        font-weight: 900;
        color: var(--delni-navy);
    }

    .st-row--danger .st-row__text {
        color: #EF4444;
    }

    .st-row__arrow {
        flex-shrink: 0;
        color: #CBD5E1;
        width: 18px !important;
        height: 18px !important;
    }

    /* iOS Native Style Dynamic Switch Toggle */
    .st-toggle {
        width: 46px;
        height: 26px;
        flex-shrink: 0;
        position: relative;
        border-radius: 999px;
        border: none;
        background: #E2E8F0;
        cursor: pointer;
        transition: background 0.15s ease;
        padding: 0;
    }

    .st-toggle.is-on {
        background: #22C55E;
    }

    .st-toggle__knob {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1.5px 3px rgba(0,0,0,0.15);
        transition: transform 0.15s ease;
    }

    html[dir="rtl"] .st-toggle.is-on .st-toggle__knob {
        transform: translateX(-20px);
    }
    html[dir="ltr"] .st-toggle.is-on .st-toggle__knob {
        transform: translateX(20px);
    }

    /* Bottom Confirmation Modal Drawer Sheets */
    .st-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: flex-end;
    }

    .st-modal[hidden] {
        display: none;
    }

    .st-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
    }

    .st-modal__sheet {
        position: relative;
        z-index: 1;
        width: 100%;
        padding: .85rem 1.1rem 2rem;
        background: #fff;
        border-radius: 20px 20px 0 0;
        text-align: center;
        box-shadow: 0 -10px 25px rgba(0,0,0,0.1);
        animation: slideUp 0.2s ease-out forwards;
    }

    @keyframes slideUp {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }

    .st-modal__drag-handle {
        width: 32px;
        height: 4px;
        background: #E2E8F0;
        border-radius: 999px;
        margin: 0 auto 1rem auto;
    }

    .st-modal__icon {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: #FEF2F2;
        color: #EF4444;
        margin-bottom: 0.75rem;
    }

    .st-modal__icon svg {
        width: 22px !important;
        height: 22px !important;
    }

    .st-modal__sheet h2 {
        margin: 0 0 0.4rem 0;
        color: #1E293B;
        font-size: 1.05rem;
        font-weight: 950;
    }

    .st-modal__sheet p {
        margin: 0 0 1.25rem 0;
        color: #64748B;
        font-size: .84rem;
        line-height: 1.5;
        font-weight: 700;
    }

    .st-modal__confirm {
        display: block;
        width: 100%;
        min-height: 44px;
        padding: .75rem 1rem;
        border: none;
        border-radius: 12px;
        background: #EF4444;
        color: #fff;
        font-size: .88rem;
        font-weight: 900;
        font-family: inherit;
        cursor: pointer;
        margin-bottom: 0.5rem;
    }

    .st-modal__cancel {
        display: block;
        width: 100%;
        min-height: 44px;
        padding: .75rem 1rem;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        background: #F8FAFC;
        color: #475569;
        font-size: .86rem;
        font-weight: 900;
        font-family: inherit;
        cursor: pointer;
    }

    /* Complete Dark Mode Variable Re-mapping Styles */
    [data-theme="dark"] .mobile-settings-container {
        background-color: #0F172A;
    }
    [data-theme="dark"] .lp-title {
        color: #F8FAFC;
    }
    [data-theme="dark"] .st-list {
        background: #1E293B;
    }
    [data-theme="dark"] .st-guest {
        background: #1E293B;
        box-shadow: none;
    }
    [data-theme="dark"] .st-guest__icon {
        background: #0F172A;
        color: #64748B;
    }
    [data-theme="dark"] .st-row {
        border-color: #334155;
    }
    [data-theme="dark"] .st-row:not(.st-row--no-link):active {
        background-color: #273549;
    }
    [data-theme="dark"] .st-row__text {
        color: #F8FAFC;
    }
    [data-theme="dark"] .st-group__label {
        color: #94A3B8;
    }
    [data-theme="dark"] .st-toggle {
        background: #334155;
    }
    [data-theme="dark"] .st-modal__sheet {
        background: #1E293B;
    }
    [data-theme="dark"] .st-modal__sheet h2 {
        color: #F8FAFC;
    }
    [data-theme="dark"] .st-modal__drag-handle {
        background: #334155;
    }
    [data-theme="dark"] .st-modal__cancel {
        background: #0F172A;
        border-color: #334155;
        color: #94A3B8;
    }
    [data-theme="dark"] .st-row__icon--blue   { background: rgba(59,130,246,.12); }
    [data-theme="dark"] .st-row__icon--orange { background: rgba(241,98,15,.12); }
    [data-theme="dark"] .st-row__icon--purple { background: rgba(139,92,246,.12); }
    [data-theme="dark"] .st-row__icon--teal   { background: rgba(20,184,166,.12); }
    [data-theme="dark"] .st-row__icon--green  { background: rgba(34,197,94,.12); }
    [data-theme="dark"] .st-row__icon--slate  { background: #1E293B; }
    [data-theme="dark"] .st-row__icon--red    { background: rgba(239,68,68,.12); }
    [data-theme="dark"] .st-row__arrow { color: #475569; }
    [data-theme="dark"] .st-row--btn { border-color: #334155; }
    [data-theme="dark"] .st-row--danger .st-row__text { color: #F87171; }
</style>
@endpush
