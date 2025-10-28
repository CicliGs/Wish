@extends('layouts.app')

@section('content')
<div class="wishes-container">
    <div class="container">
        <div class="wishes-card">
            <div class="d-flex align-items-center justify-content-center mb-4">
                <h1 class="wishes-title mb-0">{{ $wishList->title }}</h1>
                <span class="currency-badge ms-3">
                    <i class="bi bi-currency-exchange me-1"></i>
                    {{ $wishList->currency }}
                </span>
            </div>
            @if($wishList->description)
                <p class="text-center mb-4 text-muted">{{ $wishList->description }}</p>
            @endif
            
            @if($wishes->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-gift"></i>
                    </div>
                    <div class="empty-state-text">{{ __('messages.no_gifts_in_list') }}</div>
                    <div class="empty-state-hint">{{ __('messages.list_is_empty') }}</div>
                </div>
            @else
                <div class="row g-4">
                    @foreach($wishes as $wish)
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="wish-card">
                                @if($wish->hasImage())
                                    <img src="{{ $wish->image_url }}" alt="image" class="wish-card-img">
                                @endif
                                <div class="wish-card-body d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h5 class="wish-card-title">{{ $wish->title }}</h5>
                                        {!! $wish->is_reserved
                                            ? ($wish->reservation && auth()->check() && $wish->reservation->user_id === auth()->id()
                                                ? '<span class="badge bg-success ms-2">' . __('messages.reserved_by_you') . '</span>'
                                                : '<span class="badge bg-success ms-2">' . __('messages.reserved_by_someone') . '</span>')
                                            : '<span class="badge bg-secondary ms-2">' . __('messages.available') . '</span>' !!}
                                    </div>
                                    @if($wish->price)
                                        <div class="wish-card-price mb-2">{{ $wish->formatted_price }}</div>
                                    @endif
                                    <div class="mt-auto">
                                        @if($wish->url)
                                            <a href="{{ $wish->url }}" target="_blank" class="wish-btn btn-outline-primary mb-2 w-100">
                                                {{ __('messages.wish_url') }}
                                            </a>
                                        @endif
                                        @if(!$wish->is_reserved)
                                            <form method="POST" action="{{ route('wishes.reserve', ['wishList' => $wishList->id, 'wish' => $wish->id]) }}">
                                                @csrf
                                                <button type="submit" class="wish-btn btn-success w-100">{{ __('messages.reserve') }}</button>
                                            </form>
                                        @elseif($wish->reservation && auth()->check() && $wish->reservation->user_id === auth()->id())
                                            <form method="POST" action="{{ route('wishes.unreserve', ['wishList' => $wishList->id, 'wish' => $wish->id]) }}">
                                                @csrf
                                                <button type="submit" class="wish-btn btn-danger w-100">{{ __('messages.cancel_reservation') }}</button>
                                            </form>
                                        @endif
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

@if($isGuest)
<!-- Модальное окно для гостей -->
<div class="modal fade show" id="guestModal" tabindex="-1" aria-labelledby="guestModalLabel" aria-hidden="false" style="display: block; z-index: 1055;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="guestModalLabel">{{ __('messages.register_to_reserve') }}</h5>
        <button type="button" class="btn-close" onclick="closeModal('guestModal')" aria-label="{{ __('messages.close') }}"></button>
      </div>
      <div class="modal-body">
        <p>{{ __('messages.guests_cannot_reserve') }}</p>
        <div class="modal-footer">
            <a href="{{ route('register') }}" class="btn btn-dark w-100 mb-2">{{ __('messages.register') }}</a>
            <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">{{ __('messages.login') }}</a>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal-backdrop fade show" style="z-index: 1050;"></div>
@endif

@if(!$isGuest && !$isFriend && auth()->check() && auth()->id() !== $user->id)
<!-- Модальное окно для не-друзей -->
<div class="modal fade show" id="friendModal" tabindex="-1" aria-labelledby="friendModalLabel" aria-hidden="false" style="display: block; z-index: 1055;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="friendModalLabel">{{ __('messages.add_user_to_friends') }}</h5>
        <button type="button" class="btn-close" onclick="closeModal('friendModal')" aria-label="{{ __('messages.close') }}"></button>
      </div>
      <div class="modal-body">
        <p>{{ __('messages.friends_can_reserve') }}</p>
        <div class="modal-footer">
            <form method="POST" action="{{ route('profile.sendFriendRequest', ['userId' => $user->id]) }}">
                @csrf
                <button type="submit" class="btn btn-dark w-100 mb-2">{{ __('messages.add_to_friends') }}</button>
            </form>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal-backdrop fade show" style="z-index: 1050;"></div>
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('css/wishes.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/modal.js') }}"></script>
@endpush
@endsection 