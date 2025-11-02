@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="min-height: 80vh;">
<div class="row justify-content-center">
  <div class="col-12 col-md-10 col-lg-8">
    <div class="card shadow-lg rounded-4 overflow-hidden d-flex flex-row" style="min-height: 60vh;">

      <aside class="friends-sidebar bg-light border-end" style="width: 350px; min-height: 60vh;">

        <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
          <h5 class="fw-bold m-0">{{ __('messages.friends') }}</h5>
          @if(count($incomingRequests) > 0)
          <button class="btn btn-outline-secondary position-relative" data-bs-toggle="modal" data-bs-target="#friendRequestsModal" aria-label="{{ __('messages.incoming_requests') }}">
            <i class="bi bi-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              {{ count($incomingRequests) }}
            </span>
          </button>
          @endif
        </div>

        <div class="px-3 py-2 d-flex gap-2 align-items-center">
            <input type="text" class="form-control form-control-sm" id="friendSearch" placeholder="{{ __('messages.search') }}...">
            <a href="{{ route('friends.search') }}" class="btn btn-dark btn-sm" title="{{ __('messages.add_friend') }}"><i class="bi bi-person-plus-fill"></i></a>
        </div>
        <nav class="friends-list" style="max-height: calc(60vh - 130px);">
          @forelse($friends as $friend)
          <a href="{{ route('friends.index', ['friend_id' => $friend->id]) }}" class="d-flex align-items-center gap-3 px-4 py-3 friend-link {{ optional($selectedFriend)->id === $friend->id ? 'bg-primary bg-opacity-10' : 'text-dark' }} rounded-3 mb-2 text-decoration-none transition">
            @if($friend->avatar ?? false)
            <img src="{{ $friend->avatar }}" alt="avatar" class="rounded-circle" style="width:42px; height:42px; object-fit:cover; box-shadow: 0 2px 6px rgb(0 0 0 / 0.15);">
            @else
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white fw-semibold" style="width:42px; height:42px; font-size:1.2rem;">
              {{ mb_substr($friend->name,0,1) }}
            </div>
            @endif
            <div class="flex-grow-1">
              <div class="fw-semibold">{{ $friend->name }}</div>
              <div class="text-muted" style="font-size:0.9rem;">{{ __('messages.offline') }}</div>
            </div>
          </a>
          @empty
          <div class="text-muted px-4 py-3 fst-italic">{{ __('messages.no_friends') }}</div>
          @endforelse
        </nav>

      </aside>

      <main class="flex-grow-1 p-5 d-flex flex-column align-items-center justify-content-center" style="min-height: 60vh;">
        @if($selectedFriend)
        <div class="text-center w-100" style="max-width: 360px;">
          <div class="d-flex align-items-center justify-content-center mb-4">
          @if($selectedFriend->avatar ?? false)
          <img src="{{ $selectedFriend->avatar }}" alt="avatar" class="rounded-circle mb-4" style="width:100px; height:100px; object-fit:cover; box-shadow: 0 2px 12px rgb(0 0 0 / 0.2);">
          @else
          <div class="rounded-circle d-flex align-items-center justify-content-center mb-4 bg-secondary text-white fw-bold fs-2" style="width:100px; height:100px;">
            {{ mb_substr($selectedFriend->name,0,1) }}
          </div>
          @endif
          <div class="d-flex flex-column align-items-center" style="padding-left: 30px;">
          <h3 class="fw-bold mb-1">{{ $selectedFriend->name }}</h3>
          <p class="text-muted mb-3">{{ $selectedFriend->email }}</p>
          </div>
          </div>
          <a href="{{ route('wishes.user', ['user' => $selectedFriend->id]) }}" class="btn btn-outline-dark mb-3 w-100 d-flex justify-content-center align-items-center gap-2">
            <i class="bi bi-heart-fill"></i> {{ __('messages.view_wishes') }}
          </a>
          <a href="{{ route('profile.user', ['user' => $selectedFriend->id]) }}" class="btn btn-outline-primary mb-3 w-100 d-flex justify-content-center align-items-center gap-2">
            <i class="bi bi-person-circle"></i> {{ __('messages.view_profile') }}
          </a>
          <form action="{{ route('friends.remove', ['user' => $selectedFriend->id]) }}" method="POST" class="w-100" onsubmit="return confirm('{{ __('messages.confirm_remove_friend') }}')">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 d-flex justify-content-center gap-2">
              <i class="bi bi-person-x-fill"></i> {{ __('messages.remove_friend') }}
            </button>
          </form>
        </div>
        @else
        <div class="text-center text-muted fs-5 d-flex flex-column align-items-center gap-3">
          <i class="bi bi-people" style="font-size:4rem;"></i>
          <div>{{ __('messages.select_friend_to_view') }}</div>
        </div>
        @endif
      </main>

    </div>
  </div>
