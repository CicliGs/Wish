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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSRF token для AJAX запросов
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Handle reserve button clicks
    document.querySelectorAll('.reserve-btn').forEach(function(button) {
        button.addEventListener('click', reserveHandler);
    });

    // Handle unreserve button clicks
    document.querySelectorAll('.unreserve-btn').forEach(function(button) {
        button.addEventListener('click', unreserveHandler);
    });

    function reserveHandler(e) {
        e.preventDefault();
        const wishId = this.getAttribute('data-wish-id');
        const button = this;
        
        // Disable button during request
        button.disabled = true;
        button.textContent = '{{ __("messages.processing") }}...';
        
        fetch(`/ajax/wishes/${wishId}/reserve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', data.message);
                
                // Update button to unreserve
                button.className = 'wish-btn btn-danger w-100 unreserve-btn';
                button.textContent = '{{ __("messages.unreserve") }}';
                button.classList.remove('reserve-btn');
                button.classList.add('unreserve-btn');
                
                // Update wish status badge
                const wishCard = button.closest('.wish-card');
                const statusBadge = wishCard.querySelector('.badge');
                if (statusBadge) {
                    statusBadge.textContent = '{{ __("messages.reserved_by_you") }}';
                    statusBadge.className = 'badge bg-success ms-2';
                }
                
                // Remove old event listener and add new one
                button.removeEventListener('click', reserveHandler);
                button.addEventListener('click', unreserveHandler);
                
                // Re-enable button
                button.disabled = false;
            } else {
                showAlert('error', data.message);
                // Re-enable button
                button.disabled = false;
                button.textContent = '{{ __("messages.reserve") }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', '{{ __("messages.error_occurred") }}');
            // Re-enable button
            button.disabled = false;
            button.textContent = '{{ __("messages.reserve") }}';
        });
    }

    function unreserveHandler(e) {
        e.preventDefault();
        const wishId = this.getAttribute('data-wish-id');
        const button = this;
        
        // Disable button during request
        button.disabled = true;
        button.textContent = '{{ __("messages.processing") }}...';
        
        fetch(`/ajax/wishes/${wishId}/unreserve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', data.message);
                
                // Update button to reserve
                button.className = 'wish-btn btn-success w-100 reserve-btn';
                button.textContent = '{{ __("messages.reserve") }}';
                button.classList.remove('unreserve-btn');
                button.classList.add('reserve-btn');
                
                // Update wish status badge
                const wishCard = button.closest('.wish-card');
                const statusBadge = wishCard.querySelector('.badge');
                if (statusBadge) {
                    statusBadge.textContent = '{{ __("messages.available") }}';
                    statusBadge.className = 'badge bg-secondary ms-2';
                }
                
                // Remove old event listener and add new one
                button.removeEventListener('click', unreserveHandler);
                button.addEventListener('click', reserveHandler);
                
                // Re-enable button
                button.disabled = false;
            } else {
                showAlert('error', data.message);
                // Re-enable button
                button.disabled = false;
                button.textContent = '{{ __("messages.unreserve") }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', '{{ __("messages.error_occurred") }}');
            // Re-enable button
            button.disabled = false;
            button.textContent = '{{ __("messages.unreserve") }}';
        });
    }

    // Function to show alerts
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <div class="alert-message">
                <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.classList.add('closing');
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 300);
            }
        }, 5000);
    }

    // Wish data for modals
    const wishData = {
        @foreach($wishes as $wish)
            @if($wish->wishList)
            {{ $wish->id }}: {
                title: "{{ addslashes($wish->title) }}",
                image: "{{ $wish->image }}",
                price: "{{ $wish->price }}",
                formattedPrice: "{{ $wish->formatted_price }}",
                url: "{{ $wish->url }}",
                isReserved: {{ $wish->is_reserved ? 'true' : 'false' }},
                listTitle: "{{ addslashes($wish->wishList->title ?? __('messages.untitled')) }}",
                isOwner: {{ (auth()->check() && auth()->id() === $wish->user_id) ? 'true' : 'false' }},
                editUrl: "{{ route('wishes.edit', [$wish->wishList->id, $wish->id]) }}",
                deleteUrl: "{{ route('wishes.destroy', [$wish->wishList->id, $wish->id]) }}"
            }@if(!$loop->last),@endif
            @endif
        @endforeach
    };

    // Handle wish image clicks
    document.querySelectorAll('.wish-image-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const wishId = this.getAttribute('data-wish-id');
            const wish = wishData[wishId];
            
            if (wish) {
                // Update modal content
                document.getElementById('wishImageModalLabel').textContent = wish.title;
                document.getElementById('wishModalImage').src = wish.image;
                document.getElementById('wishModalList').textContent = wish.listTitle;
                
                // Update price
                const priceElement = document.getElementById('wishModalPrice');
                if (wish.price) {
                    priceElement.textContent = wish.formattedPrice || (wish.price + ' BYN');
                    priceElement.style.display = 'block';
                } else {
                    priceElement.style.display = 'none';
                }
                
                // Update URL
                const urlElement = document.getElementById('wishModalUrl');
                if (wish.url) {
                    urlElement.href = wish.url;
                    urlElement.style.display = 'inline-block';
                } else {
                    urlElement.style.display = 'none';
                }
                
                // Update status
                const statusElement = document.getElementById('wishModalStatus');
                if (wish.isReserved) {
                    statusElement.textContent = '{{ __("messages.reserved_by_someone") }}';
                    statusElement.className = 'badge bg-success ms-2';
                } else {
                    statusElement.textContent = '{{ __("messages.available") }}';
                    statusElement.className = 'badge bg-secondary ms-2';
                }
                
                // Update actions
                const actionsElement = document.getElementById('wishModalActions');
                if (wish.isOwner) {
                    document.getElementById('wishModalEdit').href = wish.editUrl;
                    document.getElementById('wishModalDelete').action = wish.deleteUrl;
                    actionsElement.style.display = 'flex';
                } else {
                    actionsElement.style.display = 'none';
                }
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('wishImageModal'));
                modal.show();
            }
        });
    });
});
</script>
@endpush