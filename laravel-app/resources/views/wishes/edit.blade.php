@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Редактировать желание</h1>
    <form action="{{ route('wishes.update', $wish) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="title" class="form-label">Название</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ $wish->title }}" required>
        </div>
        <div class="mb-3">
            <label for="url" class="form-label">Ссылка</label>
            <input type="url" name="url" id="url" class="form-control" value="{{ $wish->url }}">
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Картинка (URL)</label>
            <input type="url" name="image" id="image" class="form-control" value="{{ $wish->image }}">
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Примерная цена</label>
            <input type="number" step="0.01" name="price" id="price" class="form-control" value="{{ $wish->price }}">
        </div>
        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="{{ route('wishes.index') }}" class="btn btn-secondary">Назад</a>
    </form>
</div>
@endsection 