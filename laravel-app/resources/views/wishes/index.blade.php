@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">{{ $wishList->title }}</h1>
    <a href="{{ route('wish-lists.index') }}" class="btn btn-outline-secondary mb-4" style="border-radius: 8px;">Назад к спискам</a>
    <a href="{{ route('wishes.create', $wishList->id) }}" class="btn btn-dark mb-4 ms-2" style="border-radius: 8px;">Добавить желание</a>
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
                        <div class="mt-auto d-flex gap-2">
                            <a href="{{ route('wishes.edit', [$wishList->id, $wish->id]) }}" class="btn btn-sm btn-outline-dark" style="border-radius: 8px;">Редактировать</a>
                            <form action="{{ route('wishes.destroy', [$wishList->id, $wish->id]) }}" method="POST" style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" style="border-radius: 8px;" onclick="return confirm('Удалить?')">Удалить</button>
                            </form>
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