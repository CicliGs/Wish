@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center wishlist-container">
    <div class="wishlist-card">
        <h1 class="wishlist-title">{{ __('messages.edit_list') }}</h1>
        <form action="{{ route('wish-lists.update', $wishList->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="title" class="form-label wishlist-form-label">{{ __('messages.list_title') }}</label>
                <input type="text" name="title" id="title" class="form-control wishlist-form-control" value="{{ old('title', $wishList->title) }}" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label wishlist-form-label">{{ __('messages.list_description') }} ({{ __('messages.optional') }})</label>
                <textarea name="description" id="description" class="form-control wishlist-form-control" rows="3">{{ old('description', $wishList->description) }}</textarea>
            </div>
            <button type="submit" class="btn btn-dark w-100 wishlist-btn">{{ __('messages.save') }}</button>
            <div class="mt-3 text-center">
                <a href="{{ route('wish-lists.index') }}" class="wishlist-link">{{ __('messages.back_to_lists') }}</a>
            </div>
        </form>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('css/wishlist.css') }}">
@endpush
@endsection 