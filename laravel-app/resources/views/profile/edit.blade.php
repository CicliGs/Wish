@extends('layouts.app')

@section('content')
<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="card shadow-lg p-4 profile-card" style="border-radius: 28px; max-width: 480px; width: 100%; background: #f6f6f7; border: none;">
        <div class="d-flex flex-column align-items-center position-relative mb-3">
            <h3 class="mb-4 text-dark">{{ __('messages.edit_profile') }}</h3>
            
            <!-- Avatar Section -->
            <div class="profile-avatar-wrapper mb-3 position-relative">
                @if($user->avatar ?? false)
                    <img src="{{ $user->avatar }}" alt="avatar" class="rounded-circle profile-avatar" style="width: 110px; height: 110px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 2px 12px 0 #e0e0e0;">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center profile-avatar" style="width: 110px; height: 110px; background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); font-size: 2.8rem; color: #fff; font-weight: 700; border: 4px solid #fff; box-shadow: 0 2px 12px 0 #e0e0e0;">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            
            <!-- Name Field -->
            <div class="mb-3">
                <label for="name" class="form-label fw-bold text-dark">{{ __('messages.name') }}</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       id="name" name="name" value="{{ old('name', $user->name) }}" 
                       placeholder="{{ __('messages.enter_name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Avatar Field -->
            <div class="mb-4">
                <label for="avatar" class="form-label fw-bold text-dark">{{ __('messages.avatar') }}</label>
                <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                       id="avatar" name="avatar" accept="image/*">
                <div class="form-text">{{ __('messages.avatar_help') }}</div>
                @error('avatar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Preview Section -->
            <div class="mb-4" id="avatar-preview" style="display: none;">
                <label class="form-label fw-bold text-dark">{{ __('messages.preview') }}</label>
                <div class="text-center">
                    <img id="preview-image" src="" alt="Preview" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 8px 0 #e0e0e0;">
                </div>
            </div>

            <!-- Buttons -->
            <div class="d-flex gap-2">
                <a href="{{ route('profile') }}" class="btn btn-outline-secondary flex-fill">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary flex-fill">
                    {{ __('messages.save_changes') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatar');
    const previewSection = document.getElementById('avatar-preview');
    const previewImage = document.getElementById('preview-image');

    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewSection.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewSection.style.display = 'none';
        }
    });
});
</script>
@endpush
@endsection 