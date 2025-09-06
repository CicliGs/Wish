@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center auth-container">
    <div class="auth-card">
        <h1 class="auth-title">{{ __('messages.register') }}</h1>
        <form action="{{ route('register') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label auth-form-label">{{ __('messages.name') }}</label>
                <input type="text" name="name" id="name" class="form-control auth-form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label auth-form-label">{{ __('messages.email') }}</label>
                <input type="email" name="email" id="email" class="form-control auth-form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label auth-form-label">{{ __('messages.password') }}</label>
                <input type="password" name="password" id="password" class="form-control auth-form-control" required>
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label auth-form-label">{{ __('messages.password_confirmation') }}</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control auth-form-control" required>
            </div>
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="alert-message">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
            <button type="submit" class="btn btn-dark w-100 auth-btn">{{ __('messages.register') }}</button>
            <div class="mt-3 text-center">
                {{ __('messages.have_account') }} <a href="{{ route('login') }}" class="auth-link">{{ __('messages.login') }}</a>
            </div>
        </form>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/register.js') }}"></script>
@endpush
@endsection
