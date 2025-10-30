@extends('layouts.app')

@section('content')
<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="card shadow-lg p-4 profile-card" style="border-radius: 28px; max-width: 480px; width: 100%; background: #f6f6f7; border: none;">
        <div class="d-flex flex-column align-items-center position-relative mb-3">
            <div class="profile-avatar-wrapper mb-3 position-relative">
                @if($user->avatar ?? false)
                    <img src="{{ $user->avatar }}" alt="avatar" class="rounded-circle profile-avatar" style="width: 110px; height: 110px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 2px 12px 0 #e0e0e0;">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center profile-avatar" style="width: 110px; height: 110px; background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); font-size: 2.8rem; color: #fff; font-weight: 700; border: 4px solid #fff; box-shadow: 0 2px 12px 0 #e0e0e0;">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 mb-3">
                <h2 class="mb-0">{{ $user->name }}</h2>
                @if($user->id === auth()->id())
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-dark btn-sm" title="{{ __('messages.edit_profile') }}"><i class="bi bi-pencil"></i></a>
                @endif
            </div>
            <div class="mb-3 text-muted">{{ $user->email }}</div>
        </div>
        <hr class="my-3" style="border-top: 1.5px solid #e0e0e0;">
        <div class="w-100">
  <h5 class="fw-bold mb-4 text-dark">{{ __('messages.achievements') }}</h5>
  <div class="achievements-flex mb-2">
    @php
      $receivedAchievements = array_filter($achievements, fn($a) => $a['received']);
      $displayedAchievements = array_slice($receivedAchievements, 0, 3);
    @endphp
    @foreach($displayedAchievements as $ach)
      <div class="achievement-icon-wrapper d-flex flex-column align-items-center" title="{{ $ach['title'] }}">
        <img src="{{ $ach['icon'] }}" alt="{{ $ach['title'] }}" class="achievement-icon" loading="lazy">
        <div class="achievement-title mt-2 text-truncate fst-italic text-secondary" title="{{ $ach['title'] }}"></div>
      </div>
    @endforeach
    @if (count($receivedAchievements) === 0)
      <div class="text-muted fst-italic">{{ __('messages.no_achievements') }}</div>
    @endif
  </div>
  @if(count($receivedAchievements) > 3)
    <div class="text-center mt-3">
      <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#allAchievementsModal">
        {{ __('messages.all_achievements') }} ({{ count($receivedAchievements) }})
      </button>
    </div>
  @endif
</div>

<div class="modal fade" id="allAchievementsModal" tabindex="-1" aria-labelledby="allAchievementsModalLabel" aria-hidden="true" data-bs-backdrop="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="allAchievementsModalLabel">{{ __('messages.all_achievements') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
      </div>
      <div class="modal-body">
        <div class="achievements-flex mb-2">
          @foreach($achievements as $ach)
            <div class="achievement-icon-wrapper d-flex flex-column align-items-center {{ $ach['received'] ? '' : 'opacity-50' }}" title="{{ $ach['title'] }}">
              <img src="{{ $ach['icon'] }}" alt="{{ $ach['title'] }}" class="achievement-icon" loading="lazy">
              <div class="achievement-title mt-2 text-truncate fst-italic text-secondary">{{ $ach['title'] }}</div>
              <div class="mt-1">
                @if($ach['received'])
                  <span class="badge bg-success">{{ __('messages.received') }}</span>
                @else
                  <span class="badge bg-secondary">{{ __('messages.not_received') }}</span>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row w-100 mb-2 mt-2 g-2">
            <div class="col-6">
                <div class="p-3 text-center shadow-sm rounded-4 profile-widget" style="background: #fff; color: #222; font-weight: 600; border-radius: 16px;">
                    <div style="font-size: 1.6rem;"><i class="bi bi-list-task me-1"></i>{{ $stats['total_wish_lists'] ?? 0 }}</div>
                    <div style="font-size: 0.98rem; color: #444;">{{ __('messages.lists') }}</div>
                </div>
                </a>
            </div>
            <div class="col-6">
                <div class="p-3 text-center shadow-sm rounded-4 profile-widget" style="background: #fff; color: #222; font-weight: 600; border-radius: 16px;">
                    <div style="font-size: 1.6rem;"><i class="bi bi-gift-fill me-1"></i>{{ $stats['total_wishes'] ?? 0 }}</div>
                    <div style="font-size: 0.98rem; color: #444;">{{ __('messages.wishes') }}</div>
                </div>
                </a>
            </div>
            <div class="col-6 mt-2">
                <div class="p-3 text-center shadow-sm rounded-4 profile-widget" style="background: #fff; color: #222; font-weight: 600; border-radius: 16px;">
                    <div style="font-size: 1.6rem;"><i class="bi bi-people-fill me-1"></i>{{ isset($friends) ? $friends->count() : 0 }}</div>
                    <div style="font-size: 0.98rem; color: #444;">{{ __('messages.friends_count') }}</div>
                </div>
                </a>
            </div>
            <div class="col-6 mt-2">
                <div class="p-3 text-center shadow-sm rounded-4 profile-widget" style="background: #fff; color: #222; font-weight: 600; border-radius: 16px;">
                    <div style="font-size: 1.6rem;"><i class="bi bi-bookmark-heart-fill me-1"></i>{{ $stats['total_reserved_wishes'] ?? 0 }}</div>
                    <div style="font-size: 0.98rem; color: #444;">{{ __('messages.reserved') }}</div>
                </div>
                </a>
            </div>
        </div>
        <hr class="my-3" style="border-top: 1.5px solid #e0e0e0;">
        @if($user->id === auth()->id())
        <form method="POST" action="{{ route('logout') }}" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100">{{ __('messages.logout_account') }}</button>
        </form>
        @endif
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush

@push('scripts')
<script>
// Ensure modal is properly initialized
document.addEventListener('DOMContentLoaded', function() {
    // Initialize achievement modal with Bootstrap 5
    const achievementModal = document.getElementById('allAchievementsModal');
    if (achievementModal) {
        const modal = new bootstrap.Modal(achievementModal, {
            backdrop: false,
            keyboard: true,
            focus: true
        });
        
        // Add event listener for modal shown
        achievementModal.addEventListener('shown.bs.modal', function() {
            // Ensure proper z-index after modal is shown
            this.style.zIndex = '9999';
        });
        
        // Add event listener for modal show
        achievementModal.addEventListener('show.bs.modal', function() {
            // Ensure proper z-index before modal is shown
            this.style.zIndex = '9999';
        });
    }
    
    // Force z-index on page load
    function forceModalZIndex() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.zIndex = '9999';
        });
    }
    
    // Run on page load
    forceModalZIndex();
    
    // Run periodically to ensure z-index is maintained
    setInterval(forceModalZIndex, 100);
});
</script>
@endpush
@endsection