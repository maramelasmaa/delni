@extends('public.layout')

@section('title', __('messages.public.error_404_title') . ' - ' . config('app.name'))

@section('content')
<section style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 200px); padding: 2rem 1rem;">
    <style>
        .error-layout {
            max-width: 500px;
            width: 100%;
            background: #FFFFFF;
            border: 1px solid #E7E7E7;
            border-radius: 16px;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03);
            text-align: center;
        }

        .error-code {
            font-size: clamp(2.5rem, 10vw, 3.5rem);
            font-weight: 900;
            color: #0B1A34;
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .error-title {
            font-size: clamp(1.5rem, 3vw, 1.8rem);
            font-weight: 900;
            color: #0B1A34;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .error-message {
            font-size: 0.95rem;
            color: #5D5959;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .error-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .error-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .error-actions a.primary {
            background: #F1620F;
            color: white;
        }

        .error-actions a.primary:hover {
            background: #D9550C;
        }

        .error-actions a.secondary {
            background: transparent;
            border: 1px solid #E7E7E7;
            color: #0B1A34;
        }

        .error-actions a.secondary:hover {
            background: #FCFBFB;
            border-color: #F1620F;
            color: #F1620F;
        }

        .error-info {
            padding-top: 1.5rem;
            border-top: 1px solid #E7E7E7;
        }

        .error-info-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0B1A34;
            margin-bottom: 0.75rem;
        }

        .error-info-list {
            list-style: none;
            font-size: 0.9rem;
            color: #5D5959;
            font-weight: 500;
            display: inline-block;
            text-align: center;
        }

        .error-info-list li {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        @media (max-width: 640px) {
            .error-layout {
                padding: 1.25rem;
            }

            .error-code {
                font-size: 2.2rem;
                margin-bottom: 0.4rem;
            }

            .error-title {
                font-size: 1.4rem;
                margin-bottom: 0.75rem;
            }

            .error-message {
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
            }

            .error-actions {
                gap: 0.5rem;
                margin-bottom: 1.25rem;
            }

            .error-actions a {
                padding: 0.65rem 1.25rem;
                font-size: 0.9rem;
            }

            .error-info {
                padding-top: 1.25rem;
            }

            .error-info-title {
                font-size: 0.8rem;
                margin-bottom: 0.6rem;
            }

            .error-info-list {
                font-size: 0.85rem;
            }

            .error-info-list li {
                margin-bottom: 0.4rem;
            }
        }
    </style>

    <div class="error-layout">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">{{ __('messages.public.error_404_title') }}</h2>
        <p class="error-message">{{ __('messages.public.error_404_message') }}</p>

        <div class="error-actions">
            <a href="{{ route('home') }}" class="primary">{{ __('messages.public.back_home') }}</a>
            <a href="{{ route('public.search') }}" class="secondary">{{ __('messages.public.search') }}</a>
        </div>

        <div class="error-info">
            <p class="error-info-title">{{ __('messages.public.suggestions') }}</p>
            <ul class="error-info-list">
                <li>{{ __('messages.public.error_404_suggestion_1') }}</li>
                <li>{{ __('messages.public.error_404_suggestion_2') }}</li>
                <li>{{ __('messages.public.error_404_suggestion_3') }}</li>
            </ul>
        </div>
    </div>

    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #E7E7E7;">
        <div class="provider-cta-card" style="background: #FCFBFB; border: 1px solid #E7E7E7; border-radius: 16px; padding: 2rem; text-align: center; max-width: 500px; margin: 0 auto;">
            <h3 style="font-size: 1.3rem; font-weight: 900; color: #0B1A34; margin-bottom: 0.75rem; letter-spacing: -0.02em;">
                {{ __('messages.public.are_you_professional') }}
            </h3>
            <p style="font-size: 0.95rem; color: #5D5959; margin-bottom: 1.5rem; line-height: 1.6;">
                {{ __('messages.public.join_marketplace_description') }}
            </p>
            <a href="{{ route('contact') }}" style="display: inline-block; background: #F1620F; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 700; transition: background 0.2s ease;">
                {{ __('messages.public.contact_us') }}
            </a>
        </div>
    </div>
</section>
@endsection
