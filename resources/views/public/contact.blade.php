@extends('public.layout')

@section('title', __('messages.public.contact_us') . ' - ' . config('app.name'))

@section('content')
<section style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 200px); padding: 2rem 1rem;">
    <div style="max-width: 500px; width: 100%; background: #FFFFFF; border: 1px solid #E7E7E7; border-radius: 16px; padding: clamp(1.5rem, 4vw, 2.5rem); box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03); text-align: center;">
        <h1 style="font-size: 1.8rem; font-weight: 900; color: #0B1A34; margin-bottom: 1rem; letter-spacing: -0.03em;">
            {{ __('messages.public.contact_us') }}
        </h1>

        @if($contactInfo)
            <div style="text-align: center; margin-top: 2rem;">
                <p style="font-size: 0.95rem; color: #5D5959; margin-bottom: 2rem; line-height: 1.8;">
                    {{ __('messages.public.need_help') }}
                </p>

                <div style="background: #FCFBFB; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                    @if($contactInfo->whatsapp)
                        <div style="margin-bottom: 1.25rem;">
                            <p style="font-size: 0.85rem; color: #5D5959; font-weight: 600; margin-bottom: 0.5rem;">
                                {{ __('filament.fields.whatsapp') }}
                            </p>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contactInfo->whatsapp) }}" target="_blank" style="color: #F1620F; text-decoration: none; font-size: 0.95rem; font-weight: 700;">
                                {{ $contactInfo->whatsapp }}
                            </a>
                        </div>
                    @endif

                    @if($contactInfo->phone)
                        <div style="margin-bottom: 1.25rem;">
                            <p style="font-size: 0.85rem; color: #5D5959; font-weight: 600; margin-bottom: 0.5rem;">
                                {{ __('filament.fields.phone') }}
                            </p>
                            <a href="tel:{{ $contactInfo->phone }}" style="color: #F1620F; text-decoration: none; font-size: 0.95rem; font-weight: 700;">
                                {{ $contactInfo->phone }}
                            </a>
                        </div>
                    @endif

                    @if($contactInfo->email)
                        <div style="margin-bottom: 1.25rem;">
                            <p style="font-size: 0.85rem; color: #5D5959; font-weight: 600; margin-bottom: 0.5rem;">
                                {{ __('filament.fields.email') }}
                            </p>
                            <a href="mailto:{{ $contactInfo->email }}" style="color: #F1620F; text-decoration: none; font-size: 0.95rem; font-weight: 700;">
                                {{ $contactInfo->email }}
                            </a>
                        </div>
                    @endif

                    @if($contactInfo->address)
                        <div>
                            <p style="font-size: 0.85rem; color: #5D5959; font-weight: 600; margin-bottom: 0.5rem;">
                                {{ __('filament.fields.address') }}
                            </p>
                            <p style="color: #5D5959; font-size: 0.95rem; font-weight: 500;">
                                {{ $contactInfo->address }}
                            </p>
                        </div>
                    @endif
                </div>

                <a href="{{ route('home') }}" style="display: inline-block; background: #F1620F; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 700; transition: background 0.2s ease;">
                    {{ __('messages.public.back_home') }}
                </a>
            </div>
        @else
            <p style="font-size: 0.95rem; color: #5D5959; margin-bottom: 1.5rem;">
                {{ __('messages.public.contact_information') }}
            </p>

            <a href="{{ route('home') }}" style="display: inline-block; background: #F1620F; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 700; transition: background 0.2s ease;">
                {{ __('messages.public.back_home') }}
            </a>
        @endif
    </div>
</section>
@endsection
