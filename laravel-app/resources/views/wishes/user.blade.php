@extends('layouts.app')

@section('content')
<div class="wishes-container">
    <div class="container">
        <div class="wishes-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="wishes-title">{{ __('messages.wish_list_of') }} {{ $user->name }}</h1>
                <a href="{{ route('wishes.user', $user->id) }}" class="wish-btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> {{ __('messages.back_to_lists') }}
                </a>
            </div>
            <h2 class="wishes-title" style="font-size: 1.8rem; margin-bottom: 2rem;">{{ __('messages.gifts_from_list') }}: {{ $wishList->title }}</h2>
            @push('styles')
            <link rel="stylesheet" href="{{ asset('css/wishes.css') }}">
            @endpush
            
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
                                @if($wish->image)
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#wishImageModal-{{ $wish->id }}">
                                        <img src="{{ $wish->image }}" alt="image" class="wish-card-img">
                                    </a>
                                    <div class="modal fade" id="wishImageModal-{{ $wish->id }}" tabindex="-1" aria-labelledby="wishImageModalLabel-{{ $wish->id }}" aria-hidden="true">
                                      <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                          <div class="modal-header">
                                            <h5 class="modal-title" id="wishImageModalLabel-{{ $wish->id }}">{{ $wish->title }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                                          </div>
                                          <div class="modal-body text-center">
                                            <img src="{{ $wish->image }}" alt="image" class="img-fluid mb-3" style="max-height:70vh;">
                                            <div class="mb-2">
                                              @if($wish->price)
                                                <div class="wish-card-price">{{ number_format($wish->price, 2) }} BYN</div>
                                              @endif
                                              @if($wish->url)
                                                <a href="{{ $wish->url }}" target="_blank" class="wish-btn btn-outline-primary">
                                                  {{ __('messages.wish_url') }}
                                                </a>
                                              @endif
                                              <div class="mb-2">
                                                {!! $wish->is_reserved
                                                    ? ($wish->reservation && auth()->check() && $wish->reservation->user_id === auth()->id()
                                                        ? '<span class="badge bg-success ms-2">' . __('messages.reserved_by_you') . '</span>'
                                                        : '<span class="badge bg-success ms-2">' . __('messages.reserved_by_someone') . '</span>')
                                                    : '<span class="badge bg-secondary ms-2">' . __('messages.available') . '</span>' !!}
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
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
                                        <div class="wish-card-price">{{ number_format($wish->price, 2) }} BYN</div>
                                    @endif
                                    @if($wish->url)
                                        <a href="{{ $wish->url }}" target="_blank" class="wish-btn btn-outline-primary mb-2">
                                            {{ __('messages.wish_url') }}
                                        </a>
                                    @endif
                                    <div class="mt-auto">
                                        @if(!$wish->is_reserved && auth()->check() && auth()->id() !== $wishList->user_id)
                                            <form action="{{ route('wishes.reserve', ['wishList' => $wishList->id, 'wish' => $wish->id]) }}" method="POST" style="display:inline-block; width:100%;">
                                                @csrf
                                                <button type="submit" class="wish-btn btn-success w-100">{{ __('messages.reserve') }}</button>
                                            </form>
                                        @elseif($wish->is_reserved && auth()->check() && $wish->reservation && $wish->reservation->user_id === auth()->id())
                                            <form action="{{ route('wishes.unreserve', ['wishList' => $wishList->id, 'wish' => $wish->id]) }}" method="POST" style="display:inline-block; width:100%;">
                                                @csrf
                                                <button type="submit" class="wish-btn btn-danger w-100">{{ __('messages.unreserve') }}</button>
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
        <p>{{ __('messages.register_description') }}</p>
        <a href="{{ route('register') }}" class="wish-btn btn-primary w-100 mb-2">{{ __('messages.register') }}</a>
        <a href="{{ route('login') }}" class="wish-btn btn-outline-primary w-100">{{ __('messages.login_to_reserve') }}</a>
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
        <h5 class="modal-title" id="friendModalLabel">{{ __('messages.add_to_friends') }}</h5>
        <button type="button" class="btn-close" onclick="closeModal('friendModal')" aria-label="{{ __('messages.close') }}"></button>
      </div>
      <div class="modal-body">
        <p>{{ __('messages.add_to_friends_description') }}</p>
        <form action="{{ route('friends.request', $user->id) }}" method="POST">
            @csrf
            <button type="submit" class="wish-btn btn-primary w-100 mb-2">{{ __('messages.add_to_friends_button') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="modal-backdrop fade show" style="z-index: 1050;"></div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/modal.js') }}"></script>
@endpush
@endsection 