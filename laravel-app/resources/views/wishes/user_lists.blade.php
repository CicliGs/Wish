@extends('layouts.app')

@section('content')
<div class="wishlists-container">
    <div class="container">
        <div class="wishlists-card">
            <a href="{{ route('profile') }}" class="back-link mb-3 d-inline-flex align-items-center">
                <i class="bi bi-arrow-left"></i> {{ __('messages.back_to_profile') }}
            </a>
            <h1 class="wishlists-title">{{ __('messages.select_list_of') }} {{ $user->name }}</h1>
            @push('styles')
            <link rel="stylesheet" href="{{ asset('css/wishlists.css') }}">
            @endpush
            
            @if($wishLists->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-list-ul"></i>
                    </div>
                    <div class="empty-state-text">{{ __('messages.no_lists_yet') }}</div>
                    <div class="empty-state-hint">{{ __('messages.user_has_no_lists') }}</div>
                </div>
            @else
                <div class="wishlists-grid">
                    @foreach($wishLists as $list)
                        <div class="wishlist-card">
                            <div class="wishlist-card-header">
                                <h5 class="wishlist-card-title">{{ $list->title }}</h5>
                                <div class="wishlist-card-subtitle">{{ __('messages.created') }} {{ $list->created_at->format('d.m.Y') }}</div>
                            </div>
                            <div class="wishlist-card-body">
                                <p class="wishlist-card-description">{{ $list->description }}</p>
                                <div class="wishlist-card-stats">
                                    <div class="wishlist-stat">
                                        <span class="wishlist-stat-number">{{ $list->wishes_count }}</span>
                                        <div class="wishlist-stat-label">{{ __('messages.gifts') }}</div>
                                    </div>
                                    <div class="wishlist-stat">
                                        <span class="wishlist-stat-number">{{ $list->reserved_wishes_count }}</span>
                                        <div class="wishlist-stat-label">{{ __('messages.reserved') }}</div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('wishes.user.list', ['userId' => $user->id, 'wishListId' => $list->id]) }}" class="wishlist-btn btn-primary">{{ __('messages.view_gifts') }}</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 