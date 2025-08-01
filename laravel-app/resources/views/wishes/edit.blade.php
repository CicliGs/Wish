@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center wish-form-container">
    <div class="wish-form-card">
        <h1 class="wish-form-title">{{ __('messages.edit_wish') }}</h1>
        <form action="{{ route('wishes.update', [$wishList->id, $wish->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="title" class="form-label wish-form-label">{{ __('messages.wish_title') }}</label>
                <input type="text" name="title" id="title" class="form-control wish-form-control" value="{{ old('title', $wish->title) }}" required>
            </div>
            <div class="mb-3">
                <label for="url" class="form-label wish-form-label">{{ __('messages.wish_url') }} ({{ __('messages.optional') }})</label>
                <input type="url" name="url" id="url" class="form-control wish-form-control" value="{{ old('url', $wish->url) }}">
            </div>
            <div class="mb-3">
                <label for="price" class="form-label wish-form-label">{{ __('messages.wish_price') }} ({{ __('messages.optional') }})</label>
                <input type="number" name="price" id="price" class="form-control wish-form-control" step="0.01" value="{{ old('price', $wish->price) }}">
            </div>
            <button type="submit" class="btn btn-dark w-100 wish-form-btn">{{ __('messages.save') }}</button>
            <div class="mt-3 text-center">
                <a href="{{ route('wishes.index', $wishList->id) }}" class="wish-form-link">{{ __('messages.back_to_list') }}</a>
            </div>
        </form>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('css/wish-form.css') }}">
@endpush
@endsection
