@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center wish-form-container">
    <div class="wish-form-card">
        <h1 class="wish-form-title">{{ __('messages.add_wish') }} "{{ $wishList->title }}"</h1>
        <form action="{{ route('wishes.store', $wishList->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="title" class="form-label wish-form-label">{{ __('messages.wish_title') }}</label>
                <input type="text" name="title" id="title" class="form-control wish-form-control" required>
            </div>
            <div class="mb-3">
                <label for="url" class="form-label wish-form-label">{{ __('messages.wish_url') }} ({{ __('messages.optional') }})</label>
                <input type="url" name="url" id="url" class="form-control wish-form-control">
            </div>
            <div class="mb-3">
                <label class="form-label wish-form-label">{{ __('messages.wish_image') }}</label>
                <input type="file" name="image_file" id="image_file" class="form-control wish-form-control mb-2" accept="image/*">
                <input type="url" name="image" id="image" class="form-control wish-form-control mb-2" placeholder="https://example.com/image.jpg">
                <div class="image-toggle-tabs">
                    <button type="button" class="image-toggle-btn" id="btn-upload-file">
                        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                        {{ __('messages.upload_file') }}
                    </button>
                    <button type="button" class="image-toggle-btn" id="btn-upload-url">
                        <svg viewBox="0 0 24 24"><path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"/><path d="M16 6l-4-4-4 4M12 2v14"/></svg>
                        {{ __('messages.paste_link') }}
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label wish-form-label">{{ __('messages.wish_price') }} ({{ __('messages.optional') }})</label>
                <input type="number" name="price" id="price" class="form-control wish-form-control" step="0.01">
            </div>
            <button type="submit" class="btn btn-dark w-100 wish-form-btn">{{ __('messages.add_wish') }}</button>
            <div class="mt-3 text-center">
                <a href="{{ route('wishes.index', $wishList->id) }}" class="wish-form-link">{{ __('messages.back_to_list') }}</a>
            </div>
        </form>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('css/wish-form.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('js/wish-image-toggle.js') }}"></script>
@endpush
@endsection 