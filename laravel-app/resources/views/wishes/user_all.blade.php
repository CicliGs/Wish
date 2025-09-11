@extends('layouts.app')

@section('content')
<div class="wishes-container">
    <div class="container">
        <div class="wishes-card">
            <h1 class="wishes-title">{{ __('messages.select_list_of') }} {{ $user->name }}</h1>
            @push('styles')
            <link rel="stylesheet" href="{{ asset('css/wishes.css') }}">
            @endpush
            <a href="{{ route('friends.index') }}" class="back-link">
                <svg fill="none" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ __('messages.back') }}
            </a>
            
            @if($wishLists->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-list-task"></i>
                    </div>
                    <div class="empty-state-text">{{ __('messages.list_is_empty') }}</div>
                    <div class="empty-state-hint">{{ __('messages.user_has_no_lists') }}</div>
                </div>
            @else
                <div class="row g-4">
                    @foreach($wishLists as $wishList)
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="wish-card">
                                <div class="wish-card-body d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h5 class="wish-card-title">{{ $wishList->title }}</h5>
                                        <span class="badge bg-primary ms-2">{{ $wishList->wishes_count ?? 0 }} {{ __('messages.gifts') }}</span>
                                    </div>
                                    
                                    @if($wishList->description)
                                        <p class="wish-card-description text-muted mb-3">{{ Str::limit($wishList->description, 100) }}</p>
                                    @endif
                                    
                                    <div class="mt-auto">
                                        <a href="{{ route('wishes.user.list', ['user' => $user->id, 'wishList' => $wishList->id]) }}" 
                                           class="wish-btn btn-primary w-100">
                                            <i class="bi bi-eye me-2"></i>{{ __('messages.view_wishlist') }}
                                        </a>
                                    </div>
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