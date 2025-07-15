@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">{{ $wishList->title }}</h1>
    <p class="mb-4 text-muted">{{ $wishList->description }}</p>
    <div class="mb-4 text-center">
        <a href="{{ route('wish-lists.public', $wishList->public_id) }}" class="btn btn-outline-primary">Публичная ссылка на этот список</a>
    </div>
    <div class="row g-4">
        @forelse($wishes as $wish)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm" style="border-radius: 16px; background: #fff;">
                    @if($wish->image)
                        <img src="{{ $wish->image }}" alt="image" class="card-img-top" style="max-height: 180px; object-fit: cover; border-radius: 16px 16px 0 0;">
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title" style="color: #222;">{{ $wish->title }}</h5>
                        @if($wish->url)
                            <a href="{{ $wish->url }}" target="_blank" class="mb-2" style="color: #444; text-decoration: underline;">Ссылка на магазин</a>
                        @endif
                        <p class="mb-2" style="color: #444;">{{ $wish->price ? number_format($wish->price, 2) . ' BYN' : '' }}</p>
                        <div class="mb-2">
                            {!! $wish->is_reserved ? '<span class="badge bg-success">Забронировано</span>' : '<span class="badge bg-secondary">Свободно</span>' !!}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted">Желаний пока нет</div>
        @endforelse
    </div>
</div>
@endsection 