@extends('layouts.app')

@section('content')
<div class="wishlists-container">
    <div class="container py-4">
        <div class="wishlists-card">
            <h1 class="wishlists-title">{{ __('messages.my_wish_lists') }}</h1>
            @push('styles')
            <link rel="stylesheet" href="{{ asset('css/wishlists.css') }}">
            @endpush
            <div class="wishlists-grid">
                <a href="{{ route('wish-lists.create') }}" class="wishlist-card add-wishlist-card text-decoration-none">
                    <div class="wishlist-card-body d-flex align-items-center justify-content-center">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="24" cy="24" r="24" fill="none"/>
                                <path class="add-plus" d="M24 14V34" stroke="#667eea" stroke-width="4" stroke-linecap="round"/>
                                <path class="add-plus" d="M14 24H34" stroke="#667eea" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                            <p class="mt-3 mb-0 text-muted">{{ __('messages.create_new_list') }}</p>
                        </div>
                    </div>
                </a>
                @forelse($wishLists as $wishList)
                    <div class="wishlist-card">
                        <div class="wishlist-card-header">
                            <h5 class="wishlist-card-title">{{ $wishList->title }}</h5>
                            <div class="wishlist-card-subtitle">
                                {{ __('messages.created') }} {{ $wishList->created_at->format('d.m.Y') }}
                                <span class="currency-badge ms-2">
                                    <i class="bi bi-currency-exchange me-1"></i>
                                    {{ $wishList->currency }}
                                </span>
                            </div>
                        </div>
                        <div class="wishlist-card-body">
                            <p class="wishlist-card-description">{{ $wishList->description }}</p>
                            <div class="wishlist-card-stats">
                                <div class="wishlist-stat">
                                    <span class="wishlist-stat-number">{{ $wishList->wishes->count() }}</span>
                                    <div class="wishlist-stat-label">{{ __('messages.wishes') }}</div>
                                </div>
                                <div class="wishlist-stat">
                                    <span class="wishlist-stat-number">{{ $wishList->wishes->where('is_reserved', true)->count() }}</span>
                                    <div class="wishlist-stat-label">{{ __('messages.reserved') }}</div>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('wishes.index', $wishList->id) }}" class="wishlist-btn btn-primary text-center">{{ __('messages.open') }}</a>
                                <a href="{{ route('wish-lists.edit', $wishList) }}" class="wishlist-btn btn-outline-primary text-center">{{ __('messages.edit_list') }}</a>
                                <button class="wishlist-btn btn-outline-primary text-center" data-bs-toggle="modal" data-bs-target="#qrModal-{{ $wishList->id }}">{{ __('messages.qr_code') }}</button>
                                <button class="wishlist-btn btn-outline-primary text-center" onclick="navigator.clipboard.writeText('{{ route('wish-lists.public', $wishList->uuid) }}'); return false;">{{ __('messages.copy_link') }}</button>
                                <form action="{{ route('wish-lists.destroy', $wishList) }}" method="POST" style="display:inline-block; width: 100%;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="wishlist-btn btn-danger w-100" onclick="return confirm('{{ __('messages.confirm_delete_list') }}')">{{ __('messages.delete_list') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-list-ul"></i>
                        </div>
                        <div class="empty-state-text">{{ __('messages.no_lists_yet') }}</div>
                        <div class="empty-state-hint">{{ __('messages.create_your_first_list') }}</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

<!-- QR Modals -->
@foreach($wishLists as $wishList)
<div class="modal fade" id="qrModal-{{ $wishList->id }}" tabindex="-1" aria-labelledby="qrModalLabel-{{ $wishList->id }}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="qrModalLabel-{{ $wishList->id }}">{{ __('messages.qr_code_for_invitation') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
      </div>
      <div class="modal-body d-flex flex-column align-items-center">
        <div class="qr-code-wrapper">
            <div id="qrcode-{{ $wishList->id }}" class="qr-code-container">
                <div class="text-muted">{{ __('messages.loading_qr_code') }}</div>
            </div>
        </div>
        <p class="mt-3 small text-muted text-center">{{ __('messages.link') }}: <br><a href="{{ route('wish-lists.public', $wishList->uuid) }}" target="_blank">{{ route('wish-lists.public', $wishList->uuid) }}</a></p>
      </div>
    </div>
  </div>
</div>
@endforeach

@push('scripts')
<!-- Load QRious library first -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

<!-- Initialize QR data -->
<script>
window.wishListQrData = [
    @foreach($wishLists as $wishList)
        {id: {{ $wishList->id }}, url: "{{ route('wish-lists.public', $wishList->uuid) }}"}@if(!$loop->last),@endif
    @endforeach
];
</script>

<!-- Load our QR script -->
<script src="{{ asset('js/wishlist-index.js') }}"></script>
@endpush 