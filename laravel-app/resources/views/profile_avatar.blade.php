@extends('layouts.app')

@section('content')
<div class="container py-5 d-flex justify-content-center align-items-center profile-edit-container">
    <div class="card profile-edit-card avatar">
        <h3 class="profile-edit-title">{{ __('messages.change_photo') }}</h3>
        <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3 text-center">
                @if($user->avatar ?? false)
                    <img id="avatarPreview" src="{{ $user->avatar }}" alt="avatar" class="avatar-preview">
                @else
                    <img id="avatarPreview" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=96" alt="avatar" class="avatar-preview">
                @endif
            </div>
            <div class="mb-3">
                <input type="file" name="avatar" id="avatarInput" class="form-control profile-edit-form-control" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-dark w-100 profile-edit-btn">{{ __('messages.save') }}</button>
            <a href="{{ route('profile') }}" class="btn btn-outline-secondary w-100 profile-edit-btn back">{{ __('messages.back') }}</a>
        </form>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile-edit.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('js/avatar-preview.js') }}"></script>
@endpush
@endsection 