</div>


    <div class="modal fade" id="friendRequestsModal" tabindex="-1" aria-labelledby="friendRequestsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content shadow-lg rounded-4 border-0">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold fs-4 text-dank" id="friendRequestsModalLabel">{{ __('messages.friend_requests') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
      </div>
      <div class="modal-body pt-2">
        @if(count($incomingRequests) === 0 && count($outgoingRequests) === 0)
          <div class="text-center text-muted py-5 fs-5">{{ __('messages.no_requests') }}</div>
        @else
          <section class="mb-4">
            <h6 class="text-dark fw-semibold mb-3">{{ __('messages.incoming_requests') }}</h6>
            @forelse($incomingRequests as $req)
              <div class="d-flex align-items-center gap-3 p-3 mb-3 rounded-3 shadow-sm hover-shadow" style="background:#fff; transition: box-shadow 0.3s ease;">
                @if($req->sender->avatar ?? false)
                  <img src="{{ $req->sender->avatar }}" alt="avatar" class="rounded-circle" style="width:40px; height:40px; object-fit:cover; box-shadow:0 2px 6px rgb(0 0 0 / 0.15);">
                @else
                  <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-secondary fw-semibold" style="width:40px; height:40px; font-size:1.2rem;">
                    {{ mb_substr($req->sender->name,0,1) }}
                  </div>
                @endif
                <div class="flex-grow-1">
                  <span class="d-block fw-semibold text-dark fs-6">{{ $req->sender->name }}</span>
                </div>
                <div class="d-flex gap-2">
                    <form action="{{ route('friends.accept', $req->id) }}" method="POST" class="m-0">
                      @csrf
                      <button type="submit" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-check2-circle"></i> {{ __('messages.accept') }}
                      </button>
                    </form>
                    <form action="{{ route('friends.decline', $req->id) }}" method="POST" class="m-0">
                      @csrf
                      <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-x-circle"></i> {{ __('messages.decline') }}
                      </button>
                    </form>
                </div>
              </div>
            @empty
              <div class="text-muted fst-italic ps-2">{{ __('messages.no_incoming_requests') }}</div>
            @endforelse
          </section>

          <hr class="my-4">

          <section>
            <h6 class="text-secondary fw-semibold mb-3">{{ __('messages.outgoing_requests') }}</h6>
            @forelse($outgoingRequests as $req)
              <div class="d-flex align-items-center gap-3 p-3 mb-3 rounded-3 shadow-sm" style="background:#f9f9f9;">
                @if($req->receiver->avatar ?? false)
                  <img src="{{ $req->receiver->avatar }}" alt="avatar" class="rounded-circle" style="width:40px; height:40px; object-fit:cover; box-shadow:0 2px 6px rgb(0 0 0 / 0.1);">
                @else
                  <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-secondary fw-semibold" style="width:40px; height:40px; font-size:1.2rem;">
                    {{ mb_substr($req->receiver->name,0,1) }}
                  </div>
                @endif
                <span class="flex-grow-1 fw-semibold fs-6 text-dark">{{ $req->receiver->name }}</span>
                <span class="badge bg-warning text-dark fw-semibold">{{ __('messages.pending') }}</span>
              </div>
            @empty
              <div class="text-muted fst-italic ps-2">{{ __('messages.no_outgoing_requests') }}</div>
            @endforelse
          </section>
        @endif
      </div>
    </div>
  </div>
</div>

</div>
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('css/friends.css') }}">
<style>
/* Remove Friend Button */
.btn-outline-danger {
    border-radius: 20px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    border: 2px solid #dc3545;
    color: #dc3545;
    background: transparent;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.1);
}

.btn-outline-danger:hover {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
    border-color: #dc3545;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
}

.btn-outline-danger:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.1);
}

/* View Wishes Button */
.btn-outline-dark {
    border-radius: 20px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    border: 2px solid #343a40;
    color: #343a40;
    background: transparent;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(52, 58, 64, 0.1);
}

.btn-outline-dark:hover {
    background: linear-gradient(135deg, #343a40 0%, #495057 100%);
    border-color: #343a40;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(52, 58, 64, 0.2);
}

.btn-outline-dark:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(52, 58, 64, 0.1);
}

/* Friend Link Hover */
.friend-link {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.friend-link:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.friend-link.bg-primary.bg-opacity-10 {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%) !important;
    border-left: 3px solid #007bff;
}
</style>
@endpush
@endsection
