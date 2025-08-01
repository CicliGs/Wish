@extends('layouts.app')

@section('content')
<div class="container py-5 d-flex justify-content-center align-items-center search-container">
    <div class="search-card">
        <h3 class="search-title">{{ __('messages.find_friends') }}</h3>
        
        <form action="{{ route('friends.search') }}" method="GET" class="search-form">
            <div class="input-group search-input-group">
                <input type="text" name="q" class="form-control search-input" placeholder="{{ __('messages.search_placeholder') }}" value="{{ $query ?? '' }}" required>
                <button class="btn search-btn" type="submit">{{ __('messages.search') }}</button>
            </div>
        </form>
        
        @if(isset($users))
            <div class="users-list">
                @forelse($users as $user)
                    <div class="user-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="user-name">{{ $user->name }}</div>
                                <div class="user-email">{{ $user->email }}</div>
                            </div>
                            <div>
                                @if($user->friend_status === 'accepted')
                                    <span class="badge bg-success">{{ __('messages.already_friends') }}</span>
                                @elseif($user->friend_status === 'pending')
                                    <span class="badge bg-warning text-dark">{{ __('messages.request_sent') }}</span>
                                @else
                                    <form action="{{ route('friends.request', $user->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success btn-sm">{{ __('messages.add_friend') }}</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted text-center">{{ __('messages.no_users_found') }}</div>
                @endforelse
            </div>
        @endif
        <a href="{{ route('friends.index') }}" class="btn btn-outline-secondary w-100 mt-4 back-btn">{{ __('messages.back_to_profile') }}</a>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('css/friends-search.css') }}">
@endpush
@endsection 