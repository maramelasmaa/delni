<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" type="image/png" href="{{ asset('images/icon-192.png') }}" sizes="192x192">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-192.png') }}">

    <meta name="theme-color" content="#0B1A34">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">

    <script>
        (function(){var t=localStorage.getItem('delni-theme');if(t==='dark')document.documentElement.setAttribute('data-theme','dark');})();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')

    @php
        $shouldRegisterPublicPwa = request()->routeIs(
            'home',
            'public.search',
            'public.top-rated',
            'public.categories',
            'public.category',
            'public.subcategory',
            'public.city',
            'public.provider',
            'contact',
            'privacy',
            'terms',
            'disclaimer',
        );
    @endphp

    <style>
        :root {
            --delni-primary: #F1620F;
            --delni-navy: #0B1A34;
            --delni-bg: #FCFBFB;
            --delni-gray: #C7C3C3;
            --delni-muted: #5D5959;
            --delni-border: #E7E7E7;
            --delni-success: #22C55E;
            --delni-warning: #F59E0B;

            --delni-radius-sm: 12px;
            --delni-radius-md: 18px;
            --delni-radius-lg: 26px;

            --delni-shadow-sm: 0 8px 20px rgba(11, 26, 52, .05);
            --delni-shadow-md: 0 16px 36px rgba(11, 26, 52, .08);

            /* PWA Native UI Specifications */
            --pwa-nav-height: 64px;
            --pwa-header-height: 60px;
        }

        [data-theme="dark"] {
            --delni-bg: #0F172A;
            --delni-navy: #F1F5F9;
            --delni-border: #1E293B;
            --delni-muted: #94A3B8;
            --delni-gray: #475569;
            --delni-shadow-sm: 0 8px 20px rgba(0,0,0,.3);
            --delni-shadow-md: 0 16px 36px rgba(0,0,0,.4);
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden; /* Locks desktop scroll bounces, handles layout natively */
            background: var(--delni-bg);
            color: var(--delni-navy);
            font-family: 'Cairo', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .delni-splash {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .85rem;
            background: #0B1A34;
            opacity: 1;
            transition: opacity .4s ease;
        }

        .delni-splash img {
            width: 96px;
            height: 96px;
            border-radius: 24px;
            animation: delni-splash-pop .5s ease;
        }

        .delni-splash strong {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 950;
            letter-spacing: 0;
        }

        .delni-splash.is-done {
            opacity: 0;
            pointer-events: none;
        }

        @keyframes delni-splash-pop {
            from { transform: scale(.85); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* Continuous Structural Flex Framework */
        .pwa-shell {
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: -webkit-fill-available;
        }

        /* Custom Header Wrapper */
        .delni-header {
            height: calc(var(--pwa-header-height) + env(safe-area-inset-top));
            padding-top: env(safe-area-inset-top);
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-bottom: 1px solid var(--delni-border);
            position: sticky;
            top: 0;
            z-index: 100;
            flex-shrink: 0;
        }

        .delni-header__inner {
            height: var(--pwa-header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
        }

        .delni-logo {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: 1.2rem;
            font-weight: 950;
            letter-spacing: 0;
        }

        .delni-logo__mark {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            overflow: hidden;
            background: #0B1A34;
        }

        .delni-logo__mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .delni-header__quick {
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        .delni-icon-btn {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #F8FAFC;
            border: 1px solid var(--delni-border);
            color: var(--delni-navy);
            transition: background .16s ease, border-color .16s ease, color .16s ease, transform .16s ease;
        }

        .delni-icon-btn svg {
            width: 20px;
            height: 20px;
        }

        .delni-icon-btn:active {
            transform: scale(.96);
        }

        .delni-offline-banner {
            position: fixed;
            top: calc(var(--pwa-header-height) + env(safe-area-inset-top) + .6rem);
            inset-inline: .75rem;
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: .65rem .9rem;
            border-radius: 16px;
            background: #0B1A34;
            color: #fff;
            box-shadow: 0 18px 40px rgba(11, 26, 52, .18);
            font-size: .82rem;
            font-weight: 850;
            text-align: center;
        }

        .delni-offline-banner.is-visible {
            display: flex;
        }

        /* Dedicated App Viewport Container */
        .delni-main {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: calc(var(--pwa-nav-height) + env(safe-area-inset-bottom) + 20px);
        }

        .pwa-view-boundary {
            width: min(100% - 1.5rem, 1240px);
            margin-inline: auto;
            padding-top: .85rem;
        }

        /* Top Desktop View Navigation Items Link List Wrapper */
        .delni-nav, .delni-actions {
            display: none;
        }

        /* Persistent High-Fidelity App Bottom Navigation Bar */
        .pwa-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: calc(var(--pwa-nav-height) + env(safe-area-inset-bottom));
            padding-bottom: env(safe-area-inset-bottom);
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid var(--delni-border);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            z-index: 999;
        }

        .pwa-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--delni-muted);
            font-size: 0.72rem;
            font-weight: 700;
            gap: 4px;
            transition: color 0.2s ease;
            text-decoration: none;
        }

        .pwa-nav-item:focus-visible,
        .delni-icon-btn:focus-visible,
        button:focus-visible,
        .lp-chip:focus-visible,
        .lp-filter-select:focus-visible,
        .lp-pagination a:focus-visible {
            outline: 3px solid rgba(241, 98, 15, .28);
            outline-offset: 2px;
        }

        .is-loading {
            opacity: .72;
            pointer-events: none;
        }

        .pwa-nav-item.active {
            color: var(--delni-primary);
        }

        .pwa-nav-item svg {
            width: 24px;
            height: 24px;
            stroke-width: 2;
            transition: transform 0.2s ease;
        }

        .pwa-tab-item.is-active {
            color: var(--delni-primary);
        }

        .pwa-tab-item:active svg {
            transform: scale(0.92);
        }

        /* PWA Inline Core Footer Adjustments */
        .delni-footer {
            margin-top: 4rem;
            padding: 2rem 0;
            border-top: 1px solid var(--delni-border);
            background: #fff;
            color: var(--delni-muted);
            font-size: .85rem;
            font-weight: 600;
        }

        .delni-footer__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .delni-footer a:hover {
            color: var(--delni-primary);
        }

        /* ── Shared listing-page (lp-*) design system ───────────────────── */
        /* Used by: category, subcategory, city, top-rated, categories      */

        .lp-wrapper {
            padding: .65rem 0 2rem;
        }

        /* App-bar style page header */
        .lp-header {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .8rem .95rem;
            background: #fff;
            border: 1px solid var(--delni-border);
            border-radius: 18px;
            box-shadow: var(--delni-shadow-sm);
        }

        .lp-back {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #F8FAFC;
            border: 1px solid var(--delni-border);
            color: var(--delni-navy);
        }

        .lp-back svg { width: 20px; height: 20px; }

        .lp-header-body { flex: 1; min-width: 0; }

        .lp-label {
            display: block;
            color: var(--delni-primary);
            font-size: .7rem;
            font-weight: 900;
            margin-bottom: .1rem;
        }

        .lp-title {
            margin: 0;
            color: var(--delni-navy);
            font-size: 1.15rem;
            font-weight: 950;
            line-height: 1.2;
        }

        .lp-count {
            display: block;
            margin-top: .15rem;
            color: #64748B;
            font-size: .76rem;
            font-weight: 800;
        }

        /* Horizontal chip strip (subcategories, filter tabs) */
        .lp-chips {
            display: flex;
            gap: .5rem;
            overflow-x: auto;
            scrollbar-width: none;
            padding: .75rem .1rem .35rem;
        }
        .lp-chips::-webkit-scrollbar { display: none; }

        .lp-chip {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .38rem;
            min-height: 38px;
            padding: .45rem .85rem;
            border-radius: 999px;
            border: 1px solid var(--delni-border);
            background: #fff;
            color: var(--delni-navy);
            font-size: .82rem;
            font-weight: 900;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s, border-color .15s, color .15s;
        }

        .lp-chip small {
            min-width: 22px;
            min-height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #F1F5F9;
            color: #64748B;
            font-size: .68rem;
            font-weight: 900;
        }

        .lp-chip:active,
        .lp-chip.is-active {
            border-color: rgba(241,98,15,.3);
            background: #FFF7ED;
            color: var(--delni-primary);
        }

        .lp-chip--reset {
            border-color: rgba(241,98,15,.25);
            background: #FFF7ED;
            color: var(--delni-primary);
        }

        .lp-chip svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        .lp-chips--compact {
            padding-top: .3rem;
        }

        .lp-chips--service {
            padding-top: .55rem;
        }

        .lp-chip.is-active small {
            background: rgba(241,98,15,.15);
            color: var(--delni-primary);
        }

        /* Inline filter selects */
        .lp-filter-row {
            display: flex;
            align-items: center;
            gap: .5rem;
            overflow-x: auto;
            scrollbar-width: none;
            padding: .3rem .1rem .5rem;
        }
        .lp-filter-row::-webkit-scrollbar { display: none; }

        .lp-filter-select {
            flex: 0 0 auto;
            min-height: 38px;
            padding: 0 .75rem;
            border-radius: 999px;
            border: 1px solid var(--delni-border);
            background: #fff;
            color: var(--delni-navy);
            font: inherit;
            font-size: .78rem;
            font-weight: 850;
            outline: none;
            cursor: pointer;
        }

        .lp-filter-select:focus {
            border-color: rgba(241,98,15,.4);
        }

        /* Results section */
        .lp-results { margin-top: .65rem; }
        .lp-results--search { margin-top: .85rem; }

        .lp-results-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 .2rem .65rem;
        }

        .lp-results-head span {
            color: var(--delni-primary);
            font-size: .7rem;
            font-weight: 900;
        }

        .lp-results-head h2 {
            margin: .1rem 0 0;
            color: var(--delni-navy);
            font-size: 1.02rem;
            font-weight: 950;
            line-height: 1.35;
        }

        /* Pagination */
        .lp-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            margin-top: 1rem;
        }

        .lp-pagination a,
        .lp-pagination span {
            flex: 0 0 auto;
            min-height: 40px;
            padding: .5rem .85rem;
            border-radius: 12px;
            background: #fff;
            border: 1px solid var(--delni-border);
            color: var(--delni-navy);
            font-size: .78rem;
            font-weight: 950;
        }

        .lp-pagination strong {
            color: #64748B;
            font-size: .78rem;
        }

        .lp-pagination .is-disabled {
            color: #94A3B8;
            background: #F1F5F9;
        }

        @media (min-width: 640px) {
            .lp-wrapper { padding-top: 1rem; }
            .lp-title { font-size: 1.35rem; }
        }

        /* Header icon badge — right-side accent used on listing pages */
        .lp-header-icon {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: rgba(241, 98, 15, .08);
            color: var(--delni-primary);
        }
        .lp-header-icon svg { width: 22px; height: 22px; }

        /* Provider CTA banner — shared across home, categories */
        .lp-cta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1.2rem;
            padding: 1rem 1.1rem;
            border-radius: 20px;
            background: var(--delni-navy);
            color: #fff;
        }
        .lp-cta > div { flex: 1; min-width: 0; }
        .lp-cta span {
            display: block;
            color: var(--delni-primary);
            font-size: .72rem;
            font-weight: 900;
            margin-bottom: .2rem;
        }
        .lp-cta h2 {
            margin: 0;
            font-size: 1rem;
            font-weight: 950;
            color: #fff;
        }
        .lp-cta p {
            margin: .25rem 0 0;
            color: rgba(255, 255, 255, .7);
            font-size: .78rem;
            font-weight: 600;
            line-height: 1.6;
        }
        .lp-cta a {
            flex-shrink: 0;
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            padding: .55rem 1rem;
            border-radius: 12px;
            background: #fff;
            color: var(--delni-navy);
            font-size: .82rem;
            font-weight: 950;
            text-decoration: none;
        }
        @media (max-width: 400px) { .lp-cta { flex-direction: column; align-items: flex-start; } }
        /* ── end shared listing-page ──────────────────────────────────────── */

        /* ── Dark mode overrides ──────────────────────────────────────────── */
        [data-theme="dark"] body {
            background: var(--delni-bg);
            color: var(--delni-navy);
        }

        /* Shell chrome */
        [data-theme="dark"] .delni-header {
            background: #0F172A;
            border-color: #1E293B;
        }
        [data-theme="dark"] .delni-logo {
            color: #F1F5F9;
        }
        [data-theme="dark"] .delni-logo__mark {
            background: #0B1A34;
        }
        [data-theme="dark"] .pwa-bottom-nav {
            background: rgba(15, 23, 42, 0.96);
            border-color: #1E293B;
        }
        [data-theme="dark"] .delni-footer {
            background: #0F172A;
            border-color: #1E293B;
        }

        /* lp-* system */
        [data-theme="dark"] .lp-header {
            background: #1E293B;
            border-color: #334155;
        }
        [data-theme="dark"] .lp-back {
            background: #0F172A;
            border-color: #334155;
            color: #F1F5F9;
        }
        [data-theme="dark"] .lp-count { color: #94A3B8; }
        [data-theme="dark"] .lp-chip {
            background: #1E293B;
            border-color: #334155;
            color: #F1F5F9;
        }
        [data-theme="dark"] .lp-chip small {
            background: #0F172A;
            color: #94A3B8;
        }
        [data-theme="dark"] .lp-chip.is-active {
            background: rgba(241,98,15,.15);
            border-color: rgba(241,98,15,.35);
        }
        [data-theme="dark"] .lp-filter-select {
            background: #1E293B;
            border-color: #334155;
            color: #F1F5F9;
            color-scheme: dark;
        }
        [data-theme="dark"] .lp-pagination a,
        [data-theme="dark"] .lp-pagination span {
            background: #1E293B;
            border-color: #334155;
            color: #F1F5F9;
        }
        [data-theme="dark"] .lp-pagination strong { color: #94A3B8; }
        [data-theme="dark"] .lp-pagination .is-disabled {
            background: #0F172A;
            color: #475569;
        }
        [data-theme="dark"] .lp-cta {
            background: #1E293B;
            border: 1px solid #334155;
            color: #F1F5F9;
        }
        [data-theme="dark"] .lp-cta h2 { color: #F1F5F9; }
        [data-theme="dark"] .lp-cta span { color: var(--delni-primary); }
        [data-theme="dark"] .lp-cta a {
            background: #0F172A;
            color: #F1F5F9;
        }
        [data-theme="dark"] .lp-results-head h2 { color: #F1F5F9; }
        [data-theme="dark"] .delni-header .delni-icon-btn {
            background: #111827;
            border-color: #334155;
            color: #F8FAFC;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.02);
        }
        [data-theme="dark"] .delni-header .delni-icon-btn:hover,
        [data-theme="dark"] .delni-header .delni-icon-btn:focus-visible {
            background: #1E293B;
            border-color: rgba(241,98,15,.38);
            color: #F1620F;
        }

        /* Provider card (pc-*) */
        [data-theme="dark"] .pc-card {
            background: #1E293B;
            border-color: #334155;
        }
        [data-theme="dark"] .pc-name { color: #F1F5F9; }
        [data-theme="dark"] .pc-meta-item { color: #94A3B8; }
        [data-theme="dark"] .pc-tag {
            background: #0F172A;
            border-color: #334155;
            color: #94A3B8;
        }
        [data-theme="dark"] .pc-btn--primary { /* keep as-is (orange) */ }
        [data-theme="dark"] .pc-btn--wa {
            background: #0D2B1D;
            border-color: rgba(37,211,102,.2);
            color: #4ADE80;
        }

        /* Desktop ghost button */
        [data-theme="dark"] .delni-btn--ghost {
            background: #1E293B;
            color: #F1F5F9;
            border-color: #334155;
        }
        /* ── end dark mode overrides ──────────────────────────────────────── */

        /* Wide Screen Layout Desktop Enhancements */
        @media (min-width: 1025px) {
            html, body { overflow: visible; }
            .pwa-shell { height: auto; }
            .delni-main { overflow-y: visible; padding-bottom: 0; }
            .pwa-bottom-nav { display: none; }
            .delni-nav { display: flex; align-items: center; gap: .35rem; }
            .delni-actions { display: flex; align-items: center; gap: .6rem; }

            .delni-nav a {
                min-height: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: .55rem .9rem;
                border-radius: 999px;
                color: var(--delni-muted);
                font-size: .92rem;
                font-weight: 850;
            }

            .delni-nav a:hover,
            .delni-nav a.is-active {
                color: var(--delni-primary);
                background: rgba(241, 98, 15, .08);
            }

            .delni-btn {
                min-height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .45rem;
                padding: .7rem 1rem;
                border-radius: 14px;
                border: 1px solid transparent;
                font-size: .9rem;
                font-weight: 900;
                cursor: pointer;
                transition: .18s ease;
            }

            .delni-btn--primary {
                background: var(--delni-primary);
                color: #fff;
                box-shadow: 0 12px 24px rgba(241, 98, 15, .22);
            }

            .delni-btn--primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 16px 32px rgba(241, 98, 15, .28);
            }

            .delni-btn--ghost {
                background: #fff;
                color: var(--delni-navy);
                border-color: var(--delni-border);
            }

            .delni-btn--ghost:hover {
                border-color: rgba(241, 98, 15, .28);
                color: var(--delni-primary);
            }
        }

        .pc-fav-toast,
        .delni-auth-toast {
            position: fixed;
            inset-inline-start: 50%;
            bottom: calc(var(--pwa-nav-height) + env(safe-area-inset-bottom, 0px) + .75rem);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: .75rem;
            max-width: min(calc(100vw - 2rem), 420px);
            padding: .68rem .85rem;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 16px;
            background: #0B1A34;
            color: #fff;
            box-shadow: 0 18px 44px rgba(2,6,23,.28);
            font-size: .84rem;
            font-weight: 800;
            line-height: 1.5;
            opacity: 0;
            pointer-events: none;
            transform: translateX(50%) translateY(1rem);
            transition: opacity .22s ease, transform .22s ease;
        }

        .pc-fav-toast.is-visible,
        .delni-auth-toast.is-visible {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(50%) translateY(0);
        }

        .pc-fav-toast span,
        .delni-auth-toast span {
            min-width: 0;
        }

        .pc-fav-toast a,
        .delni-auth-toast a {
            flex-shrink: 0;
            color: #FDBA74;
            font-weight: 950;
            text-decoration: none;
        }

        [data-theme="dark"] .pc-fav-toast,
        [data-theme="dark"] .delni-auth-toast {
            border-color: #334155;
            background: #1E293B;
            color: #F1F5F9;
        }
    </style>
</head>

<body>
    <div class="delni-offline-banner" id="delniOfflineBanner" role="status">
        أنت غير متصل بالإنترنت حاليا. بعض بيانات مقدمي الخدمات قد لا تكون محدثة.
    </div>

    <div class="delni-splash" id="delniSplash" aria-hidden="true">
        <img src="{{ asset('images/icon-192.png') }}" alt="" width="96" height="96">
        <strong>دلني</strong>
    </div>
    <script>
        (() => {
            const splash = document.getElementById('delniSplash');
            if (sessionStorage.getItem('delni_splash_shown')) {
                splash.remove();
                return;
            }
            sessionStorage.setItem('delni_splash_shown', '1');
            const dismiss = () => {
                splash.classList.add('is-done');
                setTimeout(() => splash.remove(), 450);
            };
            window.addEventListener('load', () => setTimeout(dismiss, 350));
            setTimeout(dismiss, 2500);
        })();
    </script>

    <div class="pwa-shell">
        <header class="delni-header">
            <div class="delni-header__inner">
                <a href="{{ route('home') }}" class="delni-logo" aria-label="{{ config('app.name') }}">
                    <span class="delni-logo__mark">
                        <img src="{{ asset('images/icon-192.png') }}" alt="" width="36" height="36">
                    </span>
                    <span>{{ config('app.name') }}</span>
                </a>

                <nav class="delni-nav" aria-label="Primary navigation">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">الرئيسية</a>
                    <a href="{{ route('public.top-rated') }}" class="{{ request()->routeIs('public.top-rated') ? 'is-active' : '' }}">الأعلى تقييما</a>
                    <a href="{{ route('favorites.index') }}" class="{{ request()->routeIs('favorites.*') ? 'is-active' : '' }}">المفضلة</a>
                    <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings') || request()->routeIs('about') ? 'is-active' : '' }}">الإعدادات</a>
                </nav>

                <div class="delni-header__quick">
                    <a href="{{ route('public.search') }}" class="delni-icon-btn" aria-label="بحث">
                        <x-render-icon icon="heroicon-o-magnifying-glass" />
                    </a>
                    <a href="{{ auth()->check() ? route('settings') : route('login') }}" class="delni-icon-btn" aria-label="الحساب">
                        <x-render-icon icon="app-account" />
                    </a>
                </div>
            </div>
        </header>

        <main class="delni-main">
            <div class="pwa-view-boundary">
                @yield('content')
            </div>
        </main>

        <nav class="pwa-bottom-nav" aria-label="Mobile Navigation Bar">
            <a href="{{ route('home') }}" class="pwa-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                <x-render-icon icon="app-home" />
                <span>الرئيسية</span>
            </a>
            <a href="{{ route('public.top-rated') }}" class="pwa-nav-item {{ request()->routeIs('public.top-rated') ? 'active' : '' }}">
                <x-render-icon icon="app-star" />
                <span>الأعلى تقييماً</span>
            </a>
            <a href="{{ route('favorites.index') }}" class="pwa-nav-item {{ request()->routeIs('favorites.*') ? 'active' : '' }}">
                <x-render-icon icon="app-heart" />
                <span>المفضلة</span>
            </a>
            <a href="{{ route('settings') }}" class="pwa-nav-item {{ request()->routeIs('settings') || request()->routeIs('about') ? 'active' : '' }}">
                <x-render-icon icon="app-account" />
                <span>الإعدادات</span>
            </a>
        </nav>
    </div>

    <script>
        window.DelniAuthToast = window.DelniAuthToast || (() => {
            let toast = null;
            let timeoutId = null;

            return {
                show(message, actionLabel, actionUrl) {
                    if (!toast) {
                        toast = document.createElement('div');
                        toast.className = 'delni-auth-toast';
                        toast.setAttribute('role', 'status');
                        toast.setAttribute('aria-live', 'polite');
                        document.body.appendChild(toast);
                    }

                    window.clearTimeout(timeoutId);
                    toast.innerHTML = '<span></span><a></a>';
                    toast.querySelector('span').textContent = message;

                    const action = toast.querySelector('a');
                    action.textContent = actionLabel;
                    action.href = actionUrl;

                    requestAnimationFrame(() => toast.classList.add('is-visible'));

                    timeoutId = window.setTimeout(() => {
                        toast.classList.remove('is-visible');
                    }, 5000);
                },
            };
        })();
    </script>

    @stack('scripts')

    <script>
        (() => {
            const banner = document.getElementById('delniOfflineBanner');
            const syncOnlineState = () => banner?.classList.toggle('is-visible', !navigator.onLine);

            window.addEventListener('online', syncOnlineState);
            window.addEventListener('offline', syncOnlineState);
            syncOnlineState();
        })();

        document.addEventListener('submit', (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement) || form.dataset.noBusy === 'true') {
                return;
            }

            const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
            submitter?.classList.add('is-loading');
            submitter?.setAttribute('aria-busy', 'true');
        });

        @if($shouldRegisterPublicPwa)
        if ('serviceWorker' in navigator && window.isSecureContext) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
            });
        }
        @endif
    </script>
</body>
</html>
