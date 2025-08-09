@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">{{ $wishList->title }}</h1>
    <p class="mb-4 text-muted">{{ $wishList->description }}</p>
    <div class="row g-4">
        @forelse($wishes as $wish)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm wish-card d-flex flex-column justify-content-between" style="border-radius: 16px; background: #fff;">
                    @if($wish->image)
                        <img src="{{ $wish->image }}" alt="image" class="card-img-top wish-img" style="max-height: 180px; object-fit: cover; border-radius: 16px 16px 0 0;">
                    @endif
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="card-title mb-0 wish-title" style="font-weight: 700; color: #222;">{{ $wish->title }}</h5>
                            {!! $wish->is_reserved
                                ? ($wish->reservation && auth()->check() && $wish->reservation->user_id === auth()->id()
                                    ? '<span class="badge bg-success ms-2">Вы забронировали</span>'
                                    : '<span class="badge bg-success ms-2">Забронировано</span>')
                                : '<span class="badge bg-secondary ms-2">Свободно</span>' !!}
                        </div>
                        @if($wish->price)
                            <div class="wish-price mb-2">{{ $wish->formatted_price }}</div>
                        @endif
                        <div class="wish-actions">
                            <a href="{{ $wish->url }}" target="_blank" class="btn btn-outline-dark btn-sm wish-link mb-2">
                                <i class="bi bi-box-arrow-up-right"></i> {{ __('messages.wish_url') }}
                            </a>
                            @if($wish->is_reserved)
                                <form method="POST" action="{{ route('wishes.unreserve', ['wishList' => $wishList->id, 'wish' => $wish->id]) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">{{ __('messages.cancel_reservation') }}</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('wishes.reserve', ['wishList' => $wishList->id, 'wish' => $wish->id]) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-100">{{ __('messages.reserve') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted">Желаний пока нет</div>
        @endforelse
    </div>
</div>

@if($isGuest)
<!-- Модальное окно для гостей -->
<div class="modal fade show" id="guestModal" tabindex="-1" aria-labelledby="guestModalLabel" aria-hidden="false" style="display: block; z-index: 1055;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="guestModalLabel">Зарегистрируйтесь, чтобы забронировать подарок</h5>
        <button type="button" class="btn-close" onclick="closeModal('guestModal')" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Только зарегистрированные пользователи могут бронировать подарки и добавлять в друзья.</p>
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
        <h5 class="modal-title" id="friendModalLabel">Добавьте пользователя в друзья</h5>
        <button type="button" class="btn-close" onclick="closeModal('friendModal')" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Чтобы бронировать подарки и видеть больше информации, добавьте пользователя в друзья.</p>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/modal.js') }}"></script>
@endpush
@endsection 