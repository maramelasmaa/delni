@extends('layouts.auth')

@section('auth_title', 'Edit Account')

@section('content')
<h2 class="text-2xl font-bold mb-6 text-navy-800">Edit Account</h2>

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded">
        <ul class="list-disc list-inside text-red-600 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('account.update') }}" method="POST">
    @csrf

    <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $user->name) }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
            required
        />
    </div>

    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email', $user->email) }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
            required
        />
    </div>

    <div class="mb-6">
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
        <input
            type="tel"
            id="phone"
            name="phone"
            value="{{ old('phone', $user->phone) }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
        />
    </div>

    <button type="submit" class="w-full bg-primary-600 text-white py-2 rounded-md font-medium hover:bg-primary-700 transition">
        Save Changes
    </button>
</form>

<div class="mt-4 text-center">
    <a href="{{ route('dashboard') }}" class="text-sm text-primary-600 hover:text-primary-700">Back to Dashboard</a>
</div>
@endsection
