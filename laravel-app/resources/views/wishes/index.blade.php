@extends('layouts.app')

@section('content')
<div class="wishes-container">
    <div class="container">
        <div class="wishes-card">
            <h1 class="wishes-title">{{ $wishList->title }}</h1>
            @push('styles')
            <link rel="stylesheet" href="{{ asset('css/wishes.css') }}">
            @endpush
            <a href="{{ route('wish-lists.index') }}" class="back-link">
                <svg fill="none" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ __('messages.back') }}
            </a>
            <div class="row g-4">
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <a href="{{ route('wishes.create', $wishList->id) }}" class="wish-card add-gift-card">
                        <div class="wish-card-body text-center d-flex align-items-center justify-content-center">
                            <div>
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="24" cy="24" r="24" fill="none"/>
                                    <path class="add-plus" d="M24 14V34" stroke="#667eea" stroke-width="4" stroke-linecap="round"/>
                                    <path class="add-plus" d="M14 24H34" stroke="#667eea" stroke-width="4" stroke-linecap="round"/>
                                </svg>
                                <p class="mt-3 mb-0 text-muted">{{ __('messages.add_wish') }}</p>
                            </div>
                        </div>
                    </a>
                </div>
                @forelse($wishes as $wish)
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
                                                ? '<span class="badge bg-success ms-2">' . __('messages.reserved_by_someone') . '</span>'
                                                : '<span class="badge bg-secondary ms-2">' . __('messages.available') . '</span>' !!}
                                          </div>
                                          <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('wishes.edit', [$wishList->id, $wish->id]) }}" class="wish-btn btn-outline-primary">{{ __('messages.edit_wish') }}</a>
                                            <form action="{{ route('wishes.destroy', [$wishList->id, $wish->id]) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="wish-btn btn-danger" onclick="return confirm('{{ __('messages.confirm_delete_wish') }}')">{{ __('messages.delete_wish') }}</button>
                                            </form>
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
                                        ? '<span class="badge bg-success ms-2">' . __('messages.reserved_by_someone') . '</span>'
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
                                <div class="mt-auto d-flex gap-2">
                                    <a href="{{ route('wishes.edit', [$wishList->id, $wish->id]) }}" class="wish-btn btn-outline-primary w-100">{{ __('messages.edit_wish') }}</a>
                                    <form action="{{ route('wishes.destroy', [$wishList->id, $wish->id]) }}" method="POST" style="display:inline-block; width:100%;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="wish-btn btn-danger w-100" onclick="return confirm('{{ __('messages.confirm_delete_wish') }}')">{{ __('messages.delete_wish') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bi bi-gift"></i>
                            </div>
                            <div class="empty-state-text">{{ __('messages.no_wishes_yet') }}</div>
                            <div class="empty-state-hint">{{ __('messages.add_your_first_wish') }}</div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush
