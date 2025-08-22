@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-white border-0 py-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="mb-0 fw-bold text-dark">{{ __('messages.search_friends') }}</h3>
                        <a href="{{ route('friends.index') }}" class="btn btn-outline-dark">
                            <i class="bi bi-arrow-left"></i> {{ __('messages.back_to_friends') }}
                        </a>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('friends.search') }}" class="mb-4">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   name="q" 
                                   value="{{ request('q') }}" 
                                   placeholder="{{ __('messages.search_friends_placeholder') }}"
                                   autocomplete="off">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> {{ __('messages.search') }}
                            </button>
                        </div>
                    </form>

                    <!-- Search Results -->
                    @if(request('q'))
                        @if($users && $users->count() > 0)
                            <div class="row g-3">
                                @foreach($users as $user)
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm hover-shadow transition user-card">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <!-- Avatar -->
                                                    @if($user->avatar ?? false)
                                                        <img src="{{ $user->avatar }}" 
                                                             alt="avatar" 
                                                             class="rounded-circle user-avatar" 
                                                             style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                                                    @else
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white fw-semibold user-avatar" 
                                                             style="width: 50px; height: 50px; font-size: 1.3rem; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                                                            {{ mb_substr($user->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- User Info -->
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 fw-semibold text-dark">{{ $user->name }}</h6>
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    </div>
                                                    
                                                    <!-- Action Button -->
                                                    <div>
                                                        @if($user->id === Auth::id())
                                                            <div class="status-badge status-you">
                                                                <i class="bi bi-person-circle me-1"></i>
                                                                {{ __('messages.this_is_you') }}
                                                            </div>
                                                        @else
                                                            @php
                                                                $status = $friendStatuses[$user->id] ?? 'none';
                                                            @endphp
                                                            
                                                            @if($status === 'friends')
                                                                <div class="status-badge status-friends">
                                                                    <i class="bi bi-people-fill me-1"></i>
                                                                    {{ __('messages.already_friends') }}
                                                                </div>
                                                            @elseif($status === 'request_sent')
                                                                <div class="status-badge status-pending">
                                                                    <i class="bi bi-clock-history me-1"></i>
                                                                    {{ __('messages.request_sent') }}
                                                                </div>
                                                            @elseif($status === 'request_received')
                                                                <div class="d-flex gap-2">
                                                                    <form action="{{ route('friends.accept', $user->id) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-success btn-sm status-btn">
                                                                            <i class="bi bi-check-circle-fill me-1"></i>
                                                                            {{ __('messages.accept') }}
                                                                        </button>
                                                                    </form>
                                                                    <form action="{{ route('friends.decline', $user->id) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-danger btn-sm status-btn">
                                                                            <i class="bi bi-x-circle-fill me-1"></i>
                                                                            {{ __('messages.decline') }}
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            @elseif($status === 'none')
                                                                <form action="{{ route('friends.request', ['user' => $user->id]) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-primary btn-sm status-btn">
                                                                        <i class="bi bi-person-plus-fill me-1"></i>
                                                                        {{ __('messages.add_friend') }}
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-search" style="font-size: 3rem; color: #ccc;"></i>
                                <h5 class="mt-3 text-muted">{{ __('messages.no_users_found') }}</h5>
                                <p class="text-muted">{{ __('messages.try_different_search') }}</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-search" style="font-size: 3rem; color: #ccc;"></i>
                            <h5 class="mt-3 text-muted">{{ __('messages.search_for_friends') }}</h5>
                            <p class="text-muted">{{ __('messages.enter_name_or_email') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.transition {
    transition: all 0.3s ease;
}

/* Status Badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.status-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.status-you {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
}

.status-friends {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.status-pending {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    color: white;
}

/* Status Buttons */
.status-btn {
    border-radius: 20px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    border: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.status-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-success.status-btn {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}

.btn-success.status-btn:hover {
    background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
}

.btn-danger.status-btn {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
    border: none;
}

.btn-danger.status-btn:hover {
    background: linear-gradient(135deg, #c82333 0%, #d63384 100%);
}

.btn-primary.status-btn {
    background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
    border: none;
}

.btn-primary.status-btn:hover {
    background: linear-gradient(135deg, #0056b3 0%, #520dc2 100%);
}

/* Card hover effects */
.card.hover-shadow {
    transition: all 0.3s ease;
}

.card.hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* User Cards */
.user-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.user-card:hover {
    background: linear-gradient(135deg, #ffffff 0%, #e9ecef 100%);
    border-color: rgba(0, 0, 0, 0.1);
}

.user-avatar {
    transition: all 0.3s ease;
}

.user-card:hover .user-avatar {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}
</style>
@endpush
@endsection ё