@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="p-4" style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px 0 #e0e0e0; min-width: 340px; max-width: 400px; width: 100%;">
        <h1 class="mb-4 text-center" style="color: #222; font-weight: 700;">Добавить желание в "{{ $wishList->title }}"</h1>
        <form action="{{ route('wishes.store', $wishList->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="title" class="form-label" style="color: #333;">Название</label>
                <input type="text" name="title" id="title" class="form-control" required style="background: #f6f6f7; border-radius: 8px; border: 1px solid #e0e0e0; color: #222;">
            </div>
            <div class="mb-3">
                <label for="url" class="form-label" style="color: #333;">Ссылка (необязательно)</label>
                <input type="url" name="url" id="url" class="form-control" style="background: #f6f6f7; border-radius: 8px; border: 1px solid #e0e0e0; color: #222;">
            </div>
            <div class="mb-3">
                <label for="image" class="form-label" style="color: #333;">Картинка (URL)</label>
                <input type="url" name="image" id="image" class="form-control" style="background: #f6f6f7; border-radius: 8px; border: 1px solid #e0e0e0; color: #222;">
            </div>
            <div class="mb-3">
                <label for="price" class="form-label" style="color: #333;">Цена (необязательно)</label>
                <input type="number" name="price" id="price" class="form-control" step="0.01" style="background: #f6f6f7; border-radius: 8px; border: 1px solid #e0e0e0; color: #222;">
            </div>
            <button type="submit" class="btn btn-dark w-100" style="border-radius: 8px;">Добавить</button>
            <div class="mt-3 text-center">
                <a href="{{ route('wishes.index', $wishList->id) }}" style="color: #222; text-decoration: underline;">Назад к списку</a>
            </div>
        </form>
    </div>
</div>
@endsection 