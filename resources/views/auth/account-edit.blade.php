@extends('layouts.auth')

@section('title', 'Edit Account - ' . config('app.name'))

@section('auth_title', 'Edit Account')

@section('auth_subtitle', 'Update your profile details and keep your account information current.')

@section('content')
    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">
            <x-render-icon icon="heroicon-o-exclamation-circle" />
            <div>
                <strong>Unable to save changes</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('account.update') }}" method="POST" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="name" class="auth-label">Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $user->name) }}"
                class="auth-input @error('name') is-invalid @enderror"
                autocomplete="name"
                required
            >
            @error('name')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="email" class="auth-label">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                class="auth-input @error('email') is-invalid @enderror"
                autocomplete="email"
                required
            >
            @error('email')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="phone" class="auth-label">Phone</label>
            <input
                type="tel"
                id="phone"
                name="phone"
                value="{{ old('phone', $user->phone) }}"
                class="auth-input @error('phone') is-invalid @enderror"
                autocomplete="tel"
            >
            @error('phone')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="auth-submit">Save changes</button>
    </form>

    <div class="auth-footer">
        <a href="{{ route('settings') }}" class="auth-link">Back to settings</a>
    </div>
@endsection
