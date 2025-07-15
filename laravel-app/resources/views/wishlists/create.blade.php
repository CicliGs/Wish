//TODO ИНТЕРНАЦИАНАЛИЗАЦИЯ

@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="p-4" style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px 0 #e0e0e0; min-width: 340px; max-width: 400px; width: 100%;">
        <h1 class="mb-4 text-center" style="color: #222; font-weight: 700;">Создать список желаний</h1>
        <form action="{{ route('wish-lists.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="title" class="form-label" style="color: #333;">Название списка</label>
                <input type="text" name="title" id="title" class="form-control" required style="background: #f6f6f7; border-radius: 8px; border: 1px solid #e0e0e0; color: #222;">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label" style="color: #333;">Описание (необязательно)</label>
                <textarea name="description" id="description" class="form-control" rows="3" style="background: #f6f6f7; border-radius: 8px; border: 1px solid #e0e0e0; color: #222;"></textarea>
            </div>
            <button type="submit" class="btn btn-dark w-100" style="border-radius: 8px;">Создать</button>
            <div class="mt-3 text-center">
                <a href="{{ route('wish-lists.index') }}" style="color: #222; text-decoration: underline;">Назад к спискам</a>
            </div>
        </form>
    </div>
</div>
@endsection
