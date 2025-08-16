@extends('layouts.app')

@section('content')
<div class="wishes-container">
    <div class="container">
        <div class="wishes-card">
            <h1 class="wishes-title">{{ __('messages.all_wishes_of') }} {{ $user->name }}</h1>
            @push('styles')
            <link rel="stylesheet" href="{{ asset('css/wishes.css') }}">
            @endpush
            <a href="{{ route('wishes.user', $user->id) }}" class="back-link">
                <svg fill="none" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ __('messages.back') }}
            </a>
    @if($wishes->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-gift"></i>
                    </div>
                    <div class="empty-state-text">{{ __('messages.no_wishes_yet') }}</div>
                    <div class="empty-state-hint">{{ __('messages.user_has_no_wishes') }}</div>
                </div>
    @else
        <div class="row g-4">
            @foreach($wishes as $wish)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="wish-card">
                        @if($wish->image)
                                    <a href="#" class="wish-image-link" data-wish-id="{{ $wish->id }}">
                                        <img src="{{ $wish->image }}" alt="image" class="wish-card-img">
                                    </a>
                                @endif
                                <div class="wish-card-body d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h5 class="wish-card-title">{{ $wish->title }}</h5>
                                        {!! $wish->is_reserved
                                            ? '<span class="badge bg-success ms-2">' . __('messages.reserved_by_someone') . '</span>'
                                            : '<span class="badge bg-secondary ms-2">' . __('messages.available') . '</span>' !!}
                                    </div>
                                    <div class="mb-2 text-muted" style="font-size:0.98rem;">
                                        <i class="bi bi-list-task me-1"></i>{{ __('messages.from_list') }}: <b>{{ $wish->wishList->title ?? __('messages.untitled') }}</b>
                                  </div>
                                      @if($wish->price)
                                        <div class="wish-card-price">{{ $wish->formatted_price }}</div>
                                      @endif
                                      @if($wish->url)
                                        <a href="{{ $wish->url }}" target="_blank" class="wish-btn btn-outline-primary mb-2">
                                            {{ __('messages.wish_url') }}
                                        </a>
                                      @endif
                                      @if(auth()->check() && auth()->id() === $wish->user_id)
                                    <div class="mt-auto d-flex gap-2">
                                        <a href="{{ route('wishes.edit', [$wish->wishList->id ?? 0, $wish->id]) }}" class="wish-btn btn-outline-primary w-100">{{ __('messages.edit_wish') }}</a>
                                        <form action="{{ route('wishes.destroy', [$wish->wishList->id ?? 0, $wish->id]) }}" method="POST" style="display:inline-block; width:100%;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="wish-btn btn-danger w-100" onclick="return confirm('{{ __('messages.confirm_delete_wish') }}')">{{ __('messages.delete_wish') }}</button>
                                        </form>
                                      </div>
                                    @endif
                                    @if(auth()->check() && auth()->id() !== $wish->user_id)
                                        <div class="mt-auto">
                                            @if(!$wish->is_reserved && $wish->wishList)
                                                <button type="button" class="wish-btn btn-success w-100 reserve-btn" data-wish-id="{{ $wish->id }}">
                                                    {{ __('messages.reserve') }}
                                                </button>
                                            @elseif($wish->is_reserved && $wish->reservation && $wish->reservation->user_id === auth()->id() && $wish->wishList)
                                                <button type="button" class="wish-btn btn-danger w-100 unreserve-btn" data-wish-id="{{ $wish->id }}">
                                                    {{ __('messages.unreserve') }}
                                                </button>
                                      @endif
                                    </div>
                                    @endif
                                </div>
                              </div>
                        </div>
                    @endforeach
                            </div>
                        @endif
        </div>
    </div>
</div>

<!-- Wish Image Modal -->
<div class="modal fade" id="wishImageModal" tabindex="-1" aria-labelledby="wishImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="wishImageModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
            </div>
            <div class="modal-body text-center">
                <img id="wishModalImage" src="" alt="image" class="img-fluid mb-3" style="max-height:70vh;">
                <div class="mb-2">
                    <div id="wishModalPrice" class="wish-card-price" style="display:none;"></div>
                    <a id="wishModalUrl" href="" target="_blank" class="wish-btn btn-outline-primary" style="display:none;">
                        {{ __('messages.wish_url') }}
                    </a>
                    <div class="mb-2">
                        <span id="wishModalStatus" class="badge ms-2"></span>
                            </div>
                            <div class="mb-2 text-muted" style="font-size:0.98rem;">
                        <i class="bi bi-list-task me-1"></i>{{ __('messages.from_list') }}: <b id="wishModalList"></b>
                            </div>
                    <div id="wishModalActions" class="d-flex gap-2 justify-content-center" style="display:none;">
                        <a id="wishModalEdit" href="" class="wish-btn btn-outline-primary">{{ __('messages.edit_wish') }}</a>
                        <form id="wishModalDelete" action="" method="POST" style="display:inline-block;">
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
@endsection

@push('scripts')
<script src="{{ asset('js/wish-user.js') }}"></script>
@endpush