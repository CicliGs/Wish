@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="p-4" style="background: #fff; border-radius: 16px; box-shadow: 0 2px 16px 0 #e0e0e0; min-width: 340px; max-width: 400px; width: 100%;">
        <h1 class="mb-4 text-center" style="color: #222; font-weight: 700;">Вход</h1>
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label" style="color: #333;">Email</label>
                <input type="email" name="email" id="email" class="form-control" required style="background: #f6f6f7; border-radius: 8px; border: 1px solid #e0e0e0; color: #222;">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label" style="color: #333;">Пароль</label>
                <input type="password" name="password" id="password" class="form-control" required style="background: #f6f6f7; border-radius: 8px; border: 1px solid #e0e0e0; color: #222;">
            </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
            <button type="submit" class="btn btn-dark w-100" style="border-radius: 8px;">Войти</button>
            <div class="mt-3 text-center">
                Нет аккаунта? <a href="{{ route('register') }}" style="color: #222; text-decoration: underline;">Зарегистрироваться</a>
            </div>
        </form>
    </div>
</div>
@endsection
