@extends('layouts.app')

@section('content')
<div class="container py-5 d-flex justify-content-center align-items-center profile-edit-container">
    <div class="card profile-edit-card">
        <h4 class="profile-edit-title">{{ __('messages.change_name') }}</h4>
        <form action="{{ route('profile.update_name') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label profile-edit-form-label">{{ __('messages.your_name') }}</label>
                <input type="text" name="name" id="name" class="form-control profile-edit-form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" maxlength="255" required autofocus>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('profile') }}" class="btn btn-outline-secondary profile-edit-btn">{{ __('messages.back') }}</a>
                <button type="submit" class="btn btn-primary profile-edit-btn">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile-edit.css') }}">
@endpush
@endsection 