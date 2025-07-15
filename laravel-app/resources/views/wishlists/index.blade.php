@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Мои списки желаний</h1>
    <a href="{{ route('wish-lists.create') }}" class="btn btn-dark mb-4" style="border-radius: 8px;">Создать новый список</a>
    <div class="row g-4">
        @forelse($wishLists as $wishList)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm" style="border-radius: 16px; background: #fff;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title" style="color: #222;">{{ $wishList->title }}</h5>
                        <p class="mb-2" style="color: #444;">{{ $wishList->description }}</p>
                        <a href="{{ route('wishes.index', $wishList->id) }}" class="btn btn-outline-dark mt-auto" style="border-radius: 8px;">Открыть</a>
                        <div class="mt-2 d-flex flex-column gap-2 w-100">
                            <a href="{{ route('wish-lists.edit', $wishList) }}" class="btn btn-sm btn-outline-secondary w-100" style="border-radius: 8px;">Редактировать</a>
                            <button class="btn btn-sm btn-outline-success w-100" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#qrModal-{{ $wishList->id }}">QR-код</button>
                            <button class="btn btn-sm btn-outline-primary w-100" style="border-radius: 8px;" onclick="navigator.clipboard.writeText('{{ route('wish-lists.public', $wishList->public_id) }}'); return false;">Скопировать ссылку</button>
                            <form action="{{ route('wish-lists.destroy', $wishList) }}" method="POST" style="display:inline-block; width: 100%;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100" style="border-radius: 8px;" onclick="return confirm('Удалить список?')">Удалить</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted">Списков пока нет</div>
        @endforelse
    </div>
</div>
@endsection
<!-- QR Modal -->
@foreach($wishLists as $wishList)
<div class="modal fade" id="qrModal-{{ $wishList->id }}" tabindex="-1" aria-labelledby="qrModalLabel-{{ $wishList->id }}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="qrModalLabel-{{ $wishList->id }}">QR-код для приглашения</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex flex-column align-items-center">
        <div id="qrcode-{{ $wishList->id }}"></div>
        <p class="mt-3 small text-muted text-center">Ссылка: <br><a href="{{ route('wish-lists.public', $wishList->public_id) }}" target="_blank">{{ route('wish-lists.public', $wishList->public_id) }}</a></p>
      </div>
    </div>
  </div>
</div>
@endforeach
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script>
window.wishListQrData = [
    @foreach($wishLists as $wishList)
        {id: {{ $wishList->id }}, url: "{{ route('wish-lists.public', $wishList->public_id) }}"}@if(!$loop->last),@endif
    @endforeach
];
</script>
<script src="{{ asset('js/wishlist-qr.js') }}"></script>
@endpush 