@extends('layouts.app')

@section('content')
<div class="container py-5" style="background: #f6f6f7; border-radius: 18px; box-shadow: 0 2px 16px 0 #e0e0e0;">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="p-4 mb-5" style="background: #fff; border-radius: 16px; box-shadow: 0 2px 8px 0 #ececec;">
                <h1 class="mb-4" style="color: #222; font-weight: 700;">Виш-лист — сервис для желаемых подарков</h1>
                <p class="lead mb-0" style="color: #444;">
                    Создайте свой виш-лист, добавьте желаемые подарки с картинками, ссылками и ценой. Делитесь списком с друзьями и близкими — пусть ваши мечты сбываются!
                </p>
            </div>
            <div class="mb-5">
                <div class="d-flex justify-content-center align-items-center mb-4">
                    <div style="height: 2px; background: #e0e0e0; flex: 1;"></div>
                    <span class="mx-3" style="font-size: 1.5rem; color: #ffb300;">
                        <i class="bi bi-stars"></i>
                                </span>
                    <div style="height: 2px; background: #e0e0e0; flex: 1;"></div>
                </div>
                <h4 class="mb-4" style="color: #222; font-weight: 700; font-size: 2rem; text-align: center">
                    Как это работает?
                </h4>
                <ol class="list-group list-group-numbered" style="color: #444; font-size: 1.1rem; background: none; max-width: 100%; text-align: left">
                    <li class="list-group-item" style="background: none; border: none; padding-left: 0;">
                        <span class="fw-semibold"><i class="bi bi-person-plus" style="color: #007bff;"></i> Зарегистрируйтесь</span> и создайте свой виш-лист
                    </li>
                    <li class="list-group-item" style="background: none; border: none; padding-left: 0;">
                        <span class="fw-semibold"><i class="bi bi-gift" style="color: #28a745;"></i> Добавьте желания</span> с описанием, ссылкой, картинкой и ценой
                    </li>
                    <li class="list-group-item" style="background: none; border: none; padding-left: 0;">
                        <span class="fw-semibold"><i class="bi bi-link-45deg" style="color: #fd7e14;"></i> Поделитесь ссылкой</span> с друзьями — они увидят ваш список
                        </li>
                    <li class="list-group-item" style="background: none; border: none; padding-left: 0;">
                        <span class="fw-semibold"><i class="bi bi-bookmark-heart" style="color: #e83e8c;"></i> Друзья бронируют подарки</span> (вы не узнаете кто, но увидите, что подарок забронирован)
                        </li>
                    <li class="list-group-item" style="background: none; border: none; padding-left: 0;">
                        <span class="fw-semibold"><i class="bi bi-emoji-smile" style="color: #ffc107;"></i> Получайте только желанные подарки!</span>
                        </li>
                </ol>
            </div>
        </div>
        <div class="col-md-8 text-center">
            @guest
                <a href="{{ route('register') }}" class="btn btn-dark btn-lg me-2" style="border-radius: 8px;">Зарегистрироваться</a>
                <a href="{{ route('login') }}" class="btn btn-outline-dark btn-lg" style="border-radius: 8px;">Войти</a>
            @else
                <a href="{{ route('wish-lists.index') }}" class="btn btn-dark btn-lg" style="border-radius: 8px;">Перейти к моим спискам</a>
            @endguest
                <div class="p-5 d-flex justify-content-center align-items-center mb-4">
                    <div style="height: 2px; background: #e0e0e0; flex: 1;"></div>
                    <span class="mx-3" style="font-size: 1.5rem; color: #ffb300;">
                        <i class="bi bi-stars"></i>
                    </span>
                    <div style="height: 2px; background: #e0e0e0; flex: 1;"></div>
                </div>
            <div class="row justify-content-center mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card h-100" style="border-radius: 14px; box-shadow: 0 2px 8px 0 #ececec; border: none; background: #fff;">
                        <img src="{{ asset('images/69502621.jpg') }}" class="card-img-top" alt="Книга" style="border-radius: 14px 14px 0 0;">
                        <div class="card-body">
                            <h5 class="card-title" style="color: #222;">Книга "Атлант расправил плечи"</h5>
                            <p class="card-text" style="color: #444;">Примерная цена: <b>35 BYN</b></p>
                            <a href="https://www.labirint.ru/books/123456/" target="_blank" class="btn btn-outline-dark btn-sm" style="border-radius: 8px;">Ссылка на магазин</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100" style="border-radius: 14px; box-shadow: 0 2px 8px 0 #ececec; border: none; background: #fff;">
                        <img src="{{ asset('images/1478273.jpg') }}" class="card-img-top" alt="Наушники" style="border-radius: 14px 14px 0 0;">
                        <div class="card-body">
                            <h5 class="card-title" style="color: #222;">Беспроводные наушники</h5>
                            <p class="card-text" style="color: #444;">Примерная цена: <b>200 BYN</b></p>
                            <a href="https://www.ozon.ru/product/besprovodnye-naushniki-654321/" target="_blank" class="btn btn-outline-dark btn-sm" style="border-radius: 8px;">Ссылка на магазин</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100" style="border-radius: 14px; box-shadow: 0 2px 8px 0 #ececec; border: none; background: #fff;">
                        <img src="{{ asset('images/catan-00-1024x1024-wm.jpg') }}" class="card-img-top" alt="Настольная игра" style="border-radius: 14px 14px 0 0;">
                        <div class="card-body">
                            <h5 class="card-title" style="color: #222;">Настольная игра "Колонизаторы"</h5>
                            <p class="card-text" style="color: #444;">Примерная цена: <b>100 BYN</b></p>
                            <a href="https://www.mosigra.ru/games/kolonizatory/" target="_blank" class="btn btn-outline-dark btn-sm" style="border-radius: 8px;">Ссылка на магазин</a>
                        </div>
                    </div>
                </div>
            </div>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        </div>
    </div>
</div>
@endsection